@php
  $settingsTabs = [
    ['route' => 'bms.settings.company',  'label' => 'Company'],
    ['route' => 'bms.settings.branches', 'label' => 'Branches'],
    ['route' => 'bms.settings.branding', 'label' => 'Branding'],
    ['route' => 'bms.settings.finance',  'label' => 'Finance'],
    ['route' => 'bms.settings.invoice',  'label' => 'Invoice'],
    ['route' => 'bms.settings.email',           'label' => 'Email'],
    ['route' => 'bms.settings.email-templates', 'label' => 'Email Templates'],
    ['route' => 'bms.settings.roles',      'label' => 'Roles'],
    ['route' => 'bms.settings.numbering', 'label' => 'Numbering'],
  ];
@endphp
<div class="tabs">
  @foreach($settingsTabs as $tab)
    <a href="{{ route($tab['route']) }}" class="tab-item {{ request()->routeIs($tab['route']) ? 'active' : '' }}">{{ $tab['label'] }}</a>
  @endforeach
</div>
