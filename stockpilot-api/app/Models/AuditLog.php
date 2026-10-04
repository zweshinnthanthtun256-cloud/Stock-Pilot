<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AuditLog extends Model { protected $guarded=[]; protected $casts=['old_values'=>'array','new_values'=>'array']; public function user(){return $this->belongsTo(User::class);} public function auditable(){return $this->morphTo();} }
