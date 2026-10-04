<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Product extends Model { use SoftDeletes; protected $guarded=[]; protected $casts=['purchase_cost'=>'decimal:2','selling_price'=>'decimal:2','is_active'=>'boolean','is_discontinued'=>'boolean']; public function category(){return $this->belongsTo(Category::class);} public function brand(){return $this->belongsTo(Brand::class);} public function unit(){return $this->belongsTo(Unit::class);} public function inventories(){return $this->hasMany(Inventory::class);} }
