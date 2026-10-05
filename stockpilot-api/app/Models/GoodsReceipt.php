<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsReceipt extends Model
{
    protected $guarded = [];

    protected $casts = ['received_at' => 'datetime'];

    public function items()
    {
        return $this->hasMany(GoodsReceiptItem::class);
    }
}
