<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class SalesOrder extends Model { protected $guarded=[]; public function items(){return $this->hasMany(SalesOrderItem::class);} }
