<?php

namespace App\Mail;

use App\Models\Quote;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuoteSentMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Quote $quote,
        public array $settings
    ) {}

    public function envelope(): Envelope
    {
        $tpl     = $this->tpl();
        $subject = $this->resolve($tpl['subject'] ?? 'Quotation for {company}');

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        $items    = $this->quote->items ?? [];
        $subtotal = collect($items)->sum(fn ($i) => ($i['qty'] ?? 1) * ($i['unit_price'] ?? 0));
        $total    = $subtotal * (1 + (($this->quote->vat_rate ?? 16) / 100));

        return new Content(
            view: 'emails.quote_sent',
            with: [
                'quote'    => $this->quote,
                'settings' => $this->settings,
                'items'    => $items,
                'subtotal' => $subtotal,
                'total'    => $total,
                'symbol'   => $this->settings['currency_symbol'] ?? 'KSh',
                'tpl'      => $this->tpl(),
            ],
        );
    }

    private function tpl(): array
    {
        return $this->settings['email_templates']['quote_sent'] ?? [];
    }

    private function resolve(string $text): string
    {
        $validityDays = $this->settings['invoice']['quote_validity_days'] ?? 30;

        return str_replace(
            ['{company}', '{client_name}', '{quote_number}', '{validity_days}'],
            [
                $this->settings['company_name'] ?? 'Quick Prints',
                $this->quote->client_name ?? 'Valued Customer',
                'QT-' . str_pad($this->quote->id, 4, '0', STR_PAD_LEFT),
                $validityDays,
            ],
            $text
        );
    }

    public function attachments(): array
    {
        $pdf = Pdf::loadView('pdf.quote', [
            'quote'    => $this->quote,
            'settings' => $this->settings,
        ])->setPaper('a4', 'portrait');

        return [
            Attachment::fromData(
                fn () => $pdf->output(),
                "quotation-{$this->quote->id}.pdf"
            )->withMime('application/pdf'),
        ];
    }
}
