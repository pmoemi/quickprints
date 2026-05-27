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
            'is_designer' => 'boolean',
            'salary' => 'decimal:2',
        ];
    }

    public function scopeDesigners($query)
    {
        return $query->where('is_designer', true);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
