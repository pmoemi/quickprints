<?php

namespace App\Services;

use App\Models\BmsNotification;
use App\Models\PrintJob;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class JobDeleteOtpService
{
    public const TTL_MINUTES = 15;

    private const OTP_PREFIX = 'job_delete_otp:';

    private const PENDING_PREFIX = 'job_delete_pending:';

    private const INDEX_PREFIX = 'job_delete_pending_index:';

    private const RATE_PREFIX = 'job_delete_otp_rate:';

    public static function requiresOtp(?User $user): bool
    {
        return $user !== null && $user->role !== 'Admin';
    }

    public static function otpCacheKey(string $jobId, int $userId): string
    {
        return self::OTP_PREFIX.$jobId.':'.$userId;
    }

    public static function pendingCacheKey(string $jobId, int $userId): string
    {
        return self::PENDING_PREFIX.$jobId.':'.$userId;
    }

    public static function hasPendingRequest(string $jobId, int $userId): bool
    {
        return Cache::has(self::pendingCacheKey($jobId, $userId));
    }

    public static function hasPendingOtp(string $jobId, int $userId): bool
    {
        return Cache::has(self::otpCacheKey($jobId, $userId));
    }

    /** @return list<array{requester_id: int, requester_name: string}> */
    public static function pendingRequestsForJob(string $jobId): array
    {
        $ids = Cache::get(self::INDEX_PREFIX.$jobId, []);
        $out = [];

        foreach ($ids as $requesterId) {
            $payload = Cache::get(self::pendingCacheKey($jobId, (int) $requesterId));
            if (! $payload) {
                continue;
            }

            $out[] = [
                'requester_id' => (int) $requesterId,
                'requester_name' => $payload['requester_name'] ?? 'Staff',
            ];
        }

        return $out;
    }

    public static function request(string $jobId, User $requester): void
    {
        if (self::hasPendingRequest($jobId, $requester->id)) {
            abort(422, 'You already have a pending delete request for this job.');
        }

        $rateKey = self::RATE_PREFIX.$jobId.':'.$requester->id;
        $attempts = (int) Cache::get($rateKey, 0);
        if ($attempts >= 3) {
            abort(429, 'Too many delete requests. Please wait a few minutes and try again.');
        }
        Cache::put($rateKey, $attempts + 1, now()->addMinutes(self::TTL_MINUTES));

        $job = PrintJob::query()->find($jobId);
        $expires = now()->addMinutes(self::TTL_MINUTES);

        Cache::put(self::pendingCacheKey($jobId, $requester->id), [
            'requester_name' => $requester->name,
            'requested_at' => now()->toIso8601String(),
        ], $expires);

        $indexKey = self::INDEX_PREFIX.$jobId;
        $index = Cache::get($indexKey, []);
        if (! in_array($requester->id, $index, true)) {
            $index[] = $requester->id;
            Cache::put($indexKey, $index, $expires);
        }

        self::notifyRequester(
            $requester,
            'Delete request submitted',
            sprintf(
                "Your request to delete job %s (%s) was sent to admin for approval.\n\nYou'll receive another notification here with your delete code once approved.",
                $jobId,
                $job?->title ?? 'Untitled'
            ),
            'job_delete_request_sent',
            $jobId,
        );

        $admins = User::query()->where('role', 'Admin')->get();
        foreach ($admins as $admin) {
            self::createNotification(
                $admin,
                'Job delete approval needed',
                sprintf(
                    "%s requested to delete job %s (%s).\n\nApprove to send them a delete code in their notifications.",
                    $requester->name,
                    $jobId,
                    $job?->title ?? 'Untitled'
                ),
                'job_delete_request',
                $jobId,
                $requester->id,
            );
        }
    }

    public static function approve(string $jobId, int $requesterId, User $admin): void
    {
        if ($admin->role !== 'Admin') {
            abort(403, 'Only admins can approve delete requests.');
        }

        if (! self::hasPendingRequest($jobId, $requesterId)) {
            abort(422, 'This delete request is no longer pending or has expired.');
        }

        $requester = User::query()->findOrFail($requesterId);
        $job = PrintJob::query()->find($jobId);
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expires = now()->addMinutes(self::TTL_MINUTES);

        Cache::put(
            self::otpCacheKey($jobId, $requesterId),
            hash('sha256', $code),
            $expires
        );

        self::clearPendingRequest($jobId, $requesterId);

        self::notifyRequester(
            $requester,
            'Delete code approved',
            sprintf(
                "Admin approved your request to delete job %s (%s).\n\nDelete code: %s\nExpires in %d minutes.\n\nEnter this code on the job page to confirm deletion.",
                $jobId,
                $job?->title ?? 'Untitled',
                $code,
                self::TTL_MINUTES
            ),
            'job_delete_otp',
            $jobId,
        );

        BmsNotification::query()
            ->where('type', 'job_delete_request')
            ->where('job_id', $jobId)
            ->where('related_user_id', $requesterId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public static function verify(string $jobId, User $user, string $otp): bool
    {
        $key = self::otpCacheKey($jobId, $user->id);
        $hash = Cache::get($key);
        if (! $hash || ! preg_match('/^\d{6}$/', $otp)) {
            return false;
        }

        if (! hash_equals($hash, hash('sha256', $otp))) {
            return false;
        }

        Cache::forget($key);

        return true;
    }

    private static function clearPendingRequest(string $jobId, int $requesterId): void
    {
        Cache::forget(self::pendingCacheKey($jobId, $requesterId));

        $indexKey = self::INDEX_PREFIX.$jobId;
        $index = array_values(array_filter(
            Cache::get($indexKey, []),
            fn ($id) => (int) $id !== $requesterId
        ));

        if ($index) {
            Cache::put($indexKey, $index, now()->addMinutes(self::TTL_MINUTES));
        } else {
            Cache::forget($indexKey);
        }
    }

    private static function notifyRequester(
        User $requester,
        string $title,
        string $body,
        string $type,
        string $jobId,
    ): void {
        self::createNotification($requester, $title, $body, $type, $jobId);
    }

    private static function createNotification(
        User $user,
        string $title,
        string $body,
        string $type,
        string $jobId,
        ?int $relatedUserId = null,
    ): void {
        BmsNotification::query()->create([
            'id' => (int) (BmsNotification::query()->max('id') ?? 0) + 1,
            'user_id' => $user->id,
            'job_id' => $jobId,
            'related_user_id' => $relatedUserId,
            'title' => $title,
            'body' => $body,
            'type' => $type,
        ]);
    }
}
