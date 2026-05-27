<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    public $incrementing = false;

    protected $keyType = 'int';

    protected $guarded = [];
}
