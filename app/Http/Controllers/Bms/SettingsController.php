<?php

namespace App\Http\Controllers\Bms;

use App\Mail\TestEmailMail;
use App\Models\User;
use App\Services\BmsMailer;
use App\Support\BmsNavigation;
use App\Support\BmsSettingsDefaults;
use App\Support\BranchUsage;
use App\Support\DocTemplateEngine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Support\BrandAssets;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingsController extends BmsController
{
    public function company(): View
    {
        $this->authorizeBms('settings', 'read');

        return view('settings/company', ['settings' => $this->bmsSettings()]);
    }

    public function updateCompany(Request $request): RedirectResponse
    {
        $this->authorizeBms('settings', 'update');
        $this->settings->update($request->validate([
            'company_name' => 'required|string|max:120',
            'company_tagline' => 'nullable|string|max:200',
            'website' => 'nullable|string|max:120',
            'email' => 'nullable|email|max:120',
            'phone' => 'nullable|string|max:40',
            'address' => 'nullable|string|max:200',
            'theme' => 'nullable|in:dark,light',
        ]));

        return back()->with('success', 'Company settings saved.');
    }

    public function branches(): View
    {
        $this->authorizeBms('branches', 'read');

        return view('settings/branches', ['settings' => $this->bmsSettings()]);
    }

    public function storeBranch(Request $request): RedirectResponse
    {
        $this->authorizeBms('branches', 'create');
        $name = trim($request->validate(['name' => 'required|string|max:80'])['name']);
        $settings = $this->bmsSettings();
        $branches = $settings['branches'] ?? [];
        if (in_array($name, $branches, true)) {
            return back()->withErrors(['name' => 'Branch already exists.']);
        }
        $branches[] = $name;
        $this->settings->update(['branches' => array_values($branches)]);

        return back()->with('success', 'Branch added.');
    }

    public function renameBranch(Request $request): RedirectResponse
    {
        $this->authorizeBms('branches', 'update');
        $data = $request->validate(['from' => 'required|string', 'to' => 'required|string']);
        $settings = $this->bmsSettings();
        $branches = $settings['branches'] ?? [];
        if (! in_array($data['from'], $branches, true)) {
            return back()->withErrors(['from' => 'Branch not found.']);
        }
        BranchUsage::rename($data['from'], $data['to']);
        $this->settings->update([
            'branches' => array_values(array_map(fn ($b) => $b === $data['from'] ? $data['to'] : $b, $branches)),
        ]);

        return back()->with('success', 'Branch renamed.');
    }

    public function destroyBranch(string $name): RedirectResponse
    {
        $this->authorizeBms('branches', 'delete');
        $name = urldecode($name);
        $usage = BranchUsage::count($name);
        if ($usage['total'] > 0) {
            return back()->withErrors(['branch' => 'Branch is in use. Rename records first.']);
        }
        $settings = $this->bmsSettings();
        $branches = array_values(array_filter($settings['branches'] ?? [], fn ($b) => $b !== $name));
        if (count($branches) < 1) {
            return back()->withErrors(['branch' => 'At least one branch required.']);
        }
        $this->settings->update(['branches' => $branches]);

        return back()->with('success', 'Branch removed.');
    }

    public function branding(): View
    {
        $this->authorizeBms('settings', 'read');

        return view('settings/branding', ['settings' => $this->bmsSettings()]);
    }

    public function updateBranding(Request $request): RedirectResponse
    {
        $this->authorizeBms('settings', 'update');
        $this->settings->update($request->validate([
            'brand_color' => 'required|string|max:20',
            'brand_color_secondary' => 'required|string|max:20',
        ]));

        return back()->with('success', 'Brand colors saved.');
    }

    public function uploadLogo(Request $request): RedirectResponse
    {
        $this->authorizeBms('settings', 'update');

        $data = $request->validate([
            'logo' => 'required|image|max:2048',
            'variant' => 'required|in:dark,light,default',
        ]);

        $key = match ($data['variant']) {
            'dark' => 'logo_url_dark',
            'light' => 'logo_url_light',
            default => 'logo_url',
        };

        $path = $data['logo']->store('branding', 'public');
        $this->settings->update([$key => BrandAssets::storagePathUrl($path)]);

        $label = match ($data['variant']) {
            'dark' => 'Dark mode logo uploaded.',
            'light' => 'Light mode logo uploaded.',
            default => 'Logo uploaded.',
        };

        return back()->with('success', $label);
    }

    public function uploadFavicon(Request $request): RedirectResponse
    {
        $this->authorizeBms('settings', 'update');
        $path = $request->validate(['favicon' => 'required|file|max:512|mimes:png,jpg,jpeg,gif,ico,svg'])['favicon']
            ->store('branding', 'public');
        $this->settings->update(['favicon_url' => BrandAssets::storagePathUrl($path)]);

        return back()->with('success', 'Favicon uploaded.');
    }

    public function invoice(): View
    {
        $this->authorizeBms('settings', 'read');

        return view('settings/invoice', ['settings' => $this->bmsSettings()]);
    }

    public function invoiceDesign(): View
    {
        $this->authorizeBms('settings', 'read');

        return view('settings/invoice-design', ['settings' => $this->bmsSettings()]);
    }

    public function updateInvoice(Request $request): RedirectResponse
    {
        $this->authorizeBms('settings', 'update');
        $invoice = $this->validateInvoiceFields($request);
        $this->settings->update(['invoice' => $invoice]);

        return back()->with('success', 'Invoice template saved.');
    }

    public function previewInvoice(Request $request): \Illuminate\Http\Response
    {
        $this->authorizeBms('settings', 'read');

        $inv      = $this->validateInvoiceFields($request);
        $settings = array_merge($this->bmsSettings(), ['invoice' => $inv]);
        $engine   = new DocTemplateEngine($settings);
        $docType  = in_array($request->input('_preview_doc'), ['invoice', 'quote', 'receipt'])
            ? $request->input('_preview_doc')
            : 'invoice';

        $html = $engine->sampleHtml($docType, $settings);

        return response($html, 200)->header('Content-Type', 'text/html');
    }

    public function uploadWatermarkImage(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorizeBms('settings', 'update');

        $path = $request->validate(['watermark_image' => 'required|image|max:3072'])['watermark_image']
            ->store('branding', 'public');
        $url = Storage::disk('public')->url($path);

        // Persist immediately so the URL survives across page reloads
        $current = $this->bmsSettings()['invoice'] ?? [];
        $this->settings->update(['invoice' => array_merge($current, ['watermark_image_url' => $url])]);

        return response()->json(['url' => $url]);
    }

    /** @return array<string, mixed> */
    private function validateInvoiceFields(Request $request): array
    {
        $data = $request->validate([
            // Header & layout
            'show_header'           => 'nullable|boolean',
            'show_logo'             => 'nullable|boolean',
            'show_company_name'     => 'nullable|boolean',
            'show_tagline'          => 'nullable|boolean',
            'tagline_text'          => 'nullable|string|max:200',
            'tagline_style'         => 'nullable|string|in:plain,colored',
            'tagline_item_color'    => 'nullable|string|max:20',
            'tagline_divider'       => 'nullable|string|max:3',
            'tagline_divider_color' => 'nullable|string|max:20',
            'header_title'       => 'nullable|string|max:80',
            'layout'             => 'nullable|string|in:classic,modern,minimal,formal',
            'accent_color'       => 'nullable|string|max:20',
            'header_bg'          => 'nullable|string|max:20',
            'header_text_color'  => 'nullable|string|max:20',
            // Typography
            'font_size'          => 'nullable|integer|min:10|max:18',
            'text_color'         => 'nullable|string|max:20',
            // Logo
            'logo_size'          => 'nullable|integer|min:24|max:120',
            'logo_greyscale'     => 'nullable|boolean',
            // Table
            'table_border'       => 'nullable|boolean',
            'table_striped'      => 'nullable|boolean',
            'table_header_bg'    => 'nullable|string|max:20',
            'table_header_color' => 'nullable|string|max:20',
            // Footer
            'show_footer'        => 'nullable|boolean',
            'footer_text'        => 'nullable|string|max:200',
            'footer_bg'          => 'nullable|string|max:30',
            'footer_text_color'  => 'nullable|string|max:20',
            // Signature
            'show_signature'     => 'nullable|boolean',
            'signature_label'    => 'nullable|string|max:80',
            'signature_position' => 'nullable|string|in:left,center,right',
            // Watermark
            'watermark_type'      => 'nullable|string|in:none,text,image',
            'watermark_text'      => 'nullable|string|max:40',
            'watermark_image_url' => 'nullable|string|max:1000',
            'watermark_opacity'   => 'nullable|numeric|min:0.01|max:1.0',
            'watermark_rotation'  => 'nullable|numeric|min:-180|max:180',
            // Payment & terms
            'mpesa_paybill'      => 'nullable|string|max:20',
            'mpesa_account_hint' => 'nullable|string|max:120',
            'terms_default'      => 'nullable|string|max:2000',
            'show_vat_column'    => 'nullable|boolean',
            // Quote
            'quote_validity_days' => 'nullable|integer|min:1|max:365',
            'quote_footer_text'   => 'nullable|string|max:200',
            // Page margins (cm) — negative values allowed for bleed/overlap effects
            'margin_top'    => 'nullable|numeric|min:-5|max:10',
            'margin_right'  => 'nullable|numeric|min:-5|max:10',
            'margin_bottom' => 'nullable|numeric|min:-5|max:10',
            'margin_left'   => 'nullable|numeric|min:-5|max:10',
        ]);

        // Normalise boolean checkboxes (unchecked = absent from POST)
        foreach (['show_header', 'show_logo', 'show_company_name', 'show_tagline', 'logo_greyscale', 'table_border', 'table_striped', 'show_footer', 'show_signature', 'show_vat_column'] as $bool) {
            $data[$bool] = $request->boolean($bool);
        }

        return $data;
    }

    public function finance(): View
    {
        $this->authorizeBms('settings', 'read');

        return view('settings/finance', ['settings' => $this->bmsSettings()]);
    }

    public function numbering(): View
    {
        $this->authorizeBms('settings', 'read');
        $settings   = $this->bmsSettings();
        $nb         = $settings['numbering'] ?? [];
        $lastJob    = \App\Models\PrintJob::query()->orderByDesc('id')->value('id');
        $jobStart   = (int) ($nb['job_start'] ?? 10001);
        $nextJobNum = $jobStart;
        if ($lastJob && preg_match('/(\d+)$/', $lastJob, $m)) {
            $nextJobNum = (int) $m[1] + 1;
        }
        $nextQuoteNum = (\App\Models\Quote::query()->max('id') ?? 0) + 1;
        $totalJobs   = \App\Models\PrintJob::query()->count();
        $totalQuotes = \App\Models\Quote::query()->count();

        return view('settings/numbering', compact('settings', 'nextJobNum', 'nextQuoteNum', 'totalJobs', 'totalQuotes'));
    }

    public function updateNumbering(Request $request): RedirectResponse
    {
        $this->authorizeBms('settings', 'update');
        $data = $request->validate([
            'numbering.job_prefix'      => 'required|string|max:10|alpha_num',
            'numbering.job_start'       => 'required|integer|min:1|max:9999999',
            'numbering.job_pad'         => 'required|integer|min:3|max:8',
            'numbering.job_per_branch'  => 'nullable',
            'numbering.quote_prefix'    => 'required|string|max:10|alpha_num',
            'numbering.quote_pad'       => 'required|integer|min:3|max:8',
            'numbering.invoice_prefix'  => 'nullable|string|max:10',
            'numbering.receipt_prefix'  => 'nullable|string|max:10',
        ]);

        // Flatten nested 'numbering' array from dot notation
        $nb = $data['numbering'];
        $nb['job_per_branch'] = (bool) ($request->input('numbering.job_per_branch') ?? false);

        $this->settings->update(['numbering' => $nb]);

        return back()->with('success', 'Numbering settings saved.');
    }

    public function updateFinance(Request $request): RedirectResponse
    {
        $this->authorizeBms('settings', 'update');
        $data = $request->validate([
            'vat_rate'          => 'required|numeric|min:0|max:100',
            'currency'          => 'required|string|max:10',
            'currency_symbol'   => 'required|string|max:10',
            'payment_methods_raw' => 'nullable|string|max:500',
        ]);

        // Convert comma-separated payment methods string to array
        $raw = $data['payment_methods_raw'] ?? '';
        $methods = array_values(array_filter(array_map('trim', explode(',', $raw))));
        unset($data['payment_methods_raw']);
        if (! empty($methods)) {
            $data['payment_methods'] = $methods;
        }

        $this->settings->update($data);

        return back()->with('success', 'Finance settings saved.');
    }

    public function email(): View
    {
        $this->authorizeBms('settings', 'read');

        return view('settings/email', ['settings' => $this->bmsSettings()]);
    }

    public function updateEmail(Request $request): RedirectResponse
    {
        $this->authorizeBms('settings', 'update');

        $validated = $request->validate([
            'host'         => 'nullable|string|max:120',
            'port'         => 'nullable|integer|min:1|max:65535',
            'encryption'   => 'nullable|string|in:tls,ssl,starttls,',
            'username'     => 'nullable|string|max:120',
            'password'     => 'nullable|string|max:500',
            'from_name'    => 'nullable|string|max:120',
            'from_address' => 'nullable|email|max:120',
        ]);

        $current = $this->bmsSettings()['email_settings'] ?? [];

        // Keep existing password if field is blank
        if (empty($validated['password'])) {
            $validated['password'] = $current['password'] ?? '';
        }

        $emailSettings = array_merge($current, $validated, [
            'notifications' => [
                'job_created' => $request->boolean('notif_job_created'),
                'job_updated' => $request->boolean('notif_job_updated'),
                'quote_sent'  => $request->boolean('notif_quote_sent'),
            ],
        ]);

        $this->settings->update(['email_settings' => $emailSettings]);

        return back()->with('success', 'Email settings saved.');
    }

    public function testEmail(Request $request): RedirectResponse
    {
        $this->authorizeBms('settings', 'update');

        $to       = $request->validate(['test_to' => 'required|email'])['test_to'];
        $settings = $this->bmsSettings();

        if (! BmsMailer::configure($settings)) {
            return back()->with('test_error', 'SMTP is not configured. Please enter your SMTP settings first.');
        }

        try {
            Mail::to($to)->send(new TestEmailMail($settings));
            return back()->with('test_ok', "Test email sent to {$to}. Check your inbox!");
        } catch (\Throwable $e) {
            return back()->with('test_error', 'Failed: ' . $e->getMessage());
        }
    }

    public function emailTemplates(Request $request): View
    {
        $this->authorizeBms('settings', 'read');

        return view('settings/email_templates', ['settings' => $this->bmsSettings()]);
    }

    public function updateEmailTemplate(Request $request): RedirectResponse
    {
        $this->authorizeBms('settings', 'update');

        $type = $request->input('tpl_type');
        $allowed = ['job_created', 'job_status', 'job_invoice', 'quote_sent'];
        if (! in_array($type, $allowed, true)) {
            return back()->withErrors(['tpl_type' => 'Invalid template type.']);
        }

        $fields = $request->validate([
            'subject'       => 'nullable|string|max:200',
            'greeting'      => 'nullable|string|max:120',
            'intro'         => 'nullable|string|max:500',
            'closing'       => 'nullable|string|max:500',
            'sign_off'      => 'nullable|string|max:120',
            'validity_note' => 'nullable|string|max:500',
        ]);

        $current   = $this->bmsSettings()['email_templates'] ?? [];
        $current[$type] = array_filter(
            array_merge($current[$type] ?? [], $fields),
            fn ($v) => $v !== null
        );

        $this->settings->update(['email_templates' => $current]);

        return redirect()->route('bms.settings.email-templates', ['tpl' => $type])
            ->with('success', 'Email template saved.');
    }

    public function resetEmailTemplate(string $type): RedirectResponse
    {
        $this->authorizeBms('settings', 'update');

        $allowed = ['job_created', 'job_status', 'job_invoice', 'quote_sent'];
        if (! in_array($type, $allowed, true)) {
            return back()->withErrors(['tpl_type' => 'Invalid template type.']);
        }

        $defaults = BmsSettingsDefaults::all()['email_templates'][$type] ?? [];
        $current  = $this->bmsSettings()['email_templates'] ?? [];
        $current[$type] = $defaults;

        $this->settings->update(['email_templates' => $current]);

        return redirect()->route('bms.settings.email-templates', ['tpl' => $type])
            ->with('success', 'Template reset to default.');
    }

    public function roles(Request $request): View
    {
        $this->authorizeBms('settings', 'read');

        $settings = $this->bmsSettings();
        $roles    = $settings['roles'] ?? BmsSettingsDefaults::all()['roles'];
        $groups   = BmsSettingsDefaults::resourceGroups();
        $editRole = $request->query('edit');
        $addNew   = $request->query('add') === '1';

        return view('settings/roles', compact('roles', 'groups', 'editRole', 'addNew'));
    }

    public function storeRole(Request $request): RedirectResponse
    {
        $this->authorizeBms('settings', 'update');
        $validated = $request->validate(['name' => 'required|string|max:60']);

        $name = trim($validated['name']);
        if ($name === 'Admin') {
            return back()->withErrors(['name' => 'Cannot create a role named Admin.']);
        }

        $settings = $this->bmsSettings();
        $roles    = $settings['roles'] ?? BmsSettingsDefaults::all()['roles'];

        if (isset($roles[$name])) {
            return back()->withErrors(['name' => 'A role with that name already exists.']);
        }

        $roles[$name] = [
            'perms'       => $this->parsePerms($request),
            'allBranches' => $request->boolean('allBranches'),
        ];
        $this->settings->update(['roles' => $roles]);

        return redirect()->route('bms.settings.roles', ['edit' => $name])
            ->with('success', "Role '{$name}' created.");
    }

    public function updateRole(Request $request, string $name): RedirectResponse
    {
        $this->authorizeBms('settings', 'update');
        $name = urldecode($name);

        $settings = $this->bmsSettings();
        $roles    = $settings['roles'] ?? BmsSettingsDefaults::all()['roles'];

        if (! isset($roles[$name])) {
            return back()->withErrors(['role' => 'Role not found.']);
        }

        $newName = trim($request->input('new_name', $name)) ?: $name;
        if ($newName === 'Admin') {
            return back()->withErrors(['new_name' => 'Cannot rename a role to Admin.']);
        }

        $roleData = [
            'perms'       => $this->parsePerms($request),
            'allBranches' => $request->boolean('allBranches'),
        ];

        if ($newName !== $name) {
            User::where('role', $name)->update(['role' => $newName]);
            unset($roles[$name]);
        }

        $roles[$newName] = $roleData;
        $this->settings->update(['roles' => $roles]);

        return redirect()->route('bms.settings.roles', ['edit' => $newName])
            ->with('success', "Role '{$newName}' saved.");
    }

    public function destroyRole(string $name): RedirectResponse
    {
        $this->authorizeBms('settings', 'delete');
        $name = urldecode($name);

        if ($name === 'Admin') {
            return back()->withErrors(['role' => 'Cannot delete the Admin role.']);
        }

        $inUse = User::where('role', $name)->count();
        if ($inUse > 0) {
            return back()->withErrors(['role' => "Role '{$name}' is assigned to {$inUse} user(s). Reassign them first."]);
        }

        $settings = $this->bmsSettings();
        $roles    = $settings['roles'] ?? BmsSettingsDefaults::all()['roles'];

        if (! isset($roles[$name])) {
            return back()->withErrors(['role' => 'Role not found.']);
        }

        unset($roles[$name]);
        $this->settings->update(['roles' => $roles]);

        return back()->with('success', "Role '{$name}' deleted.");
    }

    private function parsePerms(Request $request): array
    {
        $validGroups  = array_keys(BmsSettingsDefaults::resourceGroups());
        $validActions = ['read', 'write', 'create'];
        $perms        = [];

        foreach ($request->input('perms', []) as $group => $actions) {
            if (in_array($group, $validGroups, true) && is_array($actions)) {
                $perms[$group] = array_values(array_intersect($actions, $validActions));
            }
        }

        return $perms;
    }
}


