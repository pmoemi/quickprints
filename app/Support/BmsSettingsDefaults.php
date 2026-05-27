<?php

namespace App\Support;

class BmsSettingsDefaults
{
    /** @return array<string, mixed> */
    public static function all(): array
    {
        return [
            'theme' => 'dark',
            'company_name' => 'Quick Prints',
            'company_tagline' => 'Printing, Signage & Branding',
            'website' => 'quickprints.co.ke',
            'email' => 'info@quickprints.co.ke',
            'phone' => '+254 700 000000',
            'address' => 'Nairobi, Kenya',
            'currency' => 'KES',
            'currency_symbol' => 'KSh',
            'payment_methods' => ['Mpesa', 'Cash', 'Bank Transfer', 'Cheque', 'Credit'],
            'numbering' => [
                // Job IDs  — e.g.  QP-10001
                'job_prefix'      => 'QP',
                'job_start'       => 10001,
                'job_pad'         => 5,
                'job_per_branch'  => false,   // if true, prefix becomes QP-WL-, QP-CBD- etc.
                // Quotes  — e.g.  QT-0001
                'quote_prefix'    => 'QT',
                'quote_start'     => 1,
                'quote_pad'       => 4,
                // Invoices — display prefix on the document
                'invoice_prefix'  => 'INV',
                // Receipts — display prefix on the document
                'receipt_prefix'  => 'RCP',
            ],
            'vat_rate' => 16,
            'branches' => ['Westlands', 'CBD', 'Eastleigh', 'Karen', 'Ngong Road'],
            'brand_color' => '#b91c1c',
            'brand_color_secondary' => '#dc2626',
            'logo_url' => null,
            'logo_url_dark' => null,
            'logo_url_light' => null,
            'favicon_url' => null,
            'invoice' => [
                // Header & layout
                'show_header'         => true,
                'show_logo'           => true,
                'show_company_name'   => true,
                'show_tagline'        => true,
                'header_title'        => 'QUICKPRINTS',
                'layout'              => 'classic',
                'accent_color'        => '#f97316',
                'header_bg'           => '#1e293b',
                'header_text_color'   => '#ffffff',
                // Tagline
                'tagline_text'         => '',
                'tagline_style'        => 'plain',
                'tagline_item_color'   => '',
                'tagline_divider'      => '|',
                'tagline_divider_color'=> '',
                // Typography
                'font_size'           => '13',
                'text_color'          => '#111111',
                // Logo
                'logo_size'           => '50',
                'logo_greyscale'      => false,
                // Table
                'table_border'       => true,
                'table_striped'      => false,
                'table_header_bg'    => '',
                'table_header_color' => '#ffffff',
                // Footer
                'show_footer'        => true,
                'footer_text'        => 'Thank you for your business',
                'footer_bg'          => 'transparent',
                'footer_text_color'  => '#999999',
                // Signature
                'show_signature'     => false,
                'signature_label'    => 'Authorized Signature',
                'signature_position' => 'right',
                'show_logo'            => true,
                'show_company_name'    => true,
                'show_tagline'         => true,
                'tagline_style'        => 'plain',
                'tagline_item_color'   => '',
                'tagline_divider'      => '|',
                'tagline_divider_color'=> '',
                // Watermark
                'watermark_type'      => 'text',   // 'none' | 'text' | 'image'
                'watermark_text'      => '',
                'watermark_image_url' => null,
                'watermark_opacity'   => '0.06',
                'watermark_rotation'  => '-30',
                // Payment & terms
                'terms_default'      => "We give 6 months warranty on all signage.\nM-Pesa Paybill: 516600 | Account: invoice number",
                'mpesa_paybill'      => '516600',
                'mpesa_account_hint' => 'Use invoice number as account',
                'show_vat_column'    => true,
                // Quote
                'quote_validity_days' => 30,
                'quote_footer_text'   => 'This is a quotation, not a tax invoice.',
                // Page margins (cm)
                'margin_top'    => '1.5',
                'margin_right'  => '1.5',
                'margin_bottom' => '1.5',
                'margin_left'   => '1.5',
            ],
            'email_templates' => [
                'job_created' => [
                    'subject'  => 'Job Received: {job_title} — {company}',
                    'greeting' => 'Hi {client_name},',
                    'intro'    => 'Thank you for choosing {company}. We have received your job and our team will get started shortly.',
                    'closing'  => 'We will notify you as your job progresses. If you have any questions, please contact us.',
                    'sign_off' => 'Thank you for your business!',
                ],
                'job_status' => [
                    'subject'  => 'Job Update: {stage} — {company}',
                    'greeting' => 'Hi {client_name},',
                    'closing'  => 'Thank you for choosing {company}.',
                ],
                'job_invoice' => [
                    'subject'  => 'Invoice {job_id} — {company}',
                    'greeting' => 'Dear {client_name},',
                    'intro'    => 'Please find attached the invoice for your recent job. Thank you for your business.',
                    'closing'  => 'Please contact us if you have any questions regarding this invoice.',
                ],
                'quote_sent' => [
                    'subject'      => 'Quotation for {company}',
                    'greeting'     => 'Dear {client_name},',
                    'intro'        => 'Thank you for your interest in {company}. Please find your quotation details below. The full quotation PDF is also attached.',
                    'validity_note' => 'This quotation is valid for {validity_days} days. To proceed or ask any questions, please contact us.',
                ],
            ],
            'email_settings' => [
                'host'         => '',
                'port'         => 587,
                'encryption'   => 'tls',
                'username'     => '',
                'password'     => '',
                'from_address' => '',
                'from_name'    => '',
                'notifications' => [
                    'job_created' => false,
                    'job_updated' => false,
                    'quote_sent'  => false,
                ],
            ],
            'roles' => [
                'General Manager' => [
                    'perms' => [
                        'jobs'       => ['read', 'write', 'create'],
                        'sales'      => ['read', 'write', 'create'],
                        'clients'    => ['read', 'write', 'create'],
                        'operations' => ['read', 'write', 'create'],
                        'finance'    => ['read', 'write', 'create'],
                        'hr'         => ['read', 'write', 'create'],
                        'reports'    => ['read'],
                        'settings'   => ['read', 'write'],
                    ],
                    'allBranches'          => true,
                    'resetStaffPasswords'  => false,
                ],
                'Operations Manager' => [
                    'perms' => [
                        'jobs'       => ['read', 'write', 'create'],
                        'clients'    => ['read', 'write'],
                        'operations' => ['read', 'write', 'create'],
                        'reports'    => ['read'],
                    ],
                    'allBranches'         => false,
                    'resetStaffPasswords' => false,
                ],
                'Receptionist' => [
                    'perms' => [
                        'jobs'    => ['read', 'write', 'create'],
                        'sales'   => ['read', 'write', 'create'],
                        'clients' => ['read', 'write', 'create'],
                        'reports' => ['read'],
                    ],
                    'allBranches'         => false,
                    'resetStaffPasswords' => false,
                ],
                'Sales' => [
                    'perms' => [
                        'jobs'    => ['read'],
                        'sales'   => ['read', 'write', 'create'],
                        'clients' => ['read', 'write', 'create'],
                        'reports' => ['read'],
                    ],
                    'allBranches'         => false,
                    'resetStaffPasswords' => false,
                ],
                'Designer' => [
                    'perms'               => ['operations' => ['read', 'write']],
                    'allBranches'         => false,
                    'resetStaffPasswords' => false,
                ],
                'Operator' => [
                    'perms'               => ['operations' => ['read', 'write']],
                    'allBranches'         => false,
                    'resetStaffPasswords' => false,
                ],
                'Fabrication Staff' => [
                    'perms'               => ['operations' => ['read', 'write']],
                    'allBranches'         => false,
                    'resetStaffPasswords' => false,
                ],
                'Welder' => [
                    'perms'               => ['operations' => ['read', 'write']],
                    'allBranches'         => false,
                    'resetStaffPasswords' => false,
                ],
                'CNC Operator' => [
                    'perms'               => ['operations' => ['read', 'write']],
                    'allBranches'         => false,
                    'resetStaffPasswords' => false,
                ],
                'Laser Operator' => [
                    'perms'               => ['operations' => ['read', 'write']],
                    'allBranches'         => false,
                    'resetStaffPasswords' => false,
                ],
            ],
        ];
    }

