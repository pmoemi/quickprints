<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BmsSetting extends Model
{
    public $incrementing = false;

    protected $keyType = 'int';

    protected $table = 'bms_settings';

    protected $fillable = ['id', 'data'];

    protected function casts(): array
    {
        return [
            'data' => 'array',
        ];
    }
}
