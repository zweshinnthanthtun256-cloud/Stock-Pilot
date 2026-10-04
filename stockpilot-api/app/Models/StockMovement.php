<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class StockMovement extends Model { protected $guarded=[]; public function product(){return $this->belongsTo(Product::class);} public function warehouse(){return $this->belongsTo(Warehouse::class);} public function performer(){return $this->belongsTo(User::class,'performed_by');} }