    /**
     * Resource groups: define which BMS resources and pages each permission group covers.
     *
     * @return array<string, array{label: string, resources: list<string>, pages: list<string>}>
     */
    public static function resourceGroups(): array
    {
        return [
            'jobs'       => [
                'label'     => 'Job Management',
                'resources' => ['jobs'],
                'pages'     => ['jobs', 'kanban', 'payments'],
            ],
            'sales'      => [
                'label'     => 'Sales & Quotes',
                'resources' => ['saleslog', 'quotes', 'leads'],
                'pages'     => ['saleslog', 'quotes', 'leads', 'followups', 'commissions', 'sales-targets'],
            ],
            'clients'    => [
                'label'     => 'Client Management',
                'resources' => ['clients'],
                'pages'     => ['clients'],
            ],
            'operations' => [
                'label'     => 'Operations',
                'resources' => ['inventory'],
                'pages'     => ['designer', 'operator', 'fabrication', 'delivery', 'inventory'],
            ],
            'finance'    => [
                'label'     => 'Financial Management',
                'resources' => ['opex', 'payroll', 'pettycash', 'assets', 'procurement', 'suppliers', 'purchase_orders', 'recurring_bills', 'ledger'],
                'pages'     => ['opex', 'payroll', 'petty-cash', 'assets', 'procurement', 'suppliers', 'purchase-orders', 'recurring-bills', 'ledger', 'bank-recon', 'cash-recon', 'vat-report'],
            ],
            'hr'         => [
                'label'     => 'HR & People',
                'resources' => ['staff', 'attendance', 'leave'],
                'pages'     => ['staff', 'attendance', 'leave', 'payslips'],
            ],
            'reports'    => [
                'label'     => 'Reports & Analytics',
                'resources' => [],
                'pages'     => ['reports'],
            ],
            'settings'   => [
                'label'     => 'Settings & Admin',
                'resources' => ['settings', 'branches', 'services'],
                'pages'     => ['settings', 'audit-log', 'maintenance', 'portal', 'services'],
            ],
        ];
    }

