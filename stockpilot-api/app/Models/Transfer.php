<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Transfer extends Model { protected $guarded=[]; protected $casts=['approved_at'=>'datetime','shipped_at'=>'datetime','received_at'=>'datetime']; public function items(){return $this->hasMany(TransferItem::class);} public function sourceWarehouse(){return $this->belongsTo(Warehouse::class,'source_warehouse_id');} public function destinationWarehouse(){return $this->belongsTo(Warehouse::class,'destination_warehouse_id');} }
