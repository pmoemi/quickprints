<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrintJob extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $table = 'print_jobs';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'paid' => 'boolean',
            'amount' => 'decimal:2',
            'deadline' => 'date',
            'history' => 'array',
            'follow_ups' => 'array',
        ];
    }
}