    /** Pages accessible to any authenticated BMS user regardless of role. */
    public static function alwaysAccessiblePages(): array
    {
        return ['dashboard', 'messages', 'notifications', 'services'];
    }

    /**
     * Returns the live roles map, reading from DB settings when available.
     * Uses a request-level static cache to avoid repeated DB hits.
     *
     * @return array<string, array{perms: array<string, list<string>>, allBranches: bool}>
     */
    public static function roles(): array
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }

        try {
            $data = \App\Models\BmsSetting::find(1)?->data ?? [];
            if (! empty($data['roles']) && is_array($data['roles'])) {
                return $cache = $data['roles'];
            }
        } catch (\Throwable) {
        }

        return $cache = self::all()['roles'];
    }

    /** @return array<string, mixed> */
    public static function publicBranding(): array
    {
        $all = self::all();

        return [
            'company_name'           => $all['company_name'],
            'company_tagline'        => $all['company_tagline'],
            'brand_color'            => $all['brand_color'],
            'brand_color_secondary'  => $all['brand_color_secondary'],
            'logo_url'               => $all['logo_url'],
            'logo_url_dark'          => $all['logo_url_dark'],
            'logo_url_light'         => $all['logo_url_light'],
            'favicon_url'            => $all['favicon_url'],
        ];
    }

    /** @param  array<string, mixed>  $stored */
    public static function merge(array $stored): array
    {
        $defaults = self::all();

        $invoice = array_merge(
            $defaults['invoice'],
            is_array($stored['invoice'] ?? null) ? $stored['invoice'] : []
        );

        $storedEs = is_array($stored['email_settings'] ?? null) ? $stored['email_settings'] : [];
        $emailSettings = array_merge($defaults['email_settings'], $storedEs);
        $emailSettings['notifications'] = array_merge(
            $defaults['email_settings']['notifications'],
            is_array($storedEs['notifications'] ?? null) ? $storedEs['notifications'] : []
        );

        $storedTpls     = is_array($stored['email_templates'] ?? null) ? $stored['email_templates'] : [];
        $emailTemplates = [];
        foreach ($defaults['email_templates'] as $type => $defaultTpl) {
            $emailTemplates[$type] = array_merge(
                $defaultTpl,
                is_array($storedTpls[$type] ?? null) ? $storedTpls[$type] : []
            );
        }

        return array_merge($defaults, $stored, [
            'invoice'         => $invoice,
            'email_settings'  => $emailSettings,
            'email_templates' => $emailTemplates,
        ]);
    }
}
