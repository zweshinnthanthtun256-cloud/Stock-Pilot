<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockAlert extends Model
{
    protected $guarded = [];

    protected $casts = ['resolved_at' => 'datetime'];
}
