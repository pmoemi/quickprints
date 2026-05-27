<?php

namespace App\Support;

use App\Models\Asset;
use App\Models\Client;
use App\Models\InventoryItem;
use App\Models\Lead;
use App\Models\OpexEntry;
use App\Models\PayrollEntry;
use App\Models\PettyCashEntry;
use App\Models\PrintJob;
use App\Models\ProcurementEntry;
use App\Models\Quote;
use App\Models\SalesLog;
use App\Models\Staff;
use InvalidArgumentException;

class BmsResourceRegistry
{
    /** @var array<string, class-string> */
    private const MODELS = [
        'jobs' => PrintJob::class,
        'clients' => Client::class,
        'staff' => Staff::class,
        'saleslog' => SalesLog::class,
        'quotes' => Quote::class,
        'leads' => Lead::class,
        'inventory' => InventoryItem::class,
        'opex' => OpexEntry::class,
        'payroll' => PayrollEntry::class,
        'pettycash' => PettyCashEntry::class,
        'assets' => Asset::class,
        'procurement' => ProcurementEntry::class,
    ];

    public static function model(string $resource): string
    {
        if (! isset(self::MODELS[$resource])) {
            throw new InvalidArgumentException("Unknown BMS resource: {$resource}");
        }

        return self::MODELS[$resource];
    }

    /** @return array<string, string> */
    public static function rules(string $resource, bool $updating): array
    {
        return match ($resource) {
            'jobs' => [
                'id' => $updating ? 'sometimes|string' : 'required|string',
                'client_id' => 'nullable|integer',
                'title' => ($updating ? 'sometimes' : 'required').'|string',
                'branch' => 'nullable|string',
                'category' => 'nullable|string',
                'stage' => 'nullable|string',
                'priority' => 'nullable|string',
                'amount' => 'nullable|numeric',
                'paid' => 'nullable|boolean',
                'designer_id' => 'nullable|integer',
                'sales_rep_id' => 'nullable|integer',
                'fab_substage' => 'nullable|string',
                'deadline' => 'nullable|date',
                'notes' => 'nullable|string',
                'history' => 'nullable|array',
                'follow_ups' => 'nullable|array',
            ],
            'clients' => [
                'id' => ($updating ? 'sometimes' : 'required').'|integer',
                'name' => ($updating ? 'sometimes' : 'required').'|string',
                'phone' => 'nullable|string',
                'email' => 'nullable|string',
                'company' => 'nullable|string',
                'branch' => 'nullable|string',
                'notes' => 'nullable|string',
            ],
            'staff' => [
                'id' => ($updating ? 'sometimes' : 'required').'|integer',
                'name' => ($updating ? 'sometimes' : 'required').'|string',
                'role' => ($updating ? 'sometimes' : 'required').'|string',
                'branch' => 'nullable|string',
                'email' => 'nullable|email',
                'color' => 'nullable|string',
                'salary' => 'nullable|numeric',
                'active' => 'nullable|boolean',
            ],
            'saleslog' => [
                'id' => ($updating ? 'sometimes' : 'required').'|integer',
                'date' => 'nullable|date',
                'client_name' => 'nullable|string',
                'phone' => 'nullable|string',
                'job_desc' => 'nullable|string',
                'category' => 'nullable|string',
                'branch' => 'nullable|string',
                'amount' => 'nullable|numeric',
                'pay_status' => 'nullable|string',
                'pay_method' => 'nullable|string',
                'logged_by' => 'nullable|string',
                'job_id' => 'nullable|string',
            ],
            'quotes' => [
                'id' => ($updating ? 'sometimes' : 'required').'|integer',
                'date' => 'nullable|date',
                'client_name' => 'nullable|string',
                'client_phone' => 'nullable|string',
                'branch' => 'nullable|string',
                'prepared_by' => 'nullable|string',
                'items' => 'nullable|array',
                'vat_rate' => 'nullable|numeric',
                'status' => 'nullable|string',
                'notes' => 'nullable|string',
            ],
            'leads' => [
                'id' => ($updating ? 'sometimes' : 'required').'|integer',
                'client_name' => 'nullable|string',
                'phone' => 'nullable|string',
                'service' => 'nullable|string',
                'value' => 'nullable|numeric',
                'status' => 'nullable|string',
                'assigned_to' => 'nullable|string',
                'branch' => 'nullable|string',
                'follow_up_date' => 'nullable|date',
                'notes' => 'nullable|string',
            ],
            'inventory' => [
                'id' => ($updating ? 'sometimes' : 'required').'|integer',
                'name' => ($updating ? 'sometimes' : 'required').'|string',
                'cat' => 'nullable|string',
                'unit' => 'nullable|string',
                'qty' => 'nullable|numeric',
                'unit_cost' => 'nullable|numeric',
                'branch' => 'nullable|string',
                'reorder_level' => 'nullable|numeric',
                'roll_width' => 'nullable|numeric',
            ],
            'opex' => [
                'id' => ($updating ? 'sometimes' : 'required').'|integer',
                'date' => 'nullable|date',
                'description' => 'nullable|string',
                'amount' => 'nullable|numeric',
                'branch' => 'nullable|string',
                'paid_by' => 'nullable|string',
                'pay_method' => 'nullable|string',
            ],
            'payroll' => [
                'id' => ($updating ? 'sometimes' : 'required').'|integer',
                'month' => 'nullable|string',
                'staff_id' => 'nullable|integer',
                'staff_name' => 'nullable|string',
                'gross_salary' => 'nullable|numeric',
                'nhif' => 'nullable|numeric',
                'nssf' => 'nullable|numeric',
                'paye' => 'nullable|numeric',
                'net_pay' => 'nullable|numeric',
                'status' => 'nullable|string',
            ],
            'pettycash' => [
                'id' => ($updating ? 'sometimes' : 'required').'|integer',
                'date' => 'nullable|date',
                'description' => 'nullable|string',
                'amount' => 'nullable|numeric',
                'branch' => 'nullable|string',
                'submitted_by' => 'nullable|string',
                'status' => 'nullable|string',
            ],
            'assets' => [
                'id' => ($updating ? 'sometimes' : 'required').'|integer',
                'name' => ($updating ? 'sometimes' : 'required').'|string',
                'category' => 'nullable|string',
                'purchase_cost' => 'nullable|numeric',
                'current_value' => 'nullable|numeric',
                'condition_status' => 'nullable|string',
                'branch' => 'nullable|string',
            ],
            'procurement' => [
                'id' => ($updating ? 'sometimes' : 'required').'|integer',
                'date' => 'nullable|date',
                'supplier' => 'nullable|string',
                'description' => 'nullable|string',
                'amount' => 'nullable|numeric',
                'branch' => 'nullable|string',
                'status' => 'nullable|string',
                'requested_by' => 'nullable|string',
            ],
            default => throw new InvalidArgumentException("Unknown BMS resource: {$resource}"),
        };
    }

    /** @return array<string, string> */
    public static function casts(string $resource): array
    {
        return match ($resource) {
            'jobs' => ['paid' => 'bool', 'history' => 'array', 'follow_ups' => 'array'],
            'staff' => ['active' => 'bool'],
            'quotes' => ['items' => 'array'],
            default => [],
        };
    }
}
