<?php

namespace App\Http\Controllers\Bms;

use App\Mail\JobCreatedMail;
use App\Mail\JobInvoiceMail;
use App\Mail\JobStatusMail;
use App\Models\Client;
use App\Models\PrintJob;
use App\Models\Staff;
use App\Services\BmsMailer;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class JobController extends BmsController
{
    public function index(Request $request): View
    {
        $this->authorizeBms('jobs', 'read');

        $query = $this->scopeBranch(PrintJob::query())->orderByDesc('id');

        if ($request->filled('stage') && $request->stage !== 'all') {
            $query->where('stage', $request->stage);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($builder) use ($q) {
                $builder->where('id', 'like', "%{$q}%")
                    ->orWhere('title', 'like', "%{$q}%");
            });
        }

        $jobs = $query->get();
        $clients = Client::query()->orderBy('name')->get()->keyBy('id');

        return view('jobs.index', compact('jobs', 'clients'));
    }

    public function create(): View
    {
        $this->authorizeBms('jobs', 'create');

        return view('jobs.form', [
            'job' => new PrintJob(['stage' => 'waiting', 'priority' => 'medium', 'paid' => false]),
            'clients' => $this->scopeBranch(Client::query())->orderBy('name')->get(),
            'branches' => $this->branchNames(),
            'staff' => Staff::query()->where('active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeBms('jobs', 'create');

        $data = $request->validate([
            'client_id' => 'nullable|integer',
            'title' => 'required|string|max:255',
            'branch' => 'required|string|max:80',
            'category' => 'nullable|string|max:80',
            'stage' => 'nullable|string|max:40',
            'priority' => 'nullable|string|max:20',
            'amount' => 'nullable|numeric|min:0',
            'deadline' => 'nullable|date',
            'notes' => 'nullable|string',
            'designer_id' => 'nullable|integer',
            'sales_rep_id' => 'nullable|integer',
        ]);

        $data['id'] = $this->nextJobId($data['branch'] ?? null);
        $data['stage'] = $data['stage'] ?? 'waiting';
        $data['paid'] = false;
        $data['history'] = [['action' => 'Job created', 'by' => $request->user()->name, 'at' => now()->toIso8601String()]];

        $job = PrintJob::query()->create($data);

        // Email client on job creation
        $settings = $this->bmsSettings();
        if (BmsMailer::notificationEnabled($settings, 'job_created')) {
            $client = $data['client_id'] ? Client::query()->find($data['client_id']) : null;
            if ($client?->email) {
                BmsMailer::configure($settings);
                Mail::to($client->email)->send(new JobCreatedMail($job, $client, $settings));
            }
        }

        return redirect()->route('bms.jobs.index')->with('success', 'Job created.');
    }

    public function show(string $id): View
    {
        $this->authorizeBms('jobs', 'read');

        $job = PrintJob::query()->findOrFail($id);
        $clients = Client::query()->get()->keyBy('id');

        return view('jobs.show', compact('job', 'clients'));
    }

    public function invoice(string $job): View
    {
        $this->authorizeBms('jobs', 'read');

        $job      = PrintJob::query()->findOrFail($job);
        $client   = $job->client_id ? Client::query()->find($job->client_id) : null;
        $settings = $this->bmsSettings();

        return view('jobs.invoice', compact('job', 'client', 'settings'));
    }

    public function invoicePdf(string $job): Response
    {
        $this->authorizeBms('jobs', 'read');

        $job      = PrintJob::query()->findOrFail($job);
        $client   = $job->client_id ? Client::query()->find($job->client_id) : null;
        $settings = $this->bmsSettings();

        $pdf = Pdf::loadView('pdf.invoice', compact('job', 'client', 'settings'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream("invoice-{$job->id}.pdf");
    }

    public function receiptPdf(string $job): Response
    {
        $this->authorizeBms('jobs', 'read');

        $job      = PrintJob::query()->findOrFail($job);
        $client   = $job->client_id ? Client::query()->find($job->client_id) : null;
        $settings = $this->bmsSettings();

        abort_if(! $job->paid, 404, 'Receipt only available for paid jobs.');

        $pdf = Pdf::loadView('pdf.receipt', compact('job', 'client', 'settings'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream("receipt-{$job->id}.pdf");
    }

    public function uploadArtwork(Request $request, string $jobId): \Illuminate\Http\JsonResponse
    {
        $this->authorizeBms('jobs', 'write');

        $job  = PrintJob::findOrFail($jobId);
        $path = $request->validate(['artwork' => 'required|image|max:8192'])['artwork']
            ->store('job-artwork', 'public');
        $url  = Storage::disk('public')->url($path);

        $job->update(['artwork_url' => $url]);

        return response()->json(['url' => $url]);
    }

    public function removeArtwork(string $jobId): \Illuminate\Http\JsonResponse
    {
        $this->authorizeBms('jobs', 'write');

        $job = PrintJob::findOrFail($jobId);
        $job->update(['artwork_url' => null]);

        return response()->json(['ok' => true]);
    }

    public function sendInvoice(string $job): RedirectResponse
    {
        $this->authorizeBms('jobs', 'read');

        $job      = PrintJob::query()->findOrFail($job);
        $client   = $job->client_id ? Client::query()->find($job->client_id) : null;
        $settings = $this->bmsSettings();

        if (! $client?->email) {
            return back()->with('error', 'Client has no email address on file.');
        }

        if (! BmsMailer::configure($settings)) {
            return back()->with('error', 'SMTP is not configured in Settings → Email.');
        }

        try {
            Mail::to($client->email)->send(new JobInvoiceMail($job, $client, $settings));
            return back()->with('success', "Invoice emailed to {$client->email}.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Email failed: ' . $e->getMessage());
        }
    }

    public function edit(string $id): View
    {
        $this->authorizeBms('jobs', 'update');

        $job = PrintJob::query()->findOrFail($id);

        return view('jobs.form', [
            'job' => $job,
            'clients' => $this->scopeBranch(Client::query())->orderBy('name')->get(),
            'branches' => $this->branchNames(),
            'staff' => Staff::query()->where('active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $this->authorizeBms('jobs', 'update');

        $job = PrintJob::query()->findOrFail($id);

        $data = $request->validate([
            'client_id' => 'nullable|integer',
            'title' => 'sometimes|string|max:255',
            'branch' => 'sometimes|string|max:80',
            'category' => 'nullable|string|max:80',
            'stage' => 'nullable|string|max:40',
            'priority' => 'nullable|string|max:20',
            'amount' => 'nullable|numeric|min:0',
            'paid' => 'nullable|boolean',
            'deadline' => 'nullable|date',
            'notes' => 'nullable|string',
            'designer_id' => 'nullable|integer',
            'sales_rep_id' => 'nullable|integer',
        ]);

        if (isset($data['stage']) && $data['stage'] !== $job->stage) {
            $history = $job->history ?? [];
            $history[] = [
                'action' => 'Stage changed to '.$data['stage'],
                'by' => $request->user()->name,
                'at' => now()->toIso8601String(),
            ];
            $data['history'] = $history;
        }

        if ($request->has('paid')) {
            $data['paid'] = $request->boolean('paid');
        }

        $oldStage = $job->stage;
        $job->update($data);

        // Email client on stage change
        if (isset($data['stage']) && $data['stage'] !== $oldStage) {
            $settings = $this->bmsSettings();
            if (BmsMailer::notificationEnabled($settings, 'job_updated')) {
                $client = $job->client_id ? Client::query()->find($job->client_id) : null;
                if ($client?->email) {
                    BmsMailer::configure($settings);
                    Mail::to($client->email)->send(new JobStatusMail($job, $client, $oldStage, $settings));
                }
            }
        }

        return redirect()->route('bms.jobs.show', $job->id)->with('success', 'Job updated.');
    }

    public function destroy(string $id): RedirectResponse
    {
        $this->authorizeBms('jobs', 'delete');

        PrintJob::query()->findOrFail($id)->delete();

        return redirect()->route('bms.jobs.index')->with('success', 'Job deleted.');
    }
}


