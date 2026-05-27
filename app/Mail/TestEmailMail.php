<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TestEmailMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public array $settings) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Test Email — ' . ($this->settings['company_name'] ?? 'Quick Prints') . ' BMS',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.test',
            with: ['settings' => $this->settings],
        );
    }
}
