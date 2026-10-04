<?php
namespace App\Services;
use App\Models\{Inventory,Product,StockAlert,StockMovement,User};
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    public function change(int $productId, int $warehouseId, float $delta, string $type, string $referenceNumber, User $actor, ?object $reference=null, ?string $reason=null, ?string $notes=null): StockMovement
    {
        return DB::transaction(function () use ($productId,$warehouseId,$delta,$type,$referenceNumber,$actor,$reference,$reason,$notes) {
            $inventory=Inventory::query()->firstOrCreate(['product_id'=>$productId,'warehouse_id'=>$warehouseId],['on_hand'=>0,'reserved'=>0]);
            $inventory=Inventory::query()->whereKey($inventory->id)->lockForUpdate()->firstOrFail();
            $before=(float)$inventory->on_hand; $after=$before+$delta;
            $allowNegative=filter_var(DB::table('settings')->where('key','allow_negative_stock')->value('value') ?? false,FILTER_VALIDATE_BOOL);
            if (!$allowNegative && $after < 0) throw ValidationException::withMessages(['quantity'=>'Insufficient available stock.']);
            if ($after < (float)$inventory->reserved) throw ValidationException::withMessages(['quantity'=>'This change would reduce on-hand stock below reserved stock.']);
            $inventory->update(['on_hand'=>$after]);
            $movement=StockMovement::create(['reference_number'=>$referenceNumber,'product_id'=>$productId,'warehouse_id'=>$warehouseId,'type'=>$type,'quantity'=>abs($delta),'quantity_before'=>$before,'quantity_after'=>$after,'reference_type'=>$reference?->getMorphClass(),'reference_id'=>$reference?->getKey(),'reason'=>$reason,'notes'=>$notes,'performed_by'=>$actor->id]);
            $this->syncAlert($inventory->fresh());
            return $movement;
        }, 3);
    }
    public function reserve(int $productId,int $warehouseId,float $quantity): void
    {
        DB::transaction(function() use($productId,$warehouseId,$quantity){ $i=Inventory::where(['product_id'=>$productId,'warehouse_id'=>$warehouseId])->lockForUpdate()->first(); if(!$i || $i->available < $quantity) throw ValidationException::withMessages(['quantity'=>'Insufficient available stock to reserve.']); $i->increment('reserved',$quantity); });
    }
    private function syncAlert(Inventory $inventory): void
    {
        $minimum=(float)Product::findOrFail($inventory->product_id)->minimum_stock; $quantity=$inventory->available;
        if ($quantity > $minimum) { StockAlert::where(['product_id'=>$inventory->product_id,'warehouse_id'=>$inventory->warehouse_id])->whereNull('resolved_at')->update(['resolved_at'=>now()]); return; }
        $level=$quantity<=0?'out_of_stock':($quantity <= $minimum/2?'critical':'low_stock');
        $alert=StockAlert::firstOrNew(['product_id'=>$inventory->product_id,'warehouse_id'=>$inventory->warehouse_id,'resolved_at'=>null]); $alert->fill(['level'=>$level,'quantity'=>$quantity])->save();
    }
}
