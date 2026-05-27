<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{
    public $incrementing = false;

    protected $keyType = 'int';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'items' => 'array',
            'vat_rate' => 'decimal:2',
        ];
    }
}
