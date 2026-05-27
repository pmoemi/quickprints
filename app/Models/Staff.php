<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Staff extends Model
{
    public $incrementing = false;

    protected $keyType = 'int';

    protected $table = 'staff';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'salary' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
