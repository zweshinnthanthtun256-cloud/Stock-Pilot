<?php
namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use App\Models\{Inventory,StockMovement};
use App\Services\InventoryService;
use Illuminate\Http\Request;
class InventoryController extends Controller
{
    public function index(Request $r){$q=Inventory::with('product:id,sku,name,minimum_stock,purchase_cost','warehouse:id,code,name');if($r->warehouse_id)$q->where('warehouse_id',$r->warehouse_id);if($r->search)$q->whereHas('product',fn($x)=>$x->where('name','like','%'.$r->search.'%')->orWhere('sku','like','%'.$r->search.'%'));return response()->json($q->paginate(min($r->integer('per_page',20),100)));}
    public function movements(Request $r){$q=StockMovement::with('product:id,sku,name','warehouse:id,code,name','performer:id,name')->latest();foreach(['warehouse_id','product_id','type'] as $f)if($r->$f)$q->where($f,$r->$f);return response()->json($q->paginate(min($r->integer('per_page',20),100)));}
    public function manual(Request $r,InventoryService $service){$d=$r->validate(['warehouse_id'=>'required|exists:warehouses,id','product_id'=>'required|exists:products,id','quantity'=>'required|numeric|gt:0','direction'=>'required|in:in,out','reason'=>'required|string|max:120','reference'=>'nullable|string|max:120','notes'=>'nullable|string']);abort_unless($r->user()->canAccessWarehouse($d['warehouse_id']),403);$delta=$d['direction']==='in'?$d['quantity']:-$d['quantity'];$movement=$service->change($d['product_id'],$d['warehouse_id'],$delta,'manual_'.$d['direction'],$d['reference']?:'MAN-'.now()->format('YmdHis'),$r->user(),null,$d['reason'],$d['notes']??null);return response()->json(['message'=>'Stock movement recorded.','data'=>$movement],201);}
}
