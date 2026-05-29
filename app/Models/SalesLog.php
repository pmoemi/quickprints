<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesLog extends Model
{
    public $incrementing = false;

    protected $keyType = 'int';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public function salesRep(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'sales_rep_id');
    }
}
