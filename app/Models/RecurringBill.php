<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecurringBill extends Model
{
    public $incrementing = false;

    protected $keyType = 'int';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'last_paid_at' => 'date',
        ];
    }
}
