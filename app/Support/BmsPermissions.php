<?php

namespace App\Support;

use App\Support\BmsSettingsDefaults;

class BmsPermissions
{
    /** @return array<string, array<string, list<string>>> */
    public static function matrix(): array
    {
        $opsRead = [
            'Admin', 'General Manager', 'Operations Manager', 'Receptionist',
            'Designer', 'Sales', 'Operator', 'Fabrication Staff', 'Welder', 'CNC Operator', 'Laser Operator',
        ];
        $salesWrite = ['Admin', 'General Manager', 'Receptionist', 'Sales'];
        $opsWrite = ['Admin', 'General Manager', 'Operations Manager'];
        $finance = ['Admin', 'General Manager'];
        $hr = ['Admin', 'General Manager'];
        $adminOnly = ['Admin', 'General Manager'];

        return [
            'jobs' => [
                'read' => $opsRead,
                'create' => array_merge($salesWrite, ['Operations Manager']),
                'update' => array_merge($opsWrite, ['Receptionist', 'Designer', 'Operator', 'Fabrication Staff', 'Welder', 'CNC Operator', 'Laser Operator']),
                'delete' => $adminOnly,
            ],
            'clients' => [
                'read' => array_merge($salesWrite, ['Operations Manager']),
                'create' => array_merge($salesWrite, ['Operations Manager']),
                'update' => array_merge($salesWrite, ['Operations Manager']),
                'delete' => $adminOnly,
            ],
            'staff' => [
                'read' => $hr,
                'create' => $hr,
                'update' => $hr,
                'delete' => ['Admin'],
            ],
            'saleslog' => [
                'read' => array_merge($salesWrite, ['Operations Manager']),
                'create' => $salesWrite,
                'update' => $salesWrite,
                'delete' => $adminOnly,
            ],
            'quotes' => [
                'read' => array_merge($salesWrite, ['Operations Manager']),
                'create' => $salesWrite,
                'update' => $salesWrite,
                'delete' => $adminOnly,
            ],
            'leads' => [
                'read' => $salesWrite,
                'create' => $salesWrite,
                'update' => $salesWrite,
                'delete' => $adminOnly,
            ],
            'inventory' => [
                'read' => array_merge($opsWrite, ['Receptionist']),
                'create' => $opsWrite,
                'update' => $opsWrite,
                'delete' => $adminOnly,
            ],
            'opex' => [
                'read' => $finance,
                'create' => $finance,
                'update' => $finance,
                'delete' => $finance,
            ],
            'payroll' => [
                'read' => $finance,
                'create' => $finance,
                'update' => $finance,
                'delete' => ['Admin'],
            ],
            'pettycash' => [
                'read' => $finance,
                'create' => array_merge($finance, ['Receptionist', 'Operations Manager']),
                'update' => $finance,
                'delete' => $finance,
            ],
            'assets' => [
                'read' => $finance,
                'create' => $finance,
                'update' => $finance,
                'delete' => $finance,
            ],
            'procurement' => [
                'read' => $finance,
                'create' => $finance,
                'update' => $finance,
                'delete' => $finance,
            ],
            'settings' => [
                'read' => $finance,
                'update' => $finance,
                'delete' => ['Admin'],
            ],
            'branches' => [
                'read' => array_merge($adminOnly, ['Operations Manager', 'Receptionist', 'Sales']),
                'create' => $adminOnly,
                'update' => $adminOnly,
                'delete' => $adminOnly,
            ],
            'suppliers' => [
                'read' => $finance,
                'create' => $finance,
                'update' => $finance,
                'delete' => $finance,
            ],
            'purchase_orders' => [
                'read' => $finance,
                'create' => $finance,
                'update' => $finance,
                'delete' => $finance,
            ],
            'recurring_bills' => [
                'read' => $finance,
                'create' => $finance,
                'update' => $finance,
                'delete' => $finance,
            ],
            'attendance' => [
                'read' => $hr,
                'create' => $hr,
                'update' => $hr,
                'delete' => $hr,
            ],
            'leave' => [
                'read' => array_merge($hr, ['Receptionist']),
                'create' => array_merge($hr, ['Receptionist']),
                'update' => $hr,
                'delete' => $hr,
            ],
            'ledger' => [
                'read' => $finance,
                'create' => $finance,
                'update' => $finance,
                'delete' => ['Admin'],
            ],
            'services' => [
                'read'   => $opsRead,
                'create' => $adminOnly,
                'update' => $adminOnly,
                'delete' => $adminOnly,
            ],
        ];
    }

    /**
     * Returns true if the given role is allowed to reset staff passwords.
     * Admin always has this capability; other roles need `resetStaffPasswords = true`.
     */
    public static function canResetStaffPasswords(?string $role): bool
    {
        if (! $role) {
            return false;
        }

        if ($role === 'Admin') {
            return true;
        }

        $dynamicRoles = BmsSettingsDefaults::roles();

        return (bool) ($dynamicRoles[$role]['resetStaffPasswords'] ?? false);
    }

