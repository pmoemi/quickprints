<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollEntry extends Model
{
    public $incrementing = false;

    protected $keyType = 'int';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'gross_salary' => 'decimal:2',
            'nhif' => 'decimal:2',
            'nssf' => 'decimal:2',
            'paye' => 'decimal:2',
            'net_pay' => 'decimal:2',
        ];
    }
}
