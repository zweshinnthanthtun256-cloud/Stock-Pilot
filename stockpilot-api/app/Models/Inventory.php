<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Inventory extends Model { protected $guarded=[]; protected $casts=['on_hand'=>'decimal:3','reserved'=>'decimal:3']; protected $appends=['available']; public function getAvailableAttribute(){return (float)$this->on_hand-(float)$this->reserved;} public function product(){return $this->belongsTo(Product::class);} public function warehouse(){return $this->belongsTo(Warehouse::class);} }
