<?php
namespace App\Services;
use App\Models\{Inventory,StockAdjustment,User};
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
class AdjustmentService
{
 public function __construct(private InventoryService $inventory){}
 public function approve(StockAdjustment $adjustment,User $actor):StockAdjustment{return DB::transaction(function()use($adjustment,$actor){$adjustment=StockAdjustment::whereKey($adjustment->id)->lockForUpdate()->firstOrFail();if($adjustment->status!=='pending')throw ValidationException::withMessages(['status'=>'Only pending adjustments may be approved.']);$current=(float)(Inventory::where(['product_id'=>$adjustment->product_id,'warehouse_id'=>$adjustment->warehouse_id])->lockForUpdate()->value('on_hand')??0);if($current!==(float)$adjustment->system_quantity)throw ValidationException::withMessages(['quantity'=>'Inventory changed after this adjustment was requested. Recount before approval.']);$type=$adjustment->difference>=0?'adjustment_increase':'adjustment_decrease';$this->inventory->change($adjustment->product_id,$adjustment->warehouse_id,(float)$adjustment->difference,$type,$adjustment->number,$actor,$adjustment,$adjustment->reason,$adjustment->notes);$adjustment->update(['status'=>'approved','approved_by'=>$actor->id,'approved_at'=>now()]);return $adjustment;},3);}
}
