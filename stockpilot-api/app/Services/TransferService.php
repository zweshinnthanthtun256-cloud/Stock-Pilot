<?php
namespace App\Services;
use App\Models\{Transfer,User};
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
class TransferService
{
    public function __construct(private InventoryService $inventory) {}
    public function transition(Transfer $transfer,string $action,User $actor): Transfer
    {
        return DB::transaction(function() use($transfer,$action,$actor){ $transfer=Transfer::whereKey($transfer->id)->lockForUpdate()->with('items')->firstOrFail();
            if($action==='approve' && $transfer->status==='pending_approval') $transfer->update(['status'=>'approved','approved_by'=>$actor->id,'approved_at'=>now()]);
            elseif($action==='ship' && $transfer->status==='approved'){ foreach($transfer->items as $item) $this->inventory->change($item->product_id,$transfer->source_warehouse_id,-(float)$item->quantity,'transfer_out',$transfer->number,$actor,$transfer,'Warehouse transfer shipment'); $transfer->update(['status'=>'shipped','shipped_by'=>$actor->id,'shipped_at'=>now()]); }
            elseif($action==='receive' && $transfer->status==='shipped'){ foreach($transfer->items as $item) $this->inventory->change($item->product_id,$transfer->destination_warehouse_id,(float)$item->quantity,'transfer_in',$transfer->number,$actor,$transfer,'Warehouse transfer receipt'); $transfer->update(['status'=>'completed','received_by'=>$actor->id,'received_at'=>now()]); }
            else throw ValidationException::withMessages(['status'=>'Invalid transfer transition.']); return $transfer->fresh('items'); },3);
    }
}
