<?php

namespace App\Http\Controllers\Bms;

use App\Mail\QuoteSentMail;
use App\Models\Quote;
use App\Services\BmsMailer;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class QuoteController extends BmsController
{
    public function index(): View
    {
        $this->authorizeBms('quotes', 'read');
        $quotes = $this->scopeBranch(Quote::query())->orderByDesc('id')->get();

        return view('quotes.index', compact('quotes'));
    }

    public function create(): View
    {
        $this->authorizeBms('quotes', 'create');

        return view('quotes.form', [
            'quote' => new Quote(['status' => 'draft', 'vat_rate' => 16, 'items' => []]),
            'branches' => $this->branchNames(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeBms('quotes', 'create');
        $data = $this->validated($request);
        $data['id'] = $this->nextNumericId(Quote::class);
        $data['prepared_by'] = $request->user()->name;
        $quote = Quote::query()->create($data);

        // Email client if quote_sent notification is enabled and phone hints at email
        $settings = $this->bmsSettings();
        if (BmsMailer::notificationEnabled($settings, 'quote_sent') && ! empty($data['client_phone'])) {
            // Only send if client_phone looks like an email (some workflows store email here)
            if (filter_var($data['client_phone'], FILTER_VALIDATE_EMAIL)) {
                BmsMailer::configure($settings);
                Mail::to($data['client_phone'])->send(new QuoteSentMail($quote, $settings));
            }
        }

        return redirect()->route('bms.quotes.index')->with('success', 'Quote created.');
    }

    public function edit(int $id): View
    {
        $this->authorizeBms('quotes', 'update');

        return view('quotes.form', [
            'quote' => $this->findScopedQuote($id),
            'branches' => $this->branchNames(),
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $this->authorizeBms('quotes', 'update');
        $this->findScopedQuote($id)->update($this->validated($request));

        return redirect()->route('bms.quotes.index')->with('success', 'Quote updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->authorizeBms('quotes', 'delete');
        $this->findScopedQuote($id)->delete();

        return redirect()->route('bms.quotes.index')->with('success', 'Quote deleted.');
    }

    public function pdf(int $id): Response
    {
        $this->authorizeBms('quotes', 'read');

        $quote    = $this->findScopedQuote($id);
        $settings = $this->bmsSettings();

        $pdf = Pdf::loadView('pdf.quote', compact('quote', 'settings'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream("quotation-{$id}.pdf");
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'date' => 'nullable|date',
            'client_name' => 'required|string|max:120',
            'client_phone' => 'nullable|string|max:40',
            'branch' => 'nullable|string|max:80',
            'vat_rate' => 'nullable|numeric',
            'status' => 'nullable|string|max:40',
            'notes' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*.desc' => 'nullable|string',       // form field name
            'items.*.description' => 'nullable|string', // backward compat (saved JSON)
            'items.*.qty' => 'nullable|numeric',
            'items.*.unit_price' => 'nullable|numeric',
        ]);

        $items = [];
        foreach ($data['items'] ?? [] as $row) {
            // Accept both 'desc' (form) and 'description' (stored JSON)
            $desc = trim((string) ($row['desc'] ?? $row['description'] ?? ''));
            if ($desc === '') {
                continue;
            }
            $items[] = [
                'description' => $desc,
                'qty' => (float) ($row['qty'] ?? 1),
                'unit_price' => (float) ($row['unit_price'] ?? 0),
            ];
        }
        $data['items'] = $items;

        return $data;
    }
}


