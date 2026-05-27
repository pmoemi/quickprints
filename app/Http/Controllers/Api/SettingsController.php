<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BmsSetting;
use App\Support\BrandAssets;
use App\Support\BmsPermissions;
use App\Support\BmsSettingsDefaults;
use App\Support\BranchUsage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function branding(): JsonResponse
    {
        $settings = $this->resolvedSettings();

        return response()->json([
            'company_name' => $settings['company_name'],
            'company_tagline' => $settings['company_tagline'],
            'brand_color' => $settings['brand_color'],
            'brand_color_secondary' => $settings['brand_color_secondary'],
            'logo_url' => BrandAssets::publicUrl($settings['logo_url'] ?? null),
            'logo_url_dark' => BrandAssets::publicUrl($settings['logo_url_dark'] ?? null),
            'logo_url_light' => BrandAssets::publicUrl($settings['logo_url_light'] ?? null),
            'favicon_url' => BrandAssets::faviconUrl($settings),
        ]);
    }

    public function show(Request $request): JsonResponse
    {
        $this->authorizeSettings($request, 'read');

        return response()->json($this->resolvedSettings());
    }

    public function permissions(Request $request): JsonResponse
    {
        $role = $request->user()?->role ?? 'Guest';

        return response()->json([
            'role' => $role,
            'permissions' => BmsPermissions::forRole($role),
        ]);
    }

    public function branchUsage(Request $request, string $name): JsonResponse
    {
        $this->authorizeSettings($request, 'read');

        return response()->json(BranchUsage::count(urldecode($name)));
    }

    public function addBranch(Request $request): JsonResponse
    {
        $this->authorizeSettings($request, 'create', 'branches');

        $payload = $request->validate([
            'name' => 'required|string|max:80',
        ]);

        $name = trim($payload['name']);
        if ($name === '') {
            return response()->json(['message' => 'Branch name is required.'], 422);
        }

        $row = $this->settingsRow();
        $current = BmsSettingsDefaults::merge($row->data ?? []);
        $branches = $current['branches'] ?? [];

        if (in_array($name, $branches, true)) {
            return response()->json(['message' => 'Branch already exists.'], 422);
        }

        $branches[] = $name;
        $current['branches'] = array_values($branches);
        $row->data = $current;
        $row->save();

        return response()->json($current);
    }

    public function renameBranch(Request $request): JsonResponse
    {
        $this->authorizeSettings($request, 'update', 'branches');

        $payload = $request->validate([
            'from' => 'required|string|max:80',
            'to' => 'required|string|max:80',
        ]);

        $from = trim($payload['from']);
        $to = trim($payload['to']);

        if ($from === '' || $to === '') {
            return response()->json(['message' => 'Both branch names are required.'], 422);
        }

        if ($from === $to) {
            return response()->json($this->resolvedSettings());
        }

        $row = $this->settingsRow();
        $current = BmsSettingsDefaults::merge($row->data ?? []);
        $branches = $current['branches'] ?? [];

        if (! in_array($from, $branches, true)) {
            return response()->json(['message' => 'Branch not found.'], 404);
        }

        if (in_array($to, $branches, true)) {
            return response()->json(['message' => 'Target branch name already exists.'], 422);
        }

        $current['branches'] = array_values(array_map(
            fn (string $b) => $b === $from ? $to : $b,
            $branches
        ));

        BranchUsage::rename($from, $to);
        $row->data = $current;
        $row->save();

        return response()->json($current);
    }

    public function deleteBranch(Request $request, string $name): JsonResponse
    {
        $this->authorizeSettings($request, 'delete', 'branches');

        $branch = urldecode($name);
        $row = $this->settingsRow();
        $current = BmsSettingsDefaults::merge($row->data ?? []);
        $branches = $current['branches'] ?? [];

        if (! in_array($branch, $branches, true)) {
            return response()->json(['message' => 'Branch not found.'], 404);
        }

        if (count($branches) <= 1) {
            return response()->json(['message' => 'At least one branch must remain.'], 422);
        }

        $usage = BranchUsage::count($branch);
        if ($usage['total'] > 0) {
            return response()->json([
                'message' => 'Branch is in use and cannot be deleted. Rename it instead.',
                'usage' => $usage,
            ], 422);
        }

        $current['branches'] = array_values(array_filter(
            $branches,
            fn (string $b) => $b !== $branch
        ));
        $row->data = $current;
        $row->save();

        return response()->json($current);
    }

    public function update(Request $request): JsonResponse
    {
        $this->authorizeSettings($request, 'update');
        $payload = $request->validate([
            'company_name' => 'sometimes|string|max:120',
            'company_tagline' => 'sometimes|nullable|string|max:200',
            'website' => 'sometimes|nullable|string|max:120',
            'email' => 'sometimes|nullable|email|max:120',
            'phone' => 'sometimes|nullable|string|max:40',
            'address' => 'sometimes|nullable|string|max:200',
            'currency' => 'sometimes|string|max:10',
            'currency_symbol' => 'sometimes|string|max:10',
            'vat_rate' => 'sometimes|numeric|min:0|max:100',
            'branches' => 'sometimes|array',
            'branches.*' => 'string|max:80',
            'brand_color' => 'sometimes|string|max:20',
            'brand_color_secondary' => 'sometimes|string|max:20',
            'invoice' => 'sometimes|array',
            'invoice.header_title' => 'sometimes|string|max:80',
            'invoice.accent_color' => 'sometimes|string|max:20',
            'invoice.header_bg' => 'sometimes|string|max:20',
            'invoice.footer_text' => 'sometimes|nullable|string|max:200',
            'invoice.terms_default' => 'sometimes|nullable|string|max:2000',
            'invoice.mpesa_paybill' => 'sometimes|nullable|string|max:20',
            'invoice.mpesa_account_hint' => 'sometimes|nullable|string|max:120',
            'invoice.show_vat_column' => 'sometimes|boolean',
            'invoice.layout' => 'sometimes|string|in:classic,modern,minimal',
        ]);

        $row = $this->settingsRow();
        $current = BmsSettingsDefaults::merge($row->data ?? []);

        if (isset($payload['invoice'])) {
            $payload['invoice'] = array_merge($current['invoice'], $payload['invoice']);
        }

        $row->data = array_merge($current, $payload);
        $row->save();

        return response()->json($row->data);
    }

    public function uploadLogo(Request $request): JsonResponse
    {
        $this->authorizeSettings($request, 'update');

        $data = $request->validate([
            'logo' => 'required|image|mimes:jpeg,jpg,png,gif,webp,svg|max:2048',
            'variant' => 'required|in:dark,light,default',
        ]);

        $key = match ($data['variant']) {
            'dark' => 'logo_url_dark',
            'light' => 'logo_url_light',
            default => 'logo_url',
        };

        return $this->uploadBrandAsset($request, 'logo', $key);
    }

    public function uploadFavicon(Request $request): JsonResponse
    {
        $this->authorizeSettings($request, 'update');

        return $this->uploadBrandAsset($request, 'favicon', 'favicon_url');
    }

    private function uploadBrandAsset(Request $request, string $field, string $settingKey): JsonResponse
    {
        $request->validate([
            $field => 'required|image|mimes:jpeg,jpg,png,gif,webp,svg|max:2048',
        ]);

        $row = $this->settingsRow();
        $current = BmsSettingsDefaults::merge($row->data ?? []);

        if ($current[$settingKey]) {
            $this->deleteStoredUrl($current[$settingKey]);
        }

        $path = $request->file($field)->store('branding', 'public');
        $url = BrandAssets::storagePathUrl($path);

        $current[$settingKey] = $url;
        $row->data = $current;
        $row->save();

        return response()->json([
            'ok' => true,
            $settingKey => $url,
        ]);
    }

    /** @return array<string, mixed> */
    private function resolvedSettings(): array
    {
        $row = BmsSetting::query()->find(1);

        if (! $row) {
            return BmsSettingsDefaults::all();
        }

        return BmsSettingsDefaults::merge($row->data ?? []);
    }

    private function settingsRow(): BmsSetting
    {
        return BmsSetting::query()->firstOrCreate(
            ['id' => 1],
            ['data' => BmsSettingsDefaults::all()]
        );
    }

    private function deleteStoredUrl(?string $url): void
    {
        if (! $url || ! str_contains($url, '/storage/')) {
            return;
        }

        $relative = ltrim(str_replace('/storage/', '', parse_url($url, PHP_URL_PATH) ?: ''), '/');

        if ($relative && Storage::disk('public')->exists($relative)) {
            Storage::disk('public')->delete($relative);
        }
    }

    private function authorizeSettings(Request $request, string $action, string $resource = 'settings'): void
    {
        $role = $request->user()?->role;

        if (! BmsPermissions::allowed($role, $resource, $action)) {
            abort(403, 'You do not have permission to change settings.');
        }
    }
}
