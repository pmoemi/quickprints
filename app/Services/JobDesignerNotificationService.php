<?php

namespace App\Services;

use App\Models\BmsNotification;
use App\Models\PrintJob;
use App\Models\Staff;
use App\Models\User;

class JobDesignerNotificationService
{
    public static function notifyAssigned(PrintJob $job, User $assignedBy): void
    {
        if (! $job->designer_id) {
            return;
        }

        $staff = Staff::query()->find($job->designer_id);
        if (! $staff?->user_id) {
            return;
        }

        $designerUser = User::query()->find($staff->user_id);
        if (! $designerUser) {
            return;
        }

        self::createNotification(
            $designerUser,
            'New job assigned to you',
            sprintf(
                "%s assigned you job %s (%s) on the designer board.\n\nBranch: %s",
                $assignedBy->name,
                $job->id,
                $job->title ?? 'Untitled',
                $job->branch ?? '—'
            ),
            'job_assigned',
            $job->id,
            $assignedBy->id,
        );
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
