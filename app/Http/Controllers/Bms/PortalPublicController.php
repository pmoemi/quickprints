<?php

namespace App\Http\Controllers\Bms;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\PortalToken;
use App\Models\PrintJob;
use App\Services\BmsSettingsService;
use App\Support\BrandColors;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\View\View;

class PortalPublicController extends Controller
{
    public function __construct(
        private BmsSettingsService $settingsService,
    ) {}

    public function show(string $token): View
    {
        [$portal, $client] = $this->resolvePortal($token);

        $jobs = PrintJob::query()
            ->where('client_id', $client->id)
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        return view('portal.public', $this->portalViewData($portal, $client, $jobs));
    }

    public function invoice(string $token, string $job): View
    {
        [$portal, $client, $job] = $this->resolvePortalJob($token, $job);

        return view('portal.invoice', $this->portalViewData($portal, $client, collect([$job]), $job));
    }

    public function invoicePdf(string $token, string $job): Response
    {
        [, $client, $job] = $this->resolvePortalJob($token, $job);
        $settings = $this->settingsService->all();

        $pdf = Pdf::loadView('pdf.invoice', compact('job', 'client', 'settings'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream("invoice-{$job->id}.pdf");
    }

    public function receiptPdf(string $token, string $job): Response
    {
        [, $client, $job] = $this->resolvePortalJob($token, $job);
        abort_if($job->paymentStatus() !== 'full', 404, 'Receipt only available for fully paid jobs.');

        $settings = $this->settingsService->all();

        $pdf = Pdf::loadView('pdf.receipt', compact('job', 'client', 'settings'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream("receipt-{$job->id}.pdf");
    }

    /** @return array{0: PortalToken, 1: Client} */
    private function resolvePortal(string $token): array
    {
        $portal = PortalToken::query()->where('token', $token)->firstOrFail();

        if ($portal->expires_at && $portal->expires_at->isPast()) {
            abort(410, 'This portal link has expired.');
        }

        $client = Client::query()->findOrFail($portal->client_id);

        return [$portal, $client];
    }

    /** @return array{0: PortalToken, 1: Client, 2: PrintJob} */
    private function resolvePortalJob(string $token, string $jobId): array
    {
        [$portal, $client] = $this->resolvePortal($token);

        $job = PrintJob::query()
            ->where('client_id', $client->id)
            ->findOrFail($jobId);

        return [$portal, $client, $job];
    }

    /** @param  \Illuminate\Support\Collection<int, PrintJob>|null  $jobs */
    private function portalViewData(PortalToken $portal, Client $client, $jobs = null, ?PrintJob $job = null): array
    {
        $settings = $this->settingsService->all();
        $brand = BrandColors::fromSettings($settings);
        $defaultTheme = 'dark';

        $data = compact('portal', 'client', 'settings', 'brand', 'defaultTheme');

        if ($jobs !== null) {
            $data['jobs'] = $jobs;
        }

        if ($job !== null) {
            $data['job'] = $job;
        }

        return $data;
    }
}
