{{--
  Invoice template form fields — included by both settings/invoice.blade.php
  (in the BMS layout) and settings/invoice-design.blade.php (full-page).
  Expects: $inv (invoice settings array), $settings (full BMS settings array).
  Does NOT contain <form>, @csrf, @method, submit button, or JS.
--}}

{{-- 1. Header & Layout --}}
<div class="card" style="margin-bottom:12px;">
  <div class="card-header"><div class="card-title">Header &amp; Layout</div></div>
  <div class="form-row cols-2">
    <div class="fld">
      <label>Header Title</label>
      <input type="text" name="header_title" value="{{ old('header_title', $inv['header_title'] ?? 'QUICKPRINTS') }}" placeholder="QUICKPRINTS">
    </div>
    <div class="fld">
      <label>Layout</label>
      <select name="layout">
        @foreach(['classic' => 'Classic', 'modern' => 'Modern (dark band)', 'minimal' => 'Minimal', 'formal' => 'Formal (Professional)'] as $val => $lbl)
          <option value="{{ $val }}" {{ old('layout', $inv['layout'] ?? 'classic') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
        @endforeach
      </select>
    </div>
  </div>
  <div class="form-row cols-2">
    <div class="fld">
      <label>Accent Color</label>
      <div style="display:flex;gap:8px;align-items:center;">
        <input type="color" name="accent_color" value="{{ old('accent_color', $inv['accent_color'] ?? '#f97316') }}"
          style="width:40px;height:34px;border:1px solid var(--border);border-radius:var(--radius);background:none;cursor:pointer;padding:2px;">
        <input type="text" name="accent_color_txt" value="{{ old('accent_color', $inv['accent_color'] ?? '#f97316') }}"
          style="flex:1;" placeholder="#f97316" maxlength="9" oninput="syncColor(this,'accent_color')">
      </div>
    </div>
    <div class="fld">
      <label>Header BG <span style="font-weight:400;text-transform:none;letter-spacing:0;">(modern)</span></label>
      <div style="display:flex;gap:8px;align-items:center;">
        <input type="color" name="header_bg" value="{{ old('header_bg', $inv['header_bg'] ?? '#1e293b') }}"
          style="width:40px;height:34px;border:1px solid var(--border);border-radius:var(--radius);background:none;cursor:pointer;padding:2px;">
        <input type="text" name="header_bg_txt" value="{{ old('header_bg', $inv['header_bg'] ?? '#1e293b') }}"
          style="flex:1;" placeholder="#1e293b" maxlength="9" oninput="syncColor(this,'header_bg')">
      </div>
    </div>
  </div>
  <div class="form-row cols-2">
    <div class="fld">
      <label>Header Text <span style="font-weight:400;text-transform:none;letter-spacing:0;">(modern)</span></label>
      <div style="display:flex;gap:8px;align-items:center;">
        <input type="color" name="header_text_color" value="{{ old('header_text_color', $inv['header_text_color'] ?? '#ffffff') }}"
          style="width:40px;height:34px;border:1px solid var(--border);border-radius:var(--radius);background:none;cursor:pointer;padding:2px;">
        <input type="text" name="header_text_color_txt" value="{{ old('header_text_color', $inv['header_text_color'] ?? '#ffffff') }}"
          style="flex:1;" placeholder="#ffffff" maxlength="9" oninput="syncColor(this,'header_text_color')">
      </div>
    </div>
    <div class="fld">
      <label>Logo Height (px)</label>
      <input type="number" name="logo_size" value="{{ old('logo_size', $inv['logo_size'] ?? 50) }}" min="24" max="120" placeholder="50">
    </div>
  </div>
  @php $chkStyle = "display:flex;align-items:center;gap:9px;font-size:13px;color:var(--text2);cursor:pointer;font-weight:400;text-transform:none;letter-spacing:0;"; @endphp
  <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:6px;">
    <label style="{{ $chkStyle }}">
      <input type="checkbox" name="show_header" value="1" {{ old('show_header', $inv['show_header'] ?? true) ? 'checked' : '' }}>
      Show header block
    </label>
    <label style="{{ $chkStyle }}">
      <input type="checkbox" name="show_logo" value="1" {{ old('show_logo', $inv['show_logo'] ?? true) ? 'checked' : '' }}>
      Show logo
    </label>
    <label style="{{ $chkStyle }}">
      <input type="checkbox" name="show_company_name" value="1" {{ old('show_company_name', $inv['show_company_name'] ?? true) ? 'checked' : '' }}>
      Show company name
    </label>
    <label style="{{ $chkStyle }}">
      <input type="checkbox" name="show_tagline" value="1" {{ old('show_tagline', $inv['show_tagline'] ?? true) ? 'checked' : '' }}>
      Show tagline / sub-info
    </label>
    <label style="{{ $chkStyle }}">
      <input type="checkbox" name="logo_greyscale" value="1" {{ old('logo_greyscale', $inv['logo_greyscale'] ?? false) ? 'checked' : '' }}>
      Logo greyscale
    </label>
  </div>

  {{-- Tagline text override --}}
  <div class="form-row" style="border-top:1px solid var(--border);">
    <div class="fld">
      <label>Tagline Text <span style="font-weight:400;text-transform:none;letter-spacing:0;">(blank = use company tagline)</span></label>
      <input type="text" name="tagline_text"
        value="{{ old('tagline_text', $inv['tagline_text'] ?? '') }}"
        placeholder="{{ $settings['company_tagline'] ?? 'e.g. Printing | Signage | Branding' }}">
    </div>
  </div>

  {{-- Tagline style --}}
  @php $tlStyle = old('tagline_style', $inv['tagline_style'] ?? 'plain'); @endphp
  <div class="form-row" style="border-top:1px solid var(--border);padding-top:12px;">
    <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text3);margin-bottom:10px;">Tagline Style</div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
      <div class="fld">
        <label>Style</label>
        <select name="tagline_style" id="tagline-style-sel" onchange="toggleTaglineStyle(this.value)">
          <option value="plain"   {{ $tlStyle === 'plain'   ? 'selected' : '' }}>Plain text</option>
          <option value="colored" {{ $tlStyle === 'colored' ? 'selected' : '' }}>Coloured segments</option>
        </select>
      </div>
      <div class="fld" id="tl-divider-row" style="{{ $tlStyle !== 'colored' ? 'display:none;' : '' }}">
        <label>Divider character</label>
        <select name="tagline_divider">
          @foreach(['|' => 'Pipe  ( | )', '·' => 'Middle dot  ( · )', '•' => 'Bullet  ( • )'] as $val => $lbl)
            <option value="{{ $val }}" {{ old('tagline_divider', $inv['tagline_divider'] ?? '|') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
          @endforeach
        </select>
      </div>
    </div>
    <div id="tl-color-rows" style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:10px;{{ $tlStyle !== 'colored' ? 'display:none;' : '' }}">
      <div class="fld">
        <label>Item color <span style="font-weight:400;text-transform:none;letter-spacing:0;">(blank = accent)</span></label>
        <div style="display:flex;gap:8px;align-items:center;">
          <input type="color" name="tagline_item_color" value="{{ old('tagline_item_color', $inv['tagline_item_color'] ?? '#b91c1c') }}"
            style="width:40px;height:34px;border:1px solid var(--border);border-radius:var(--radius);background:none;cursor:pointer;padding:2px;">
          <input type="text" name="tagline_item_color_txt" id="tagline_item_color_txt"
            value="{{ old('tagline_item_color', $inv['tagline_item_color'] ?? '') }}"
            style="flex:1;" placeholder="(uses accent)" maxlength="9" oninput="syncColor(this,'tagline_item_color')">
        </div>
      </div>
      <div class="fld">
        <label>Divider color <span style="font-weight:400;text-transform:none;letter-spacing:0;">(blank = accent)</span></label>
        <div style="display:flex;gap:8px;align-items:center;">
          <input type="color" name="tagline_divider_color" value="{{ old('tagline_divider_color', $inv['tagline_divider_color'] ?? '#b91c1c') }}"
            style="width:40px;height:34px;border:1px solid var(--border);border-radius:var(--radius);background:none;cursor:pointer;padding:2px;">
          <input type="text" name="tagline_divider_color_txt" id="tagline_divider_color_txt"
            value="{{ old('tagline_divider_color', $inv['tagline_divider_color'] ?? '') }}"
            style="flex:1;" placeholder="(uses accent)" maxlength="9" oninput="syncColor(this,'tagline_divider_color')">
        </div>
      </div>
    </div>
  </div>
</div>

{{-- 2. Page Margins (high hierarchy — foundational layout) --}}
<div class="card" style="margin-bottom:12px;">
  <div class="card-header"><div class="card-title">Page Margins</div></div>
  <p style="font-size:12px;color:var(--text3);margin-bottom:12px;line-height:1.6;">
    White space between content and page edge on printed PDFs. Values in centimetres. Negative values allowed for bleed effects.
  </p>
  <div style="display:flex;gap:6px;margin-bottom:12px;flex-wrap:wrap;">
    @foreach(['Narrow' => '1.0', 'Normal' => '1.5', 'Wide' => '2.0', 'Extra Wide' => '2.5'] as $lbl => $val)
      <button type="button" onclick="applyMarginPreset('{{ $val }}')"
        style="padding:3px 10px;border-radius:var(--radius);font-size:12px;font-weight:600;border:1px solid var(--border);background:var(--bg3);color:var(--text2);cursor:pointer;">
        {{ $lbl }} ({{ $val }})
      </button>
    @endforeach
  </div>
  {{-- Visual margin diagram + inputs --}}
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
    @foreach(['margin_top' => 'Top', 'margin_right' => 'Right', 'margin_bottom' => 'Bottom', 'margin_left' => 'Left'] as $name => $lbl)
    <div class="fld">
      <label>{{ $lbl }} (cm)</label>
      <input type="number" name="{{ $name }}" id="{{ $name }}"
        value="{{ old($name, $inv[$name] ?? '1.5') }}"
        step="0.25" min="-5" max="10" placeholder="1.5">
    </div>
    @endforeach
  </div>
</div>

{{-- 3. Typography --}}
<div class="card" style="margin-bottom:12px;">
  <div class="card-header"><div class="card-title">Typography</div></div>
  <div class="form-row cols-2">
    <div class="fld">
      <label>Font Size (px)</label>
      <input type="number" name="font_size" value="{{ old('font_size', $inv['font_size'] ?? 13) }}" min="10" max="18" placeholder="13">
    </div>
    <div class="fld">
      <label>Text Color</label>
      <div style="display:flex;gap:8px;align-items:center;">
        <input type="color" name="text_color" value="{{ old('text_color', $inv['text_color'] ?? '#111111') }}"
          style="width:40px;height:34px;border:1px solid var(--border);border-radius:var(--radius);background:none;cursor:pointer;padding:2px;">
        <input type="text" name="text_color_txt" value="{{ old('text_color', $inv['text_color'] ?? '#111111') }}"
          style="flex:1;" placeholder="#111111" maxlength="9" oninput="syncColor(this,'text_color')">
      </div>
    </div>
  </div>
</div>

{{-- 4. Table Style --}}
<div class="card" style="margin-bottom:12px;">
  <div class="card-header"><div class="card-title">Table Style</div></div>
  <div class="form-row cols-2">
    <div class="fld">
      <label>Table Header BG <span style="font-weight:400;text-transform:none;letter-spacing:0;">(blank = accent)</span></label>
      <div style="display:flex;gap:8px;align-items:center;">
        <input type="color" name="table_header_bg" value="{{ old('table_header_bg', $inv['table_header_bg'] ?? '#f97316') }}"
          style="width:40px;height:34px;border:1px solid var(--border);border-radius:var(--radius);background:none;cursor:pointer;padding:2px;">
        <input type="text" name="table_header_bg_txt" value="{{ old('table_header_bg', $inv['table_header_bg'] ?? '') }}"
          style="flex:1;" placeholder="(uses accent)" maxlength="9" oninput="syncColor(this,'table_header_bg')">
      </div>
    </div>
    <div class="fld">
      <label>Table Header Text</label>
      <div style="display:flex;gap:8px;align-items:center;">
        <input type="color" name="table_header_color" value="{{ old('table_header_color', $inv['table_header_color'] ?? '#ffffff') }}"
          style="width:40px;height:34px;border:1px solid var(--border);border-radius:var(--radius);background:none;cursor:pointer;padding:2px;">
        <input type="text" name="table_header_color_txt" value="{{ old('table_header_color', $inv['table_header_color'] ?? '#ffffff') }}"
          style="flex:1;" placeholder="#ffffff" maxlength="9" oninput="syncColor(this,'table_header_color')">
      </div>
    </div>
  </div>
  <div class="form-row cols-2">
    <label style="display:flex;align-items:center;gap:9px;font-size:13px;color:var(--text2);cursor:pointer;font-weight:400;text-transform:none;letter-spacing:0;">
      <input type="checkbox" name="table_border" value="1" {{ old('table_border', $inv['table_border'] ?? true) ? 'checked' : '' }}>
      Show cell borders
    </label>
    <label style="display:flex;align-items:center;gap:9px;font-size:13px;color:var(--text2);cursor:pointer;font-weight:400;text-transform:none;letter-spacing:0;">
      <input type="checkbox" name="table_striped" value="1" {{ old('table_striped', $inv['table_striped'] ?? false) ? 'checked' : '' }}>
      Alternate row shading
    </label>
  </div>
</div>

{{-- 5. Footer --}}
<div class="card" style="margin-bottom:12px;">
  <div class="card-header"><div class="card-title">Footer</div></div>
  <div class="form-row">
    <label style="display:flex;align-items:center;gap:9px;font-size:13px;color:var(--text2);cursor:pointer;font-weight:400;text-transform:none;letter-spacing:0;">
      <input type="checkbox" name="show_footer" value="1" {{ old('show_footer', $inv['show_footer'] ?? true) ? 'checked' : '' }}>
      Show footer
    </label>
  </div>
  <div class="form-row">
    <div class="fld">
      <label>Footer Text</label>
      <input type="text" name="footer_text" value="{{ old('footer_text', $inv['footer_text'] ?? 'Thank you for your business!') }}" placeholder="Thank you for your business!">
    </div>
  </div>
  <div class="form-row cols-2">
    <div class="fld">
      <label>Footer Background</label>
      <div style="display:flex;gap:8px;align-items:center;">
        <input type="color" name="footer_bg" value="{{ old('footer_bg', $inv['footer_bg'] ?? '#ffffff') }}"
          style="width:40px;height:34px;border:1px solid var(--border);border-radius:var(--radius);background:none;cursor:pointer;padding:2px;">
        <input type="text" name="footer_bg_txt" value="{{ old('footer_bg', $inv['footer_bg'] ?? 'transparent') }}"
          style="flex:1;" placeholder="transparent" maxlength="20" oninput="syncColor(this,'footer_bg')">
      </div>
    </div>
    <div class="fld">
      <label>Footer Text Color</label>
      <div style="display:flex;gap:8px;align-items:center;">
        <input type="color" name="footer_text_color" value="{{ old('footer_text_color', $inv['footer_text_color'] ?? '#999999') }}"
          style="width:40px;height:34px;border:1px solid var(--border);border-radius:var(--radius);background:none;cursor:pointer;padding:2px;">
        <input type="text" name="footer_text_color_txt" value="{{ old('footer_text_color', $inv['footer_text_color'] ?? '#999999') }}"
          style="flex:1;" placeholder="#999999" maxlength="9" oninput="syncColor(this,'footer_text_color')">
      </div>
    </div>
  </div>
</div>

{{-- 6. Signature --}}
<div class="card" style="margin-bottom:12px;">
  <div class="card-header"><div class="card-title">Signature</div></div>
  <div class="form-row">
    <label style="display:flex;align-items:center;gap:9px;font-size:13px;color:var(--text2);cursor:pointer;font-weight:400;text-transform:none;letter-spacing:0;">
      <input type="checkbox" name="show_signature" value="1" {{ old('show_signature', $inv['show_signature'] ?? false) ? 'checked' : '' }}>
      Show signature line
    </label>
  </div>
  <div class="form-row cols-2">
    <div class="fld">
      <label>Label</label>
      <input type="text" name="signature_label" value="{{ old('signature_label', $inv['signature_label'] ?? 'Authorized Signature') }}" placeholder="Authorized Signature">
    </div>
    <div class="fld">
      <label>Position</label>
      <select name="signature_position">
        @foreach(['left' => 'Left', 'center' => 'Center', 'right' => 'Right'] as $val => $lbl)
          <option value="{{ $val }}" {{ old('signature_position', $inv['signature_position'] ?? 'right') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
        @endforeach
      </select>
    </div>
  </div>
</div>

{{-- 7. Watermark --}}
@php $wmType = old('watermark_type', $inv['watermark_type'] ?? 'text'); @endphp
<div class="card" style="margin-bottom:12px;">
  <div class="card-header"><div class="card-title">Watermark</div></div>

  {{-- Type selector --}}
  <div class="form-row cols-2">
    <div class="fld">
      <label>Type</label>
      <select name="watermark_type" id="wm-type" onchange="toggleWatermarkType(this.value)">
        @foreach(['none' => 'None', 'text' => 'Text', 'image' => 'Image'] as $val => $lbl)
          <option value="{{ $val }}" {{ $wmType === $val ? 'selected' : '' }}>{{ $lbl }}</option>
        @endforeach
      </select>
    </div>
    <div class="fld">
      <label>Opacity <span style="font-weight:400;text-transform:none;letter-spacing:0;">0.01–1.0</span></label>
      <input type="number" name="watermark_opacity"
        value="{{ old('watermark_opacity', $inv['watermark_opacity'] ?? '0.06') }}"
        step="0.01" min="0.01" max="1.0" placeholder="0.06"
        id="wm-opacity"
        style="{{ $wmType === 'none' ? 'opacity:.4;' : '' }}">
    </div>
  </div>
  <div class="form-row cols-2">
    <div class="fld">
      <label>Rotation (degrees)</label>
      <input type="number" name="watermark_rotation"
        value="{{ old('watermark_rotation', $inv['watermark_rotation'] ?? '-30') }}"
        step="1" min="-180" max="180" placeholder="-30"
        id="wm-rotation"
        style="{{ $wmType === 'none' ? 'opacity:.4;' : '' }}">
    </div>
  </div>

  {{-- Text watermark fields --}}
  <div id="wm-text-row" class="form-row" style="{{ $wmType !== 'text' ? 'display:none;' : '' }}">
    <div class="fld">
      <label>Watermark Text</label>
      <input type="text" name="watermark_text"
        value="{{ old('watermark_text', $inv['watermark_text'] ?? '') }}"
        placeholder="e.g. CONFIDENTIAL">
    </div>
  </div>

  {{-- Image watermark fields --}}
  <div id="wm-image-row" class="form-row" style="{{ $wmType !== 'image' ? 'display:none;' : '' }}">
    <div class="fld">
      <label>Watermark Image</label>
      @if(!empty($inv['watermark_image_url']))
        <img src="{{ $inv['watermark_image_url'] }}" alt="watermark"
          id="wm-preview-img"
          style="max-height:64px;border:1px solid var(--border);border-radius:var(--radius);margin-bottom:8px;opacity:.6;">
      @else
        <img src="" alt="" id="wm-preview-img"
          style="max-height:64px;border:1px solid var(--border);border-radius:var(--radius);margin-bottom:8px;opacity:.6;display:none;">
      @endif
      <input type="hidden" name="watermark_image_url" id="wm-image-url"
        value="{{ old('watermark_image_url', $inv['watermark_image_url'] ?? '') }}">
      <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
        <input type="file" id="wm-image-file" accept="image/png,image/jpeg,image/svg+xml,image/webp"
          style="font-size:12px;color:var(--text2);flex:1;min-width:0;">
        <span id="wm-upload-status" style="font-size:11px;color:var(--text3);white-space:nowrap;"></span>
      </div>
      <p style="font-size:11px;color:var(--text3);margin-top:6px;">PNG with transparency works best. Image is applied at low opacity over the document.</p>
    </div>
  </div>
</div>

{{-- 8. Payment & Terms --}}
<div class="card" style="margin-bottom:12px;">
  <div class="card-header"><div class="card-title">Payment &amp; Terms</div></div>
  <div class="form-row cols-2">
    <div class="fld">
      <label>M-Pesa Paybill</label>
      <input type="text" name="mpesa_paybill" value="{{ old('mpesa_paybill', $inv['mpesa_paybill'] ?? '') }}" placeholder="522522">
    </div>
    <div class="fld">
      <label>Account Hint</label>
      <input type="text" name="mpesa_account_hint" value="{{ old('mpesa_account_hint', $inv['mpesa_account_hint'] ?? '') }}" placeholder="Use invoice number">
    </div>
  </div>
  <div class="form-row">
    <div class="fld">
      <label>Default Terms &amp; Conditions</label>
      <textarea name="terms_default" rows="4" placeholder="Payment due within 30 days...">{{ old('terms_default', $inv['terms_default'] ?? '') }}</textarea>
    </div>
  </div>
  <div class="form-row">
    <label style="display:flex;align-items:center;gap:9px;font-size:13px;color:var(--text2);cursor:pointer;font-weight:400;text-transform:none;letter-spacing:0;">
      <input type="checkbox" name="show_vat_column" value="1" {{ old('show_vat_column', $inv['show_vat_column'] ?? true) ? 'checked' : '' }}>
      Show ex-VAT column on invoice
    </label>
  </div>
</div>

{{-- 9. Quotation --}}
<div class="card" style="margin-bottom:12px;">
  <div class="card-header"><div class="card-title">Quotation</div></div>
  <div class="form-row cols-2">
    <div class="fld">
      <label>Validity (days)</label>
      <input type="number" name="quote_validity_days" value="{{ old('quote_validity_days', $inv['quote_validity_days'] ?? 30) }}" min="1" max="365" placeholder="30">
    </div>
  </div>
  <div class="form-row">
    <div class="fld">
      <label>Quote Footer Note</label>
      <input type="text" name="quote_footer_text" value="{{ old('quote_footer_text', $inv['quote_footer_text'] ?? 'This is a quotation, not a tax invoice.') }}" placeholder="This is a quotation, not a tax invoice.">
    </div>
  </div>
</div>
