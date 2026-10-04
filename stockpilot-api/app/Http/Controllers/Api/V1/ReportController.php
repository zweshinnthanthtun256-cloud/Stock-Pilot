<?php
namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use App\Models\{Inventory,StockMovement};
use Illuminate\Http\Request;
class ReportController extends Controller
{
 public function inventory(Request $r){$q=Inventory::with('product:id,sku,name,purchase_cost','warehouse:id,code,name');if($r->warehouse_id)$q->where('warehouse_id',$r->warehouse_id);$rows=$q->get()->map(fn($i)=>['sku'=>$i->product->sku,'product'=>$i->product->name,'warehouse'=>$i->warehouse->name,'on_hand'=>$i->on_hand,'reserved'=>$i->reserved,'available'=>$i->available,'value'=>(float)$i->on_hand*(float)$i->product->purchase_cost]);return $r->boolean('csv')?$this->csv($rows->all(),'inventory-summary.csv'):response()->json(['data'=>$rows,'total_value'=>$rows->sum('value')]);}
 public function movements(Request $r){$q=StockMovement::with('product:id,sku,name','warehouse:id,code,name')->latest();if($r->date_from)$q->whereDate('created_at','>=',$r->date_from);if($r->date_to)$q->whereDate('created_at','<=',$r->date_to);return response()->json($q->paginate(50));}
 private function csv(array $rows,string $name){return response()->streamDownload(function()use($rows){$out=fopen('php://output','w');if($rows){fputcsv($out,array_keys($rows[0]));foreach($rows as $row)fputcsv($out,$row);}fclose($out);},$name,['Content-Type'=>'text/csv']);}
}
