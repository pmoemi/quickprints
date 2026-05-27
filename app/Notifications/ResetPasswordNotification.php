<?php

namespace App\Notifications;

use App\Services\BmsMailer;
use App\Services\BmsSettingsService;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    public function __construct(public readonly string $token) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $settings = app(BmsSettingsService::class)->all();
        BmsMailer::configure($settings);

        $url = url(route('bms.password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->view('emails.password_reset', [
                'url'      => $url,
                'settings' => $settings,
                'name'     => $notifiable->name,
            ])
            ->subject('Reset Your Password — ' . ($settings['company_name'] ?? 'Quick Prints'));
    }
}
