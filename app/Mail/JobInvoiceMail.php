<?php

namespace App\Mail;

use App\Models\Client;
use App\Models\PrintJob;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class JobInvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public PrintJob $job,
        public ?Client  $client,
        public array    $settings
    ) {}

    public function envelope(): Envelope
    {
        $tpl     = $this->tpl();
        $subject = $this->resolve($tpl['subject'] ?? 'Invoice {job_id} — {company}');

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.job_invoice',
            with: [
                'job'      => $this->job,
                'client'   => $this->client,
                'settings' => $this->settings,
                'symbol'   => $this->settings['currency_symbol'] ?? 'KSh',
                'tpl'      => $this->tpl(),
            ],
        );
    }

    private function tpl(): array
    {
        return $this->settings['email_templates']['job_invoice'] ?? [];
    }

    private function resolve(string $text): string
    {
        return str_replace(
            ['{company}', '{client_name}', '{job_id}', '{job_title}'],
            [
                $this->settings['company_name'] ?? 'Quick Prints',
                $this->client?->name ?? 'Valued Customer',
                $this->job->id,
                $this->job->title,
            ],
            $text
        );
    }

    public function attachments(): array
    {
        $pdf = Pdf::loadView('pdf.invoice', [
            'job'      => $this->job,
            'client'   => $this->client,
            'settings' => $this->settings,
        ])->setPaper('a4', 'portrait');

        return [
            Attachment::fromData(
                fn () => $pdf->output(),
                "invoice-{$this->job->id}.pdf"
            )->withMime('application/pdf'),
        ];
    }
}
