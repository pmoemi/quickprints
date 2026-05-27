<?php

namespace App\Services;

class BmsMailer
{
    /**
     * Configure Laravel's mailer at runtime from BMS settings.
     * Uses a dedicated 'bms_smtp' mailer to avoid cache conflicts.
     *
     * Returns false if no SMTP host is configured (safe no-op).
     */
    public static function configure(array $settings): bool
    {
        $es   = $settings['email_settings'] ?? [];
        $host = trim($es['host'] ?? '');

        if (empty($host)) {
            return false;
        }

        $fromAddress = trim($es['from_address'] ?? '') ?: ($settings['email'] ?? 'noreply@example.com');
        $fromName    = trim($es['from_name'] ?? '')    ?: ($settings['company_name'] ?? 'Quick Prints');
        $encryption  = $es['encryption'] ?: null;

        config([
            'mail.default'                     => 'bms_smtp',
            'mail.mailers.bms_smtp.transport'  => 'smtp',
            'mail.mailers.bms_smtp.host'       => $host,
            'mail.mailers.bms_smtp.port'       => (int) ($es['port'] ?? 587),
            'mail.mailers.bms_smtp.encryption' => $encryption,
            'mail.mailers.bms_smtp.username'   => $es['username'] ?? null,
            'mail.mailers.bms_smtp.password'   => $es['password'] ?? null,
            'mail.mailers.bms_smtp.timeout'    => 30,
            'mail.from.address'                => $fromAddress,
            'mail.from.name'                   => $fromName,
        ]);

        return true;
    }

    /** True when SMTP host + username are both set. */
    public static function isConfigured(array $settings): bool
    {
        $es = $settings['email_settings'] ?? [];

        return ! empty(trim($es['host'] ?? '')) && ! empty(trim($es['username'] ?? ''));
    }

    /** True when SMTP is configured AND the given notification type is enabled. */
    public static function notificationEnabled(array $settings, string $key): bool
    {
        if (! self::isConfigured($settings)) {
            return false;
        }

        return (bool) ($settings['email_settings']['notifications'][$key] ?? false);
    }
}
