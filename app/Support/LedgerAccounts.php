<?php

namespace App\Support;

class LedgerAccounts
{
    /** @return array<string, array{type: string, code: string}> */
    public static function chart(): array
    {
        return [
            'Cash & Bank' => ['type' => 'asset', 'code' => '1001'],
            'Accounts Receivable' => ['type' => 'asset', 'code' => '1002'],
            'Inventory' => ['type' => 'asset', 'code' => '1003'],
            'Equipment' => ['type' => 'asset', 'code' => '1004'],
            'Accounts Payable' => ['type' => 'liability', 'code' => '2001'],
            'Sales Tax Payable' => ['type' => 'liability', 'code' => '2002'],
            'Capital' => ['type' => 'equity', 'code' => '3001'],
            'Sales Revenue' => ['type' => 'revenue', 'code' => '4001'],
            'Service Revenue' => ['type' => 'revenue', 'code' => '4002'],
            'Rent Expense' => ['type' => 'expense', 'code' => '5001'],
            'Salaries Expense' => ['type' => 'expense', 'code' => '5002'],
            'Utilities Expense' => ['type' => 'expense', 'code' => '5003'],
            'Supplies Expense' => ['type' => 'expense', 'code' => '5004'],
            'Printing Supplies' => ['type' => 'expense', 'code' => '5005'],
        ];
    }
}