    public static function actionFromMethod(string $method): string
    {
        return match (strtoupper($method)) {
            'GET' => 'read',
            'POST' => 'create',
            'PUT', 'PATCH' => 'update',
            'DELETE' => 'delete',
            default => 'read',
        };
    }

    public static function allowed(?string $role, string $resource, string $action): bool
    {
        if (! $role) {
            return false;
        }

        if ($role === 'Admin') {
            return true;
        }

        $dynamicRoles = BmsSettingsDefaults::roles();
        if (isset($dynamicRoles[$role])) {
            $roleData = $dynamicRoles[$role];

            if (isset($roleData['perms'])) {
                return self::permsAllowed($roleData['perms'], $resource, $action);
            }

            // Legacy caps/pages format fallback
            return self::legacyCapsAllowed($roleData, $resource, $action);
        }

        // Hardcoded matrix fallback
        return in_array($role, self::matrix()[$resource][$action] ?? [], true);
    }

    /** @return array<string, list<string>> */
    public static function forRole(string $role): array
    {
        if ($role === 'Admin') {
            $out = [];
            foreach (self::matrix() as $resource => $actions) {
                $out[$resource] = ['read', 'create', 'update', 'delete'];
            }

            return $out;
        }

        $dynamicRoles = BmsSettingsDefaults::roles();
        if (isset($dynamicRoles[$role])) {
            $roleData = $dynamicRoles[$role];
            $out      = [];

            if (isset($roleData['perms'])) {
                foreach (BmsSettingsDefaults::resourceGroups() as $group => $groupData) {
                    $gp = $roleData['perms'][$group] ?? [];
                    if (empty($gp)) {
                        continue;
                    }
                    $actions = [];
                    if (in_array('read', $gp, true)) {
                        $actions[] = 'read';
                    }
                    if (in_array('create', $gp, true)) {
                        $actions[] = 'create';
                    }
                    if (in_array('write', $gp, true)) {
                        $actions[] = 'update';
                        $actions[] = 'delete';
                    }
                    foreach ($groupData['resources'] as $resource) {
                        $out[$resource] = $actions;
                    }
                }

                return $out;
            }

            // Legacy caps/pages format fallback
            foreach ($roleData['pages'] ?? [] as $pageId) {
                $resource = self::pageToResource($pageId);
                if ($resource && ! isset($out[$resource])) {
                    $out[$resource] = ['read'];
                }
            }
            foreach ($roleData['caps'] ?? [] as $cap) {
                foreach (self::legacyCapResources($cap) as $resource) {
                    $out[$resource] = ['read', 'create', 'update', 'delete'];
                }
            }

            return $out;
        }

        $out = [];
        foreach (self::matrix() as $resource => $actions) {
            $allowed = [];
            foreach ($actions as $action => $roles) {
                if (in_array($role, $roles, true)) {
                    $allowed[] = $action;
                }
            }
            if ($allowed) {
                $out[$resource] = $allowed;
            }
        }

        return $out;
    }

    private static function permsAllowed(array $perms, string $resource, string $action): bool
    {
        $group = self::resourceGroup($resource);
        if (! $group) {
            return false;
        }

        $gp = $perms[$group] ?? [];

        return match ($action) {
            'read'            => in_array('read', $gp, true),
            'create'          => in_array('create', $gp, true),
            'update', 'delete' => in_array('write', $gp, true),
            default           => false,
        };
    }

    private static function legacyCapsAllowed(array $roleData, string $resource, string $action): bool
    {
        if ($action === 'read') {
            $pageId = self::resourceToPage($resource);

            return $pageId !== null && in_array($pageId, $roleData['pages'] ?? [], true);
        }

        foreach ($roleData['caps'] ?? [] as $cap) {
            if (in_array($resource, self::legacyCapResources($cap), true)) {
                return true;
            }
        }

        return false;
    }

    private static function resourceGroup(string $resource): ?string
    {
        foreach (BmsSettingsDefaults::resourceGroups() as $group => $data) {
            if (in_array($resource, $data['resources'], true)) {
                return $group;
            }
        }

        return null;
    }

    private static function legacyCapResources(string $cap): array
    {
        return match ($cap) {
            'salesLog'    => ['saleslog', 'quotes', 'leads', 'jobs', 'clients'],
            'finance'     => ['opex', 'payroll', 'pettycash', 'assets', 'procurement', 'suppliers', 'purchase_orders', 'recurring_bills', 'ledger', 'settings', 'branches'],
            'hr'          => ['staff', 'attendance', 'leave'],
            'opsWrite'    => ['jobs', 'clients', 'inventory'],
            'allBranches' => ['branches'],
            default       => [],
        };
    }

    private static function resourceToPage(string $resource): ?string
    {
        return match ($resource) {
            'pettycash'       => 'petty-cash',
            'purchase_orders' => 'purchase-orders',
            'recurring_bills' => 'recurring-bills',
            default           => $resource,
        };
    }

    private static function pageToResource(string $pageId): ?string
    {
        return match ($pageId) {
            'petty-cash'      => 'pettycash',
            'purchase-orders' => 'purchase_orders',
            'recurring-bills' => 'recurring_bills',
            default           => $pageId,
        };
    }
}
