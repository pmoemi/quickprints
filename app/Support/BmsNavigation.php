<?php

namespace App\Support;

use App\Models\User;

class BmsNavigation
{
    /** @return list<array{section: string, perm?: string, items: list<array{id: string, icon: string, label: string, perm?: string}>}> */
    public static function sections(): array
    {
        return [
            ['section' => 'Overview', 'items' => [
                ['id' => 'dashboard', 'icon' => '⊞', 'label' => 'Dashboard', 'route' => 'bms.dashboard'],
                ['id' => 'kanban', 'icon' => '▦', 'label' => 'Kanban Board', 'route' => 'bms.kanban'],
            ]],
            ['section' => 'Sales', 'items' => [
                ['id' => 'jobs', 'icon' => '📋', 'label' => 'Job Tracker', 'route' => 'bms.jobs.index'],
                ['id' => 'saleslog', 'icon' => '🧾', 'label' => 'Daily Sales Log', 'route' => 'bms.saleslog.index', 'perm' => 'salesLog'],
                ['id' => 'quotes', 'icon' => '📝', 'label' => 'Quote Builder', 'route' => 'bms.quotes.index'],
                ['id' => 'leads', 'icon' => '🎯', 'label' => 'Leads', 'route' => 'bms.leads.index'],
                ['id' => 'followups', 'icon' => '🔁', 'label' => 'Follow-ups', 'route' => 'bms.followups'],
            ]],
            ['section' => 'Operations', 'items' => [
                ['id' => 'designer', 'icon' => '🎨', 'label' => 'Designer Board', 'route' => 'bms.designer'],
                ['id' => 'operator', 'icon' => '⚙️', 'label' => 'Operator Queue', 'route' => 'bms.operator'],
                ['id' => 'fabrication', 'icon' => '🔩', 'label' => 'Fabrication Queue', 'route' => 'bms.fabrication'],
                ['id' => 'delivery', 'icon' => '🚚', 'label' => 'Delivery & Install', 'route' => 'bms.delivery'],
            ]],
            ['section' => 'Clients & Stock', 'items' => [
                ['id' => 'clients', 'icon' => '👥', 'label' => 'Clients', 'route' => 'bms.clients.index'],
                ['id' => 'inventory', 'icon' => '📦', 'label' => 'Materials/Inventory', 'route' => 'bms.inventory.index'],
            ]],
            ['section' => 'Communication', 'items' => [
                ['id' => 'messages', 'icon' => '✉️', 'label' => 'Messages', 'route' => 'bms.messages.index'],
                ['id' => 'notifications', 'icon' => '🔔', 'label' => 'Notifications', 'route' => 'bms.notifications.index'],
            ]],
            ['section' => 'Finance', 'perm' => 'finance', 'items' => [
                ['id' => 'opex', 'icon' => '💳', 'label' => 'Expenses (Opex)', 'route' => 'bms.opex.index'],
                ['id' => 'payroll', 'icon' => '💵', 'label' => 'Payroll', 'route' => 'bms.payroll.index'],
                ['id' => 'petty-cash', 'icon' => '💊', 'label' => 'Petty Cash', 'route' => 'bms.pettycash.index'],
                ['id' => 'assets', 'icon' => '🏗️', 'label' => 'Assets', 'route' => 'bms.assets.index'],
                ['id' => 'procurement', 'icon' => '📥', 'label' => 'Procurement', 'route' => 'bms.procurement.index'],
                ['id' => 'suppliers', 'icon' => '🏭', 'label' => 'Suppliers', 'route' => 'bms.suppliers.index'],
                ['id' => 'purchase-orders', 'icon' => '📑', 'label' => 'Purchase Orders', 'route' => 'bms.purchase-orders.index'],
                ['id' => 'recurring-bills', 'icon' => '🔁', 'label' => 'Recurring Bills', 'route' => 'bms.recurring-bills.index'],
                ['id' => 'ledger', 'icon' => '📒', 'label' => 'Accounting Ledger', 'route' => 'bms.ledger.index'],
                ['id' => 'bank-recon', 'icon' => '🏦', 'label' => 'Bank Reconciliation', 'route' => 'bms.bank-recon'],
                ['id' => 'cash-recon', 'icon' => '💰', 'label' => 'Cash Reconciliation', 'route' => 'bms.cash-recon'],
                ['id' => 'payments', 'icon' => '💳', 'label' => 'Payments', 'route' => 'bms.payments'],
            ]],
            ['section' => 'HR', 'perm' => 'hr', 'items' => [
                ['id' => 'staff', 'icon' => '👤', 'label' => 'Staff', 'route' => 'bms.staff.index'],
                ['id' => 'attendance', 'icon' => '🕐', 'label' => 'Attendance', 'route' => 'bms.attendance.index'],
                ['id' => 'leave', 'icon' => '🏖️', 'label' => 'Leave', 'route' => 'bms.leave.index'],
                ['id' => 'payslips', 'icon' => '🧾', 'label' => 'Payslips', 'route' => 'bms.payslips.index'],
            ]],
            ['section' => 'Catalogue', 'items' => [
                ['id' => 'services', 'icon' => '📋', 'label' => 'Services Catalogue', 'route' => 'bms.services.index'],
            ]],
            ['section' => 'Analytics', 'items' => [
                ['id' => 'reports', 'icon' => '📈', 'label' => 'Reports', 'route' => 'bms.reports'],
                ['id' => 'commissions', 'icon' => '💹', 'label' => 'Commissions', 'route' => 'bms.commissions.index', 'perm' => 'salesLog'],
                ['id' => 'sales-targets', 'icon' => '🎯', 'label' => 'Sales Targets', 'route' => 'bms.sales-targets.index', 'perm' => 'salesLog'],
                ['id' => 'vat-report', 'icon' => '🧮', 'label' => 'VAT Report', 'route' => 'bms.vat-report', 'perm' => 'finance'],
            ]],
            ['section' => 'System', 'items' => [
                ['id' => 'portal', 'icon' => '🌐', 'label' => 'Client Portal', 'route' => 'bms.portal.index'],
                ['id' => 'audit-log', 'icon' => '📜', 'label' => 'Audit Log', 'route' => 'bms.audit.index', 'perm' => 'finance'],
                ['id' => 'maintenance', 'icon' => '🔧', 'label' => 'Maintenance', 'route' => 'bms.maintenance.index', 'perm' => 'finance'],
                ['id' => 'settings', 'icon' => '⚙️', 'label' => 'Settings', 'route' => 'bms.settings.company', 'perm' => 'finance'],
            ]],
        ];
    }

