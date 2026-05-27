<?php

namespace App\Support;

/**
 * Generates document CSS and HTML sections (header, footer, signature) from
 * the BMS invoice settings configuration.  Used by invoice, quote, and receipt
 * PDF templates as well as the web invoice view.
 */
class DocTemplateEngine
{
    private array $cfg;
    private array $co;
    private bool  $forWeb = false;

    public function __construct(array $settings)
    {
        $inv = $settings['invoice'] ?? [];

        $this->co = [
            'name'    => $settings['company_name'] ?? 'Quick Prints',
            'tagline' => $settings['company_tagline'] ?? '',
            'address' => $settings['address'] ?? '',
            'phone'   => $settings['phone'] ?? '',
            'email'   => $settings['email'] ?? '',
            'website' => $settings['website'] ?? '',
            'logo'    => BrandAssets::logoForTheme($settings, 'light') ?? '',
        ];

        $this->cfg = array_merge([
            'layout'              => 'classic',
            'show_header'         => true,
            'header_title'        => strtoupper($this->co['name']),
            'accent_color'        => $settings['brand_color'] ?? '#b91c1c',
            'header_bg'           => '#1e293b',
            'header_text_color'   => '#ffffff',
            'font_size'           => '13',
            'text_color'          => '#111111',
            'logo_size'           => '50',
            'logo_greyscale'      => false,
            'table_striped'       => false,
            'table_border'        => true,
            'table_header_bg'     => '',
            'table_header_color'  => '#ffffff',
            'show_footer'         => true,
            'footer_text'         => 'Thank you for your business!',
            'footer_bg'           => 'transparent',
            'footer_text_color'   => '#999999',
            'show_signature'      => false,
            'signature_label'     => 'Authorized Signature',
            'signature_position'  => 'right',
            'show_logo'           => true,
            'show_company_name'   => true,
            'show_tagline'        => true,
            // Tagline text override (blank = use company_tagline from company settings)
            'tagline_text'         => '',
            // Tagline style
            'tagline_style'        => 'plain',  // 'plain' | 'colored'
            'tagline_item_color'   => '',        // empty = use accent
            'tagline_divider'      => '|',       // '|' | '·' | '•'
            'tagline_divider_color'=> '',        // empty = use accent
            'watermark_type'      => 'text',   // 'none' | 'text' | 'image'
            'watermark_text'      => '',
            'watermark_image_url' => '',
            'watermark_opacity'   => '0.06',
            'watermark_rotation'  => '-30',
            'quote_validity_days' => 30,
            'quote_footer_text'   => 'This is a quotation, not a tax invoice.',
            'show_vat_column'     => true,
            'mpesa_paybill'       => '',
            'mpesa_account_hint'  => '',
            'terms_default'       => '',
            // Page margins (cm)
            'margin_top'          => '1.5',
            'margin_right'        => '1.5',
            'margin_bottom'       => '1.5',
            'margin_left'         => '1.5',
        ], $inv);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->cfg[$key] ?? $default;
    }

    /**
     * Convert a local storage URL to a base64 data URI so dompdf can embed it
     * without making HTTP requests (which fail on localhost).
     * Falls back to the original URL if the file cannot be found.
     */
    public static function logoDataUri(string $url): string
    {
        if ($url === '') return '';

        // Match /storage/... path in any form of the URL
        if (preg_match('#/storage/(.+)$#', $url, $m)) {
            $path = storage_path('app/public/' . $m[1]);
            if (file_exists($path) && is_readable($path)) {
                $mime = mime_content_type($path) ?: 'image/png';
                return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
            }
        }

        return $url;
    }

    public function company(string $key): string
    {
        return (string) ($this->co[$key] ?? '');
    }

    public function accent(): string
    {
        return $this->cfg['accent_color'] ?? '#b91c1c';
    }

    public function layout(): string
    {
        return $this->cfg['layout'] ?? 'classic';
    }

    private function tableHeaderBg(): string
    {
        $bg = trim($this->cfg['table_header_bg'] ?? '');
        return $bg !== '' ? $bg : $this->accent();
    }

    /**
     * Return page margin as a CSS shorthand string (e.g. "1.5cm 1.5cm 1.5cm 1.5cm").
     * Safe to use in @page rules, doc-wrap padding, and negative-margin bleeds.
     */
    public function marginShorthand(): string
    {
        $t = (float) ($this->cfg['margin_top']    ?? 1.5);
        $r = (float) ($this->cfg['margin_right']  ?? 1.5);
        $b = (float) ($this->cfg['margin_bottom'] ?? 1.5);
        $l = (float) ($this->cfg['margin_left']   ?? 1.5);
        return "{$t}cm {$r}cm {$b}cm {$l}cm";
    }

    /** Individual margin values keyed by side. */
    private function margins(): array
    {
        return [
            't' => (float) ($this->cfg['margin_top']    ?? 1.5),
            'r' => (float) ($this->cfg['margin_right']  ?? 1.5),
            'b' => (float) ($this->cfg['margin_bottom'] ?? 1.5),
            'l' => (float) ($this->cfg['margin_left']   ?? 1.5),
        ];
    }

