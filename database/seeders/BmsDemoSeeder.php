<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\BmsSetting;
use App\Models\Client;
use App\Models\InventoryItem;
use App\Models\Lead;
use App\Models\OpexEntry;
use App\Models\PayrollEntry;
use App\Models\PettyCashEntry;
use App\Models\PrintJob;
use App\Models\Quote;
use App\Models\SalesLog;
use App\Models\Staff;
use App\Models\User;
use App\Support\BmsSettingsDefaults;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class BmsDemoSeeder extends Seeder
{
    public function run(): void
    {
        BmsSetting::query()->updateOrCreate(
            ['id' => 1],
            ['data' => BmsSettingsDefaults::all()]
        );

        $users = [
            ['email' => 'admin@quickprints.co.ke', 'password' => 'Admin@2024', 'name' => 'James Mwangi', 'role' => 'Admin', 'branch' => 'all'],
            ['email' => 'gm@quickprints.co.ke', 'password' => 'Admin@2024', 'name' => 'Mary Wanjiku', 'role' => 'General Manager', 'branch' => 'all'],
            ['email' => 'ops@quickprints.co.ke', 'password' => 'Admin@2024', 'name' => 'Peter Kamau', 'role' => 'Operations Manager', 'branch' => 'Westlands'],
            ['email' => 'grace@quickprints.co.ke', 'password' => 'Admin@2024', 'name' => 'Grace Achieng', 'role' => 'Receptionist', 'branch' => 'CBD'],
            ['email' => 'david@quickprints.co.ke', 'password' => 'Admin@2024', 'name' => 'David Ochieng', 'role' => 'Designer', 'branch' => 'Westlands'],
            ['email' => 'sarah@quickprints.co.ke', 'password' => 'Admin@2024', 'name' => 'Sarah Njeri', 'role' => 'Sales', 'branch' => 'Karen'],
        ];

        foreach ($users as $user) {
            User::query()->updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'password' => Hash::make($user['password']),
                    'role' => $user['role'],
                    'branch' => $user['branch'],
                ]
            );
        }

        $branches = ['Westlands', 'CBD', 'Eastleigh', 'Karen', 'Ngong Road'];
        $stages = ['waiting', 'designing', 'approval', 'printing', 'fabrication', 'ready', 'installed', 'paid'];
        $cats = ['Large Format', 'Signage', 'Vehicle Branding', 'Corporate', 'Promotional', 'Apparel', 'Fabrication', 'Events'];
        $clientNames = ['Safaricom Ltd', 'KCB Bank', 'Equity Bank', 'Nairobi Hospital', 'Java House', 'Artcaffe', 'Nation Media', 'Standard Chartered', 'UAP Insurance', 'Jubilee Insurance'];

        foreach ($clientNames as $i => $name) {
            Client::query()->updateOrCreate(
                ['id' => $i + 1],
                [
                    'name' => $name,
                    'phone' => '07'.str_pad((string) $i, 8, '0', STR_PAD_LEFT),
                    'email' => 'info@'.strtolower(str_replace(' ', '', $name)).'.com',
                    'company' => $name,
                    'branch' => $branches[$i % 5],
                    'notes' => 'VIP client',
                ]
            );
        }

        for ($i = 0; $i < 28; $i++) {
            $id = 'QP-'.str_pad((string) (10001 + $i), 5, '0', STR_PAD_LEFT);
            PrintJob::query()->updateOrCreate(
                ['id' => $id],
                [
                    'client_id' => ($i % 10) + 1,
                    'title' => ['Vinyl Banner 3x1m', 'Business Cards 500pcs', 'Vehicle Wrap Toyota', 'Rollup Banner', 'Acrylic Sign', 'T-Shirts 50pcs', 'Metal Fabrication', 'Exhibition Stand', 'Photography Package', 'Social Media Package'][$i % 10],
                    'branch' => $branches[$i % 5],
                    'category' => $cats[$i % 8],
                    'stage' => $stages[$i % 8],
                    'priority' => ['high', 'medium', 'low'][$i % 3],
                    'amount' => ($i + 1) * 3500 + 1200,
                    'paid' => $i % 3 !== 0,
                    'designer_id' => null,
                    'sales_rep_id' => null,
                    'fab_substage' => $i % 8 === 4 ? ['queued', 'in_progress', 'quality_check', 'complete'][$i % 4] : null,
                    'deadline' => now()->addDays(($i % 10) - 3)->toDateString(),
                    'notes' => 'Standard job',
                    'history' => [],
                    'follow_ups' => [],
                ]
            );
        }

        $staffRows = [
            [1, 'James Mwangi', 'Admin', 'all', 'admin@quickprints.co.ke', '#f97316', 85000],
            [2, 'Mary Wanjiku', 'General Manager', 'all', 'gm@quickprints.co.ke', '#3b82f6', 120000],
            [3, 'Peter Kamau', 'Operations Manager', 'Westlands', 'ops@quickprints.co.ke', '#22c55e', 95000],
            [4, 'Grace Achieng', 'Receptionist', 'CBD', 'grace@quickprints.co.ke', '#a855f7', 35000],
            [5, 'David Ochieng', 'Designer', 'Westlands', 'david@quickprints.co.ke', '#eab308', 55000],
            [6, 'Sarah Njeri', 'Sales', 'Karen', 'sarah@quickprints.co.ke', '#ef4444', 40000],
            [7, 'John Otieno', 'Welder', 'Ngong Road', 'john@quickprints.co.ke', '#06b6d4', 45000],
            [8, 'Ann Muthoni', 'CNC Operator', 'Eastleigh', 'ann@quickprints.co.ke', '#84cc16', 50000],
        ];

        foreach ($staffRows as [$id, $name, $role, $branch, $email, $color, $salary]) {
            Staff::query()->updateOrCreate(
                ['id' => $id],
                [
                    'name' => $name,
                    'role' => $role,
                    'branch' => $branch,
                    'email' => $email,
                    'color' => $color,
                    'salary' => $salary,
                    'active' => true,
                ]
            );
        }

        for ($i = 0; $i < 20; $i++) {
            SalesLog::query()->updateOrCreate(
                ['id' => $i + 1],
                [
                    'date' => now()->subDays($i)->toDateString(),
                    'client_name' => $clientNames[$i % 10],
                    'phone' => '07'.str_pad((string) $i, 8, '0', STR_PAD_LEFT),
                    'job_desc' => ['Banner print', 'Business cards', 'Vehicle wrap', 'Signage', 'T-shirts'][$i % 5],
                    'category' => $cats[$i % 5],
                    'branch' => $branches[$i % 5],
                    'amount' => ($i + 1) * 2000 + 500,
                    'pay_status' => $i % 3 === 0 ? 'pending' : 'paid',
                    'pay_method' => ['Mpesa', 'Cash', 'Bank'][$i % 3],
                    'logged_by' => 'Grace Achieng',
                    'job_id' => 'QP-'.str_pad((string) (10001 + $i), 5, '0', STR_PAD_LEFT),
                ]
            );
        }

        $inventory = [
            [1, 'Vinyl Roll 3.2m', 'Media', 'roll', 12, 4500, 'Westlands', 3, 320],
            [2, 'Mesh Banner Roll', 'Media', 'roll', 5, 3800, 'CBD', 2, null],
            [3, 'Canvas Roll', 'Media', 'roll', 8, 5200, 'Karen', 2, null],
            [4, 'Eco Solvent Ink - Cyan', 'Ink', 'litre', 4, 1800, 'all', 2, null],
            [5, 'Eco Solvent Ink - Magenta', 'Ink', 'litre', 3, 1800, 'all', 2, null],
            [6, 'Business Card Paper 350gsm', 'Paper', 'pack', 20, 800, 'all', 5, null],
            [7, 'Aluminium Sheet 2x1m', 'Metal', 'sheet', 15, 2200, 'Ngong Road', 5, null],
            [8, 'Acrylic Sheet 3mm', 'Acrylic', 'sheet', 10, 1500, 'all', 4, null],
        ];

        foreach ($inventory as [$id, $name, $cat, $unit, $qty, $unitCost, $branch, $reorder, $rollWidth]) {
            InventoryItem::query()->updateOrCreate(
                ['id' => $id],
                [
                    'name' => $name,
                    'cat' => $cat,
                    'unit' => $unit,
                    'qty' => $qty,
                    'unit_cost' => $unitCost,
                    'branch' => $branch,
                    'reorder_level' => $reorder,
                    'roll_width' => $rollWidth,
                ]
            );
        }

        for ($i = 0; $i < 8; $i++) {
            Quote::query()->updateOrCreate(
                ['id' => $i + 1],
                [
                    'date' => now()->subDays($i * 3)->toDateString(),
                    'client_name' => $clientNames[$i],
                    'client_phone' => '0700000000',
                    'branch' => $branches[$i % 5],
                    'prepared_by' => 'James Mwangi',
                    'items' => [
                        ['desc' => 'Large Format Banner', 'qty' => 2, 'unit_price' => 4500],
                        ['desc' => 'Finishing', 'qty' => 1, 'unit_price' => 500],
                    ],
                    'vat_rate' => 16,
                    'status' => ['draft', 'sent', 'approved', 'declined'][$i % 4],
                    'notes' => '',
                ]
            );
        }

        for ($i = 0; $i < 12; $i++) {
            Lead::query()->updateOrCreate(
                ['id' => $i + 1],
                [
                    'client_name' => 'Lead '.($i + 1),
                    'phone' => '07100'.str_pad((string) $i, 5, '0', STR_PAD_LEFT),
                    'service' => $cats[$i % 8],
                    'value' => ($i + 1) * 5000,
                    'status' => ['new', 'contacted', 'qualified', 'proposal', 'won', 'lost'][$i % 6],
                    'assigned_to' => $staffRows[$i % 4][1],
                    'branch' => $branches[$i % 5],
                    'follow_up_date' => now()->addDays($i + 1)->toDateString(),
                ]
            );
        }

        for ($i = 0; $i < 10; $i++) {
            OpexEntry::query()->updateOrCreate(
                ['id' => $i + 1],
                [
                    'date' => now()->subDays($i * 5)->toDateString(),
                    'description' => ['Electricity', 'Internet', 'Rent', 'Fuel', 'Transport'][$i % 5],
                    'amount' => ($i + 1) * 1500 + 500,
                    'branch' => $branches[$i % 5],
                    'paid_by' => 'Admin',
                    'pay_method' => 'Mpesa',
                ]
            );
        }

        for ($i = 0; $i < 8; $i++) {
            PettyCashEntry::query()->updateOrCreate(
                ['id' => $i + 1],
                [
                    'date' => now()->subDays($i * 3)->toDateString(),
                    'description' => ['Tea', 'Printing paper', 'Pens', 'Courier'][$i % 4],
                    'amount' => ($i + 1) * 200,
                    'branch' => $branches[$i % 5],
                    'submitted_by' => $staffRows[$i % 3][1],
                    'status' => $i % 2 === 0 ? 'approved' : 'pending',
                ]
            );
        }

        $assets = [
            [1, 'HP Latex 365 Printer', 'Machine', 850000, 620000, 'Good', 'Westlands'],
            [2, 'Roland CNC Router', 'Machine', 1200000, 950000, 'Good', 'Ngong Road'],
            [3, 'Delivery Van - KBZ 001A', 'Vehicle', 1800000, 1200000, 'Fair', 'CBD'],
        ];

        foreach ($assets as [$id, $name, $category, $purchase, $current, $condition, $branch]) {
            Asset::query()->updateOrCreate(
                ['id' => $id],
                [
                    'name' => $name,
                    'category' => $category,
                    'purchase_cost' => $purchase,
                    'current_value' => $current,
                    'condition_status' => $condition,
                    'branch' => $branch,
                ]
            );
        }

        foreach ($staffRows as [$id, $name, , , , , $salary]) {
            $paye = (int) round($salary * 0.12);
            PayrollEntry::query()->updateOrCreate(
                ['id' => $id],
                [
                    'month' => '2025-01',
                    'staff_id' => $id,
                    'staff_name' => $name,
                    'gross_salary' => $salary,
                    'nhif' => 1700,
                    'nssf' => 600,
                    'paye' => $paye,
                    'net_pay' => $salary - 1700 - 600 - $paye,
                    'status' => 'pending',
                ]
            );
        }
    }
}