    public static function canAccessPage(?User $user, string $pageId): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->role === 'Admin') {
            return true;
        }

        if (in_array($pageId, BmsSettingsDefaults::alwaysAccessiblePages(), true)) {
            return true;
        }

        $dynamicRoles = BmsSettingsDefaults::roles();
        if (isset($dynamicRoles[$user->role])) {
            $roleData = $dynamicRoles[$user->role];

            // New perms format: check if page belongs to a group with read access
            if (isset($roleData['perms'])) {
                foreach (BmsSettingsDefaults::resourceGroups() as $group => $groupData) {
                    if (in_array($pageId, $groupData['pages'], true)) {
                        return in_array('read', $roleData['perms'][$group] ?? [], true);
                    }
                }

                return false;
            }

            // Legacy pages format fallback
            return in_array($pageId, $roleData['pages'] ?? [], true);
        }

        // Hardcoded fallback for roles not in settings
        return in_array($pageId, self::rolePages()[$user->role] ?? [], true);
    }

    public static function hasCap(?User $user, string $cap): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->role === 'Admin') {
            return true;
        }

        $dynamicRoles = BmsSettingsDefaults::roles();
        if (isset($dynamicRoles[$user->role])) {
            $roleData = $dynamicRoles[$user->role];

            if (isset($roleData['perms'])) {
                $perms = $roleData['perms'];

                return match ($cap) {
                    'salesLog'    => in_array('read', $perms['sales'] ?? [], true),
                    'finance'     => in_array('read', $perms['finance'] ?? [], true),
                    'hr'          => in_array('read', $perms['hr'] ?? [], true),
                    'allBranches' => (bool) ($roleData['allBranches'] ?? false),
                    'opsWrite'    => in_array('write', $perms['operations'] ?? [], true),
                    default       => false,
                };
            }

            // Legacy caps format fallback
            return in_array($cap, $roleData['caps'] ?? [], true);
        }

        return in_array($user->role, self::permMap()[$cap] ?? [], true);
    }

    /** @return array<string, list<string>> */
    public static function rolePages(): array
    {
        return [
            'General Manager' => [
                'jobs', 'saleslog', 'quotes', 'leads', 'followups', 'clients', 'reports', 'payments', 'settings',
                'messages', 'notifications', 'attendance', 'leave', 'payslips', 'suppliers', 'purchase-orders',
                'recurring-bills', 'services', 'commissions', 'sales-targets', 'vat-report', 'ledger', 'bank-recon',
                'cash-recon', 'audit-log', 'portal', 'maintenance', 'opex', 'payroll', 'petty-cash', 'assets', 'procurement',
            ],
            'Operations Manager' => ['dashboard', 'jobs', 'kanban', 'designer', 'operator', 'fabrication', 'delivery', 'inventory', 'clients', 'reports'],
            'Receptionist'       => ['dashboard', 'jobs', 'saleslog', 'quotes', 'leads', 'followups', 'clients', 'reports', 'payments', 'messages', 'notifications', 'leave', 'services'],
            'Sales'              => ['jobs', 'saleslog', 'quotes', 'leads', 'followups', 'clients', 'reports', 'messages', 'notifications', 'commissions', 'sales-targets', 'services'],
            'Designer'           => ['designer'],
            'Operator'           => ['operator', 'fabrication'],
            'Fabrication Staff'  => ['operator', 'fabrication'],
            'Welder'             => ['operator', 'fabrication'],
            'CNC Operator'       => ['operator', 'fabrication'],
            'Laser Operator'     => ['operator', 'fabrication'],
        ];
    }

    public static function defaultRoute(?User $user): string
    {
        if (! $user) {
            return 'bms.login';
        }

        $dynamicRoles = BmsSettingsDefaults::roles();
        if (isset($dynamicRoles[$user->role])) {
            $roleData = $dynamicRoles[$user->role];
            $perms    = $roleData['perms'] ?? [];

            // Pick the best landing page based on what the role can read
            foreach (['jobs', 'sales', 'clients', 'operations', 'finance', 'hr', 'reports', 'settings'] as $group) {
                if (in_array('read', $perms[$group] ?? [], true)) {
                    return match ($group) {
                        'jobs'       => 'bms.jobs.index',
                        'sales'      => 'bms.saleslog.index',
                        'clients'    => 'bms.clients.index',
                        'operations' => 'bms.designer',
                        'finance'    => 'bms.opex.index',
                        'hr'         => 'bms.staff.index',
                        'reports'    => 'bms.reports',
                        'settings'   => 'bms.settings.company',
                        default      => 'bms.dashboard',
                    };
                }
            }
        }

        return 'bms.dashboard';
    }

    /** @return list<array<string, mixed>> */
    public static function navForUser(?User $user): array
    {
        $perm = self::permMap();

        $out = [];
        foreach (self::sections() as $section) {
            if (isset($section['perm']) && ! self::userHasPerm($user, $section['perm'], $perm)) {
                continue;
            }

            $items = [];
            foreach ($section['items'] as $item) {
                if (isset($item['perm']) && ! self::userHasPerm($user, $item['perm'], $perm)) {
                    continue;
                }
                if (! self::canAccessPage($user, $item['id'])) {
                    continue;
                }
                $items[] = $item;
            }

            if ($items) {
                $out[] = ['section' => $section['section'], 'items' => $items];
            }
        }

        return $out;
    }

    /** @return array<string, list<string>> */
    private static function permMap(): array
    {
        $map = [
            'salesLog'    => ['Admin'],
            'finance'     => ['Admin'],
            'hr'          => ['Admin'],
            'allBranches' => ['Admin'],
            'opsWrite'    => ['Admin'],
        ];

        foreach (BmsSettingsDefaults::roles() as $roleName => $roleData) {
            $checks = [];

            if (isset($roleData['perms'])) {
                $p = $roleData['perms'];
                $checks = [
                    'salesLog'    => in_array('read', $p['sales'] ?? [], true),
                    'finance'     => in_array('read', $p['finance'] ?? [], true),
                    'hr'          => in_array('read', $p['hr'] ?? [], true),
                    'allBranches' => (bool) ($roleData['allBranches'] ?? false),
                    'opsWrite'    => in_array('write', $p['operations'] ?? [], true),
                ];
            } else {
                // Legacy caps format
                foreach ($roleData['caps'] ?? [] as $cap) {
                    $checks[$cap] = true;
                }
            }

            foreach ($checks as $key => $has) {
                if ($has && isset($map[$key]) && ! in_array($roleName, $map[$key], true)) {
                    $map[$key][] = $roleName;
                }
            }
        }

        return $map;
    }

    private static function userHasPerm(?User $user, string $key, array $perm): bool
    {
        if (! $user) {
            return false;
        }

        return in_array($user->role, $perm[$key] ?? [], true);
    }
}