    /**
     * Render the tagline string, optionally as coloured segments.
     * When style is 'colored', splits by any separator found in the text and
     * re-joins using the configured divider character and per-element colours.
     */
    private function renderTagline(string $tagline): string
    {
        if (trim($tagline) === '') return '';

        if (($this->cfg['tagline_style'] ?? 'plain') !== 'colored') {
            return htmlspecialchars($tagline);
        }

        // Detect whatever separator the tagline contains and split on it (u flag for UTF-8)
        $parts = preg_split('/\s*[|·•]\s*/u', $tagline, -1, PREG_SPLIT_NO_EMPTY);

        $itemColor    = trim($this->cfg['tagline_item_color']    ?? '') ?: $this->accent();
        $dividerChar  = $this->cfg['tagline_divider']            ?? '|';
        $dividerColor = trim($this->cfg['tagline_divider_color'] ?? '') ?: $this->accent();

        if (count($parts) <= 1) {
            // No separator found — colour the whole thing as one segment
            return '<span style="color:' . htmlspecialchars($itemColor, ENT_QUOTES) . ';font-weight:700;">'
                . htmlspecialchars($tagline) . '</span>';
        }

        $itemHtmls = array_map(
            fn(string $p) => '<span style="color:' . htmlspecialchars($itemColor, ENT_QUOTES) . ';font-weight:700;">'
                . htmlspecialchars(trim($p)) . '</span>',
            $parts
        );

        $sep = '<span style="color:' . htmlspecialchars($dividerColor, ENT_QUOTES) . ';margin:0 4px;">'
            . htmlspecialchars($dividerChar) . '</span>';

        return implode($sep, $itemHtmls);
    }

