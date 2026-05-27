<?php

namespace App\Mail;

use App\Models\Client;
use App\Models\PrintJob;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class JobCreatedMail extends Mailable
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
        $subject = $this->resolve($tpl['subject'] ?? 'Job Received: {job_title} — {company}');

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.job_created',
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
        return $this->settings['email_templates']['job_created'] ?? [];
    }

    private function resolve(string $text): string
    {
        return str_replace(
            ['{company}', '{client_name}', '{job_title}', '{job_id}'],
            [
                $this->settings['company_name'] ?? 'Quick Prints',
                $this->client?->name ?? 'Valued Customer',
                $this->job->title,
                $this->job->id,
            ],
            $text
        );
    }
}
