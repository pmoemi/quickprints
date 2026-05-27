<?php

namespace App\Services;

use App\Models\BmsSetting;
use App\Support\BmsSettingsDefaults;

class BmsSettingsService
{
    /** @return array<string, mixed> */
    public function all(): array
    {
        $row = BmsSetting::query()->find(1);

        if (! $row) {
            return BmsSettingsDefaults::all();
        }

        return BmsSettingsDefaults::merge($row->data ?? []);
    }

    /** @param  array<string, mixed>  $payload */
    public function update(array $payload): array
    {
        $row = BmsSetting::query()->firstOrCreate(
            ['id' => 1],
            ['data' => BmsSettingsDefaults::all()]
        );

        $current = BmsSettingsDefaults::merge($row->data ?? []);

        if (isset($payload['invoice']) && is_array($payload['invoice'])) {
            $payload['invoice'] = array_merge($current['invoice'], $payload['invoice']);
        }

        if (isset($payload['email_settings']) && is_array($payload['email_settings'])) {
            $merged = array_merge($current['email_settings'] ?? [], $payload['email_settings']);
            if (isset($payload['email_settings']['notifications']) && is_array($payload['email_settings']['notifications'])) {
                $merged['notifications'] = $payload['email_settings']['notifications'];
            }
            $payload['email_settings'] = $merged;
        }

        if (isset($payload['email_templates']) && is_array($payload['email_templates'])) {
            $currentTpls = $current['email_templates'] ?? [];
            foreach ($payload['email_templates'] as $type => $tpl) {
                $currentTpls[$type] = is_array($tpl)
                    ? array_merge($currentTpls[$type] ?? [], $tpl)
                    : $tpl;
            }
            $payload['email_templates'] = $currentTpls;
        }

        $row->data = array_merge($current, $payload);
        $row->save();

        return $row->data;
    }
}