    /** Generate the full CSS block for a document. */
    public function css(bool $forWeb = false): string
    {
        $this->forWeb = $forWeb;
        $accent    = $this->accent();
        $thBg      = $this->tableHeaderBg();
        $thColor   = $this->cfg['table_header_color'] ?? '#ffffff';
        $textColor = $this->cfg['text_color'] ?? '#111111';
        $fontSize  = intval($this->cfg['font_size'] ?? 13) . 'px';
        $layout    = $this->layout();
        $headerBg  = $this->cfg['header_bg'] ?? '#1e293b';
        $headerTxt = $this->cfg['header_text_color'] ?? '#ffffff';

        $m   = $this->margins();
        $mSh = $this->marginShorthand();   // e.g. "1.5cm 1.5cm 1.5cm 1.5cm"

        // Both PDF and web use doc-wrap padding for margins.
        // PDF: also zero out @page margin so dompdf doesn't double-apply spacing.
        // Web: no @page rule needed.
        $pageRule  = $forWeb ? '' : "@page { margin: 0; size: A4 portrait; }\n";
        if ($forWeb) {
            $wrapStyle = "max-width: 720px; margin: 0 auto; padding: {$mSh};";
        } else {
            // Add clearance below content so the fixed footer never overlaps the last line.
            $footerClearance = ($this->cfg['show_footer'] ?? true) ? 1.5 : 0;
            $bPad = ($m['b'] + $footerClearance) . 'cm';
            $wrapStyle = "max-width: none; padding: {$m['t']}cm {$m['r']}cm {$bPad} {$m['l']}cm; margin: 0;";
        }

        $css = "
{$pageRule}* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: {$fontSize}; color: {$textColor}; background: #fff; }
.doc-wrap { {$wrapStyle} }

/* Header */
.doc-header { display: table; width: 100%; table-layout: fixed; }
.doc-header-left { display: table-cell; vertical-align: top; width: 62%; }
.doc-header-right { display: table-cell; vertical-align: top; text-align: right; width: 38%; padding-left: 12px; }
.doc-company-name { font-size: 20px; font-weight: 700; letter-spacing: 1px; }
.doc-company-sub { font-size: 11px; color: #666; margin-top: 4px; line-height: 1.7; }
.doc-ref { font-size: 22px; font-weight: 700; font-family: monospace; }
.doc-ref-sub { font-size: 11px; color: #666; margin-top: 4px; line-height: 1.7; }

/* Divider */
.doc-divider { border: none; border-top: 2px solid {$accent}; margin: 18px 0 24px; }

/* Info grid */
.info-grid { display: table; width: 100%; margin-bottom: 24px; table-layout: fixed; }
.info-col { display: table-cell; width: 50%; vertical-align: top; }
.info-col + .info-col { padding-left: 24px; }
.info-title { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #888; margin-bottom: 6px; }
.info-value { font-size: {$fontSize}; line-height: 1.8; }
.info-value strong { font-weight: 700; }

/* Tables */
table { width: 100%; border-collapse: collapse; }
thead th {
    font-size: 10px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .5px; color: {$thColor}; background: {$thBg};
    padding: 9px 10px; text-align: left;
}
tbody td { font-size: {$fontSize}; padding: 10px; border-bottom: 1px solid #f0f0f0; vertical-align: top; }
tbody tr:last-child td { border-bottom: none; }
.text-right { text-align: right; }
.text-center { text-align: center; }
.mono { font-family: monospace; }
";

        if ($this->cfg['table_border'] ?? true) {
            $css .= "
table { border: 1px solid #e5e7eb; }
thead th + th { border-left: 1px solid rgba(255,255,255,.2); }
tbody td + td { border-left: 1px solid #f0f0f0; }
";
        }

        if ($this->cfg['table_striped'] ?? false) {
            $css .= "tbody tr:nth-child(even) td { background: #f9fafb; }\n";
        }

        $css .= "
/* Totals */
.totals-wrap { margin-top: 16px; }
.totals-table { width: 280px; border-collapse: collapse; margin-left: auto; }
.totals-table td { padding: 5px 2px; font-size: {$fontSize}; border: none; }
.totals-table td:last-child { text-align: right; font-family: monospace; }
.totals-table .total-final td { border-top: 2px solid #111; font-weight: 700; font-size: 15px; padding-top: 8px; }

/* Badges */
.badge { display: inline-block; padding: 3px 10px; border-radius: 99px; font-size: 11px; font-weight: 700; }
.badge-paid     { background: #d1fae5; color: #065f46; }
.badge-unpaid   { background: #fee2e2; color: #991b1b; }
.badge-draft    { background: #e5e7eb; color: #374151; }
.badge-sent     { background: #dbeafe; color: #1d4ed8; }
.badge-approved { background: #d1fae5; color: #065f46; }
.badge-accepted { background: #d1fae5; color: #065f46; }
.badge-declined { background: #fee2e2; color: #991b1b; }

/* Pay / Terms box */
.pay-box { background: #f9fafb; border-radius: 6px; padding: 14px; font-size: 12px; margin-top: 20px; line-height: 1.9; }
.pay-box strong { font-size: 10px; text-transform: uppercase; letter-spacing: .4px; color: #888; display: block; margin-bottom: 3px; }

/* Receipt stamp */
.receipt-stamp { display: inline-block; border: 3px solid #065f46; color: #065f46; padding: 4px 14px; border-radius: 4px; font-size: 16px; font-weight: 700; letter-spacing: 2px; margin-top: 6px; }

/* Footer */
.doc-footer { border-top: 1px solid #e5e7eb; font-size: 11px; text-align: center; line-height: 1.8; }

/* Signature */
.doc-signature { margin-top: 40px; }
.signature-line { display: inline-block; width: 200px; border-top: 1px solid #333; padding-top: 6px; font-size: 11px; color: #555; }

/* Reports */
.kpi-row { display: table; width: 100%; margin-bottom: 24px; }
.kpi-cell { display: table-cell; text-align: center; padding: 16px 12px; border: 1px solid #e5e7eb; }
.kpi-cell + .kpi-cell { border-left: none; }
.kpi-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #888; }
.kpi-value { font-size: 20px; font-weight: 700; margin-top: 4px; color: {$accent}; }
.kpi-sub { font-size: 11px; color: #888; margin-top: 2px; }
.section-title { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #666; margin: 20px 0 10px; }
.bar-row { display: table; width: 100%; margin-bottom: 6px; }
.bar-label { display: table-cell; font-size: 12px; width: 130px; vertical-align: middle; }
.bar-track { display: table-cell; vertical-align: middle; padding-left: 8px; }
.bar-fill { height: 8px; border-radius: 4px; background: {$accent}; }
.bar-count { display: table-cell; font-size: 12px; text-align: right; width: 60px; vertical-align: middle; color: #666; }
.page-break { page-break-after: always; }
";

        // Footer positioning: fixed at page bottom in PDF, inline after content on web.
        if ($forWeb) {
            $css .= ".doc-footer { margin-top: 32px; padding: 14px 0; }\n";
        } else {
            $css .= ".doc-footer { position: fixed; bottom: 0; left: 0; right: 0; width: 100%; padding: 10px {$m['r']}cm; }\n";
        }

        // Layout-specific overrides
        if ($layout === 'modern') {
            // In PDF mode the band fills the content area (no doc-wrap padding to bleed through).
            // In web mode negative margins equal to the configured page margins bleed the band
            // to the edges of the doc-wrap, matching the PDF look visually.
            if ($forWeb) {
                $bandMargin = "-{$m['t']}cm -{$m['r']}cm 28px -{$m['l']}cm";
                $bandPad    = "{$m['t']}cm {$m['r']}cm";
                $bandWidth  = "calc(100% + {$m['r']}cm + {$m['l']}cm)";
            } else {
                $bandMargin = '0 0 28px 0';
                $bandPad    = '24px 28px';
                $bandWidth  = '100%';
            }
            $css .= "
.doc-header-band {
    background: {$headerBg}; color: {$headerTxt};
    padding: {$bandPad}; margin: {$bandMargin};
    display: table; width: {$bandWidth};
}
.doc-header-band .doc-header-left { display: table-cell; vertical-align: middle; }
.doc-header-band .doc-header-right { display: table-cell; vertical-align: middle; text-align: right; }
.doc-header-band .doc-company-name { color: {$headerTxt}; }
.doc-header-band .doc-company-sub { color: rgba(255,255,255,.65); }
.doc-header-band .doc-ref { color: {$headerTxt}; }
.doc-header-band .doc-ref-sub { color: rgba(255,255,255,.65); }
.doc-divider { border-color: {$accent}; }
";
        } elseif ($layout === 'minimal') {
            $css .= "
.doc-company-name { font-size: 15px; letter-spacing: 0; font-weight: 700; }
.doc-ref { font-size: 18px; }
.doc-divider { border-color: #e5e7eb; border-width: 1px; }
";
        } elseif ($layout === 'formal') {
            // ── Formal: serif professional layout ───────────────────────────────────────
            $css .= "
body { font-family: 'DejaVu Serif', 'Times New Roman', Times, serif; }
/* Header */
.fml-hdr { display: table; width: 100%; table-layout: fixed; margin-bottom: 5mm; }
.fml-brand { display: table-cell; vertical-align: top; width: 50%; }
.fml-meta  { display: table-cell; vertical-align: top; width: 50%; padding-left: 8mm; }
.fml-brand-name { font-size: 26px; font-weight: 900; letter-spacing: -1.5px; line-height: 1; font-family: 'DejaVu Sans', Arial, sans-serif; }
.fml-brand-name .bp { color: {$accent}; }
.fml-tagline { font-size: 9px; font-weight: 700; color: {$accent}; letter-spacing: .3px; margin-top: 3px; font-family: 'DejaVu Sans', Arial, sans-serif; }
.fml-address { margin-top: 7mm; font-size: 11px; line-height: 1.55; color: #222; }
.fml-doc-title { text-align: right; font-size: 26px; font-weight: 900; letter-spacing: 2px; margin-bottom: 3mm; font-family: 'DejaVu Sans', Arial, sans-serif; color: #111; }
table.fml-ref { width: 100%; border-collapse: collapse; font-size: 9px; }
table.fml-ref th, table.fml-ref td { border: 1px solid #111; padding: 5px 6px; text-align: center; }
table.fml-ref th { font-weight: 600; font-family: 'DejaVu Sans', Arial, sans-serif; }
/* Bill-To + Project-Meta row */
.fml-bill-row { display: table; width: 100%; table-layout: fixed; margin: 5mm 0; }
.fml-bill-cell { display: table-cell; vertical-align: top; width: 55%; }
.fml-proj-cell { display: table-cell; vertical-align: bottom; width: 45%; padding-left: 5mm; }
.fml-bill-box { border: 1px solid #111; }
.fml-bill-lbl { border-bottom: 1px solid #111; padding: 5px 9px; font-size: 10px; font-family: 'DejaVu Sans', Arial, sans-serif; }
.fml-bill-body { padding: 9px 11px; font-size: 12px; line-height: 1.5; font-weight: 700; }
table.fml-proj { width: 100%; border-collapse: collapse; font-size: 9px; }
table.fml-proj th, table.fml-proj td { border: 1px solid #111; text-align: center; padding: 5px 7px; }
table.fml-proj th { font-weight: 600; font-family: 'DejaVu Sans', Arial, sans-serif; }
/* Line items */
table.fml-items { width: 100%; border-collapse: collapse; font-size: 11px; }
table.fml-items thead th { border: 1px solid #111; background: #fff; text-align: center; padding: 6px 8px; font-weight: 600; font-size: 9px; font-family: 'DejaVu Sans', Arial, sans-serif; letter-spacing: .02em; }
table.fml-items tbody td { border-left: 1px solid #111; border-right: 1px solid #111; padding: 9px 10px; vertical-align: top; }
table.fml-items tbody tr:last-child td { border-bottom: 1px solid #111; }
table.fml-items td.fi-n { text-align: center; width: 8mm; }
table.fml-items td.fi-q { text-align: center; width: 12mm; }
table.fml-items td.fi-u { text-align: center; width: 18mm; }
table.fml-items td.fi-a { text-align: right; width: 28mm; }
table.fml-items td.fi-v { text-align: center; width: 16mm; }
.fml-item-title { font-weight: 700; line-height: 1.35; font-size: 12px; }
.fml-item-artwork { margin: 8px 0; text-align: center; }
.fml-item-artwork img { max-width: 100%; max-height: 170px; object-fit: contain; border: 1px dashed #bbb; }
.fml-artwork-slot { border: 1px dashed #bbb; padding: 20px; text-align: center; font-size: 9px; color: #aaa; margin: 8px 0; font-family: 'DejaVu Sans', Arial, sans-serif; }
.fml-item-spec { font-size: 10px; line-height: 1.65; color: #333; margin-top: 6px; }
/* Footer: left (VAT + Terms) | right (totals) */
.fml-footer { display: table; width: 100%; border: 1px solid #111; border-top: none; table-layout: fixed; }
.fml-fl { display: table-cell; vertical-align: top; border-right: 1px solid #111; }
.fml-fr { display: table-cell; vertical-align: top; width: 62mm; }
.fml-vat-head { padding: 7px 10px; font-weight: 700; font-size: 12px; font-family: 'DejaVu Sans', Arial, sans-serif; }
.fml-vat-body { border-top: 1px solid #111; padding: 7px 10px; min-height: 10mm; }
table.fml-vat { width: 100%; border-collapse: collapse; font-size: 9px; }
table.fml-vat th, table.fml-vat td { border: 1px solid #bbb; padding: 3px 6px; text-align: center; }
table.fml-vat th { font-family: 'DejaVu Sans', Arial, sans-serif; }
.fml-terms-head { padding: 7px 10px 2px; color: {$accent}; font-weight: 900; font-size: 13px; letter-spacing: .5px; font-family: 'DejaVu Sans', Arial, sans-serif; border-top: 1px solid #111; }
.fml-terms-body { padding: 3px 10px 10px; font-size: 10px; line-height: 1.6; }
.fml-tot { display: table; width: 100%; border-bottom: 1px solid #111; padding: 9px 12px; }
.fml-tot:last-child { border-bottom: none; }
.fml-tot-k { display: table-cell; font-weight: 700; font-size: 12px; font-family: 'DejaVu Sans', Arial, sans-serif; }
.fml-tot-v { display: table-cell; text-align: right; font-size: 12px; }
.fml-tot.grand .fml-tot-k { font-size: 17px; font-weight: 900; }
.fml-tot.grand .fml-tot-v { font-size: 15px; font-weight: 700; }
.doc-footer { margin-top: 8px; padding: 10px 0; }
";
        } else {
            // classic
            $css .= ".doc-company-name { color: {$accent}; }\n";
        }

        // Watermark is rendered entirely via watermarkOverlay() HTML div — no CSS pseudo-elements.

        if ($forWeb) {
            $css .= "
@media print { .toolbar { display: none !important; } .doc { margin: 0; border-radius: 0; box-shadow: none; } }
";
        }

        return $css;
    }

    /**
     * Render the document header.
     *
     * @param string $docType  e.g. 'INVOICE', 'QUOTATION', 'PAYMENT RECEIPT'
     * @param string $docRef   HTML for the ref/date area (right column)
     * @param string $docMeta  Optional HTML after docRef (badges, stamp, etc.)
     */
    public function header(string $docType, string $docRef = '', string $docMeta = ''): string
    {
        if (! ($this->cfg['show_header'] ?? true)) {
            return '';
        }

        $layout  = $this->layout();
        $accent  = $this->accent();
        $showLogo    = (bool) ($this->cfg['show_logo']         ?? true);
        $showName    = (bool) ($this->cfg['show_company_name'] ?? true);
        $showTagline = (bool) ($this->cfg['show_tagline']      ?? true);

        $logoUrl = $this->co['logo'];
        $logoPx  = intval($this->cfg['logo_size'] ?? 50) . 'px';
        $filter  = ($this->cfg['logo_greyscale'] ?? false) ? 'filter:grayscale(1);' : '';
        $logoSrc = ($showLogo && $logoUrl) ? self::logoDataUri($logoUrl) : '';
        $logo    = $logoSrc
            ? "<img src=\"{$logoSrc}\" style=\"height:{$logoPx};margin-bottom:8px;display:block;{$filter}\">"
            : '';

        $title = htmlspecialchars($this->cfg['header_title'] ?? strtoupper($this->co['name']));

        // tagline_text invoice override takes priority over company_tagline
        $taglineText  = trim($this->cfg['tagline_text'] ?? '') ?: ($this->co['tagline'] ?? '');
        $taglineHtml  = $this->renderTagline($taglineText);
        $contactParts = array_filter([
            $this->co['address'] ? htmlspecialchars($this->co['address']) : '',
            $this->co['phone']   ? htmlspecialchars($this->co['phone'])   : '',
            $this->co['email']   ? htmlspecialchars($this->co['email'])   : '',
        ]);
        $sub = implode('<br>', array_filter([$taglineHtml, implode('<br>', $contactParts)]));

        $nameBlock = $showName    ? "<div class=\"doc-company-name\">{$title}</div>" : '';
        $subBlock  = $showTagline ? "<div class=\"doc-company-sub\">{$sub}</div>"    : '';

        $rightHtml = '';
        if ($docRef)  $rightHtml .= "<div class=\"doc-ref-sub\" style=\"margin-top:4px;\">{$docRef}</div>";
        if ($docMeta) $rightHtml .= "<div style=\"margin-top:8px;\">{$docMeta}</div>";

        if ($layout === 'modern') {
            $headerBg  = $this->cfg['header_bg'] ?? '#1e293b';
            $headerTxt = $this->cfg['header_text_color'] ?? '#ffffff';
            return "
<div class=\"doc-header-band\">
  <div class=\"doc-header-left\">
    {$logo}
    {$nameBlock}
    {$subBlock}
  </div>
  <div class=\"doc-header-right\">
    <div class=\"doc-ref\">{$docType}</div>
    {$rightHtml}
  </div>
</div>
";
        }

        if ($layout === 'minimal') {
            return "
<div class=\"doc-header\" style=\"margin-bottom:0;\">
  <div class=\"doc-header-left\">
    {$logo}
    {$nameBlock}
    {$subBlock}
  </div>
  <div class=\"doc-header-right\">
    <div class=\"doc-ref\" style=\"color:#111;font-size:18px;letter-spacing:.5px;\">{$docType}</div>
    {$rightHtml}
  </div>
</div>
<hr class=\"doc-divider\">
";
        }

        if ($layout === 'formal') {
            // Split the $docRef HTML (e.g. '#QP-10042<br>26 May 2026') into ref + date parts
            $refParts = preg_split('/<br\s*\/?>/i', $docRef, 3);
            $ref  = trim(strip_tags($refParts[0] ?? ''));
            $date = trim(strip_tags($refParts[1] ?? ''));
            return $this->formalHeader($docType, $ref, $date, $docMeta);
        }

        // Classic
        return "
<div class=\"doc-header\" style=\"margin-bottom:0;\">
  <div class=\"doc-header-left\">
    {$logo}
    {$nameBlock}
    {$subBlock}
  </div>
  <div class=\"doc-header-right\">
    <div class=\"doc-ref\" style=\"color:#111;\">{$docType}</div>
    {$rightHtml}
  </div>
</div>
<hr class=\"doc-divider\">
";
    }

    /** Render the document footer (returns empty string if disabled). */
    public function footer(string $extraText = ''): string
    {
        if (! ($this->cfg['show_footer'] ?? true)) {
            return '';
        }

        $text    = htmlspecialchars($this->cfg['footer_text'] ?? 'Thank you for your business!');
        $company = htmlspecialchars($this->co['name']);
        $website = $this->co['website'] ? ' · ' . htmlspecialchars($this->co['website']) : '';
        $bg      = $this->cfg['footer_bg'] ?? 'transparent';
        $color   = $this->cfg['footer_text_color'] ?? '#999999';
        $extra   = $extraText ? "<br>{$extraText}" : '';

        return "
<div class=\"doc-footer\" style=\"background:{$bg};color:{$color};\">
  {$text} — {$company}{$website}{$extra}
</div>
";
    }

    /** Render the signature section (returns empty string if disabled). */
    public function signature(): string
    {
        if (! ($this->cfg['show_signature'] ?? false)) {
            return '';
        }

        $label    = htmlspecialchars($this->cfg['signature_label'] ?? 'Authorized Signature');
        $position = $this->cfg['signature_position'] ?? 'right';
        $align    = match ($position) {
            'left'   => 'left',
            'center' => 'center',
            default  => 'right',
        };

        return "
<div class=\"doc-signature\" style=\"text-align:{$align};\">
  <div class=\"signature-line\">{$label}</div>
</div>
";
    }

    /**
     * Returns a watermark overlay div for both text and image types.
     * Place this element immediately after <body> in HTML/PDF templates.
     * Returns empty string when watermark_type is 'none' or required content is absent.
     */
    public function watermarkOverlay(): string
    {
        $type = $this->cfg['watermark_type'] ?? 'text';
        if ($type === 'none') {
            return '';
        }

        $op  = number_format(max(0.01, min(1.0, (float) ($this->cfg['watermark_opacity'] ?? 0.06))), 2);
        $rot = (int) ($this->cfg['watermark_rotation'] ?? -30);

        if ($type === 'text') {
            $wm = trim($this->cfg['watermark_text'] ?? '');
            if ($wm === '') {
                return '';
            }
            $textEsc = htmlspecialchars(strtoupper($wm), ENT_QUOTES);
            return "<div style=\"position:fixed;top:38%;left:0;right:0;width:100%;"
                . "text-align:center;z-index:0;pointer-events:none;"
                . "transform:rotate({$rot}deg);"
                . "font-size:80px;font-weight:900;color:#000;"
                . "opacity:{$op};letter-spacing:8px;text-transform:uppercase;"
                . "white-space:nowrap;\">{$textEsc}</div>\n";
        }

        // image
        $url = trim($this->cfg['watermark_image_url'] ?? '');
        if ($url === '') {
            return '';
        }
        $src = self::logoDataUri($url);  // embed so dompdf can render it
        $srcEsc = htmlspecialchars($src, ENT_QUOTES);
        return "<div style=\"position:fixed;top:25%;left:0;width:100%;"
            . "text-align:center;z-index:0;pointer-events:none;opacity:{$op};\">"
            . "<img src=\"{$srcEsc}\" alt=\"\" style=\"max-width:55%;transform:rotate({$rot}deg);\">"
            . "</div>\n";
    }

    /**
     * Returns an artwork image element for embedding in invoice/quote lines.
     * Returns empty string if no URL is provided.
     */
    public function artworkHtml(string $url, string $alt = 'Artwork'): string
    {
        if ($url === '') {
            return '';
        }

        $escaped = htmlspecialchars($url, ENT_QUOTES);
        $altEsc  = htmlspecialchars($alt, ENT_QUOTES);

        return "<div style=\"margin:14px 0;text-align:center;\">
  <img src=\"{$escaped}\" alt=\"{$altEsc}\" style=\"max-width:100%;max-height:220px;object-fit:contain;border:1px solid #e5e7eb;border-radius:4px;\">
</div>\n";
    }

    /** Split company name into two display parts for the formal two-tone brand header. */
    private function splitBrandName(): array
    {
        $name  = strtoupper(trim($this->co['name']));
        $words = explode(' ', $name);
        if (count($words) <= 1) {
            $mid = (int) ceil(strlen($name) / 2);
            return [substr($name, 0, $mid), substr($name, $mid)];
        }
        $mid = (int) ceil(count($words) / 2);
        return [
            implode(' ', array_slice($words, 0, $mid)),
            implode(' ', array_slice($words, $mid)),
        ];
    }

    /**
     * Render the formal-layout document header.
     *
     * @param string $docType  e.g. 'INVOICE', 'QUOTATION', 'PAYMENT RECEIPT'
     * @param string $ref      Document reference number (e.g. '#QP-10042')
     * @param string $date     Issue date string (e.g. '26 May 2026')
     * @param string $status   Optional status badge HTML
     */
    public function formalHeader(string $docType, string $ref, string $date, string $status = ''): string
    {
        if (! ($this->cfg['show_header'] ?? true)) {
            return '';
        }

        $showLogo    = (bool) ($this->cfg['show_logo']         ?? true);
        $showName    = (bool) ($this->cfg['show_company_name'] ?? true);
        $showTagline = (bool) ($this->cfg['show_tagline']      ?? true);

        [$p1, $p2] = $this->splitBrandName();
        $p1Esc = htmlspecialchars($p1);
        $p2Esc = htmlspecialchars($p2);

        $logoUrl = $this->co['logo'];
        $logoPx  = intval($this->cfg['logo_size'] ?? 50) . 'px';
        $filter  = ($this->cfg['logo_greyscale'] ?? false) ? 'filter:grayscale(1);' : '';
        $logoSrc = ($showLogo && $logoUrl) ? self::logoDataUri($logoUrl) : '';
        $logo    = $logoSrc
            ? "<img src=\"{$logoSrc}\" style=\"height:{$logoPx};display:block;margin-bottom:6px;{$filter}\">"
            : '';

        $taglineText = trim($this->cfg['tagline_text'] ?? '') ?: ($this->co['tagline'] ?? '');
        $tagline     = $this->renderTagline($taglineText);
        $addrParts = array_filter([
            $this->co['address'],
            $this->co['phone'],
            $this->co['email'],
            $this->co['website'],
        ]);
        $addrHtml = implode('<br>', array_map('htmlspecialchars', $addrParts));

        $docTypeEsc  = htmlspecialchars(strtoupper($docType));
        $refEsc      = htmlspecialchars($ref);
        $dateEsc     = htmlspecialchars($date);
        $statusHead  = $status ? '<th>STATUS</th>' : '';
        $statusCell  = $status ? "<td>{$status}</td>" : '';

        $brandNameBlock  = $showName    ? "<div class=\"fml-brand-name\">{$p1Esc}<span class=\"bp\">{$p2Esc}</span></div>" : '';
        $taglineBlock    = $showTagline && $tagline   ? "<div class=\"fml-tagline\">{$tagline}</div>" : '';
        $addrBlock       = $showTagline && $addrHtml  ? "<div class=\"fml-address\">{$addrHtml}</div>" : '';

        return "
<div class=\"fml-hdr\">
  <div class=\"fml-brand\">
    {$logo}
    {$brandNameBlock}
    {$taglineBlock}
    {$addrBlock}
  </div>
  <div class=\"fml-meta\">
    <div class=\"fml-doc-title\">{$docTypeEsc}</div>
    <table class=\"fml-ref\">
      <thead><tr><th>REF NO</th><th>DATE</th>{$statusHead}</tr></thead>
      <tbody><tr><td>{$refEsc}</td><td>{$dateEsc}</td>{$statusCell}</tr></tbody>
    </table>
  </div>
</div>
";
    }

    /** Build a complete formal-layout HTML document for preview. */
    private function sampleFormalHtml(string $docType, string $symbol, float $vatRate): string
    {
        $css    = $this->css(true);
        $amount = 85000;
        $vatAmt = round($amount * $vatRate / (100 + $vatRate), 2);
        $net    = $amount - $vatAmt;
        $date   = date('d M Y');

        $ref    = $docType === 'quote' ? '#QT-0018' : '#QP-10042';
        $badge  = match ($docType) {
            'quote'   => '<span class="badge badge-draft">DRAFT</span>',
            'receipt' => '<span class="badge badge-paid">PAID</span>',
            default   => '<span class="badge badge-paid">PAID</span>',
        };
        $docLabel = match ($docType) {
            'quote'   => 'QUOTATION',
            'receipt' => 'PAYMENT RECEIPT',
            default   => 'INVOICE',
        };

        $headerHtml = $this->formalHeader($docLabel, $ref, $date, $badge);
        $wmOverlay  = $this->watermarkOverlay();
        $footer     = $this->footer($docType === 'quote' ? 'This is a quotation, not a tax invoice.' : '');
        $signature  = $this->signature();

        $billLabel = match ($docType) {
            'quote'   => 'PREPARED FOR',
            'receipt' => 'RECEIVED FROM',
            default   => 'BILL TO',
        };

        $billRow = "
<div class=\"fml-bill-row\">
  <div class=\"fml-bill-cell\">
    <div class=\"fml-bill-box\">
      <div class=\"fml-bill-lbl\">{$billLabel}</div>
      <div class=\"fml-bill-body\">Acme Corporation Ltd<br>P.O. Box 1234, Nairobi<br>+254 722 123 456</div>
    </div>
  </div>
  <div class=\"fml-proj-cell\">
    <table class=\"fml-proj\">
      <thead><tr><th>CATEGORY</th><th>BRANCH</th><th>DEADLINE</th></tr></thead>
      <tbody><tr><td>Wide Format</td><td>Westlands</td><td>" . date('d M Y', strtotime('+7 days')) . "</td></tr></tbody>
    </table>
  </div>
</div>";

        $showVat = $this->cfg['show_vat_column'] ?? true;
        $thead   = '<tr><th style="width:7mm;">#</th><th>DESCRIPTION</th>'
            . '<th style="width:12mm;">QTY</th><th style="width:18mm;">UNIT</th>';
        if ($showVat) {
            $thead .= '<th style="width:16mm;">VAT %</th>'
                . "<th style=\"width:28mm;\">EX-VAT ({$symbol})</th>";
        }
        $thead .= "<th style=\"width:28mm;\">AMOUNT ({$symbol})</th></tr>";

        $tbody = '<tr>'
            . '<td class="fi-n">1</td>'
            . '<td>'
            . '<div class="fml-item-title">Wide-format Banner Print — Vehicle Wrap</div>'
            . '<div class="fml-artwork-slot">[ Artwork / Design ]</div>'
            . '<div class="fml-item-spec">Premium gloss vinyl, UV-resistant ink<br>Size: 3m × 1.5m</div>'
            . '</td>'
            . '<td class="fi-q">1</td>'
            . '<td class="fi-u">Job</td>';
        if ($showVat) {
            $tbody .= '<td class="fi-v">16%</td>'
                . '<td class="fi-a">' . number_format($net, 2) . '</td>';
        }
        $tbody .= '<td class="fi-a">' . number_format($amount, 2) . '</td></tr>';

        $itemsHtml = "<table class=\"fml-items\"><thead>{$thead}</thead><tbody>{$tbody}</tbody></table>";

        $vatTable = '<table class="fml-vat">'
            . '<thead><tr><th>DESCRIPTION</th><th>BASE</th><th>VAT %</th><th>VAT AMT</th></tr></thead>'
            . '<tbody><tr><td>Standard Rate</td><td>' . number_format($net, 2)
            . "</td><td>{$vatRate}%</td><td>" . number_format($vatAmt, 2) . '</td></tr></tbody>'
            . '</table>';

        $terms    = trim($this->cfg['terms_default'] ?? '');
        $termsBody = $terms
            ? nl2br(htmlspecialchars($terms))
            : 'Standard terms apply. Payment due on receipt.';
        $termsHtml = "<div class=\"fml-terms-head\">TERMS &amp; CONDITIONS</div>"
            . "<div class=\"fml-terms-body\">{$termsBody}</div>";

        $mpesa = '';
        if (! empty($this->cfg['mpesa_paybill'])) {
            $pb = htmlspecialchars($this->cfg['mpesa_paybill']);
            $ah = ! empty($this->cfg['mpesa_account_hint'])
                ? ' · Account: ' . htmlspecialchars($this->cfg['mpesa_account_hint']) : '';
            $mpesa = "<div style=\"padding:6px 10px;font-size:10px;border-top:1px solid #111;\">"
                . "<strong>M-Pesa Paybill: {$pb}</strong>{$ah}</div>";
        }

        $formalFooter = "
<div class=\"fml-footer\">
  <div class=\"fml-fl\">
    <div class=\"fml-vat-head\">VAT SUMMARY</div>
    <div class=\"fml-vat-body\">{$vatTable}</div>
    {$termsHtml}
    {$mpesa}
  </div>
  <div class=\"fml-fr\">
    <div class=\"fml-tot\"><div class=\"fml-tot-k\">Subtotal</div><div class=\"fml-tot-v\">{$symbol}&nbsp;" . number_format($net, 2) . "</div></div>
    <div class=\"fml-tot\"><div class=\"fml-tot-k\">VAT ({$vatRate}%)</div><div class=\"fml-tot-v\">{$symbol}&nbsp;" . number_format($vatAmt, 2) . "</div></div>
    <div class=\"fml-tot grand\"><div class=\"fml-tot-k\">TOTAL</div><div class=\"fml-tot-v\">{$symbol}&nbsp;" . number_format($amount, 2) . "</div></div>
  </div>
</div>";

        return "<!DOCTYPE html>
<html lang=\"en\">
<head><meta charset=\"UTF-8\">
<title>" . ucfirst($docType) . " Preview</title>
<style>{$css}</style></head>
<body>
{$wmOverlay}<div class=\"doc-wrap\">
{$headerHtml}
{$billRow}
{$itemsHtml}
{$formalFooter}
{$signature}
{$footer}
</div>
</body>
</html>";
    }

    /**
     * Build a sample invoice HTML for the live preview endpoint.
     * Returns a complete HTML document string.
     */
    public function sampleHtml(string $docType = 'invoice', array $overrideSettings = []): string
    {
        $symbol  = $overrideSettings['currency_symbol'] ?? 'KSh';
        $vatRate = (float) ($overrideSettings['vat_rate'] ?? 16);
        $amount  = 85000;
        $vatAmt  = $amount * $vatRate / (100 + $vatRate);
        $net     = $amount - $vatAmt;

        $css     = $this->css(true); // web mode: use doc-wrap padding, no @page rule

        if ($this->layout() === 'formal') {
            return $this->sampleFormalHtml($docType, $symbol, $vatRate);
        }

        $accent  = $this->accent();

        $docRef  = '#QP-10042<br>' . date('d M Y');
        $badge   = '<span class="badge badge-paid">PAID</span>';

        if ($docType === 'quote') {
            $docRef  = '#QT-0018<br>' . date('d M Y') . '<br>Valid: 30 days';
            $badge   = '<span class="badge badge-draft">DRAFT</span>';
            $header  = $this->header('QUOTATION', $docRef, $badge);
        } elseif ($docType === 'receipt') {
            $header  = $this->header('PAYMENT RECEIPT', '#QP-10042<br>' . date('d M Y'), '<div class="receipt-stamp">PAID</div>');
        } else {
            $header  = $this->header('INVOICE', $docRef, $badge);
        }

        $footer    = $this->footer($docType === 'quote' ? 'This is a quotation, not a tax invoice. Prices valid for 30 days.' : '');
        $signature = $this->signature();

        $items = $docType === 'quote'
            ? "
<tr><td><strong>Wide-format Banner Print</strong></td><td class=\"text-right\">5</td><td class=\"text-right mono\">3,500.00</td><td class=\"text-right\">—</td><td class=\"text-right mono\">17,500.00</td></tr>
<tr><td><strong>Vehicle Branding — Full Wrap</strong></td><td class=\"text-right\">1</td><td class=\"text-right mono\">45,000.00</td><td class=\"text-right\">10%</td><td class=\"text-right mono\">40,500.00</td></tr>
<tr><td><strong>Business Card Design &amp; Print</strong><br><span style=\"font-size:11px;color:#888;\">500 pcs, glossy laminate</span></td><td class=\"text-right\">1</td><td class=\"text-right mono\">8,000.00</td><td class=\"text-right\">—</td><td class=\"text-right mono\">8,000.00</td></tr>
"
            : "
<tr><td><strong>Wide-format Banner Print — Vehicle Wrap</strong><br><span style=\"font-size:11px;color:#888;\">Premium gloss vinyl, UV-resistant ink</span></td><td class=\"text-right\">1</td>" .
($this->cfg['show_vat_column'] ?? true ? "<td class=\"text-right mono\">" . number_format($net, 2) . "</td>" : '') . "
<td class=\"text-right mono\">" . number_format($amount, 2) . "</td></tr>
";

        $thead = $docType === 'quote'
            ? "<tr><th style=\"width:40%;\">Description</th><th class=\"text-right\">Qty</th><th class=\"text-right\">Unit Price ({$symbol})</th><th class=\"text-right\">Disc %</th><th class=\"text-right\">Total ({$symbol})</th></tr>"
            : "<tr><th>Description</th><th class=\"text-right\">Qty</th>" .
              ($this->cfg['show_vat_column'] ?? true ? "<th class=\"text-right\">Ex-VAT ({$symbol})</th>" : '') .
              "<th class=\"text-right\">Amount ({$symbol})</th></tr>";

        $subtotal = $docType === 'quote' ? 66000 : $amount;
        $vatLine  = $docType === 'quote' ? $subtotal * $vatRate / 100 : $vatAmt;
        $total    = $docType === 'quote' ? $subtotal + $vatLine : $amount;

        $totals = "
<div class=\"totals-wrap\">
  <table class=\"totals-table\">
    <tr><td style=\"color:#666;\">Subtotal</td><td class=\"text-right mono\">{$symbol} " . number_format($docType === 'quote' ? $subtotal : $net, 2) . "</td></tr>
    <tr><td style=\"color:#666;\">VAT ({$vatRate}%)</td><td class=\"text-right mono\">{$symbol} " . number_format($vatLine, 2) . "</td></tr>
    <tr class=\"total-final\"><td>TOTAL</td><td class=\"text-right mono\">{$symbol} " . number_format($total, 2) . "</td></tr>
  </table>
</div>";

        $payBox = '';
        if (! empty($this->cfg['mpesa_paybill'])) {
            $payBox = "<div class=\"pay-box\"><strong>Payment Details</strong>M-Pesa Paybill: <strong>{$this->cfg['mpesa_paybill']}</strong>" .
                (! empty($this->cfg['mpesa_account_hint']) ? " &nbsp;·&nbsp; Account: {$this->cfg['mpesa_account_hint']}" : '') . '</div>';
        }
        if (! empty($this->cfg['terms_default'])) {
            $payBox .= '<div class="pay-box" style="margin-top:10px;"><strong>Terms &amp; Conditions</strong>' . nl2br(htmlspecialchars($this->cfg['terms_default'])) . '</div>';
        }

        $billedLabel = $docType === 'quote' ? 'Prepared For' : ($docType === 'receipt' ? 'Received From' : 'Bill To');

        $wmOverlay = $this->watermarkOverlay();

        return "<!DOCTYPE html>
<html lang=\"en\">
<head>
<meta charset=\"UTF-8\">
<title>" . ucfirst($docType) . " Preview</title>
<style>{$css}</style>
</head>
<body>
{$wmOverlay}<div class=\"doc-wrap\">
{$header}

<div class=\"info-grid\">
  <div class=\"info-col\">
    <div class=\"info-title\">{$billedLabel}</div>
    <div class=\"info-value\">
      <strong>Acme Corporation Ltd</strong><br>
      P.O. Box 1234, Nairobi<br>
      +254 722 123 456<br>
      billing@acme.co.ke
    </div>
  </div>
  <div class=\"info-col\">
    <div class=\"info-title\">" . ($docType === 'quote' ? 'Quotation Info' : 'Job Details') . "</div>
    <div class=\"info-value\">
      <strong>Branch:</strong> Westlands<br>
      <strong>Category:</strong> Wide Format<br>
      <strong>Deadline:</strong> " . date('d M Y', strtotime('+7 days')) . "
    </div>
  </div>
</div>

<table>
  <thead>{$thead}</thead>
  <tbody>{$items}</tbody>
</table>

{$totals}
{$payBox}
{$footer}
{$signature}
</div>
</body>
</html>";
    }
}
