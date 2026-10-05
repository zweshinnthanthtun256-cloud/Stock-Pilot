<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\PurchaseOrder;
use App\Models\StockAdjustment;
use App\Models\StockMovement;
use App\Models\Transfer;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function inventory(Request $r)
    {
        $q = Inventory::with('product:id,sku,name,purchase_cost', 'warehouse:id,code,name');
        if ($r->warehouse_id) {
            $q->where('warehouse_id', $r->warehouse_id);
        }$rows = $q->get()->map(fn ($i) => ['sku' => $i->product->sku, 'product' => $i->product->name, 'warehouse' => $i->warehouse->name, 'on_hand' => $i->on_hand, 'reserved' => $i->reserved, 'available' => $i->available, 'value' => (float) $i->on_hand * (float) $i->product->purchase_cost]);

        return $r->boolean('csv') ? $this->csv($rows->all(), 'inventory-summary.csv') : response()->json(['data' => $rows, 'total_value' => $rows->sum('value')]);
    }

    public function movements(Request $r)
    {
        $q = StockMovement::with('product:id,sku,name', 'warehouse:id,code,name')->latest();
        if ($r->date_from) {
            $q->whereDate('created_at', '>=', $r->date_from);
        }if ($r->date_to) {
            $q->whereDate('created_at', '<=', $r->date_to);
        }

return response()->json($q->paginate(50));
    }

    public function lowStock(Request $r)
    {
        $q = Inventory::with('product:id,sku,name,minimum_stock,reorder_quantity', 'warehouse:id,code,name')->whereHas('product', fn ($p) => $p->whereColumn('inventories.on_hand', '<=', 'products.minimum_stock'));
        if ($r->warehouse_id) {
            $q->where('warehouse_id', $r->warehouse_id);
        }

return response()->json($q->paginate(50));
    }

    public function purchaseOrders(Request $r)
    {
        $q = PurchaseOrder::with('supplier:id,company_name', 'warehouse:id,name')->latest();
        foreach (['supplier_id', 'warehouse_id', 'status'] as $field) {
            if ($r->$field) {
                $q->where($field, $r->$field);
            }
        }if ($r->date_from) {
            $q->whereDate('order_date', '>=', $r->date_from);
        }if ($r->date_to) {
            $q->whereDate('order_date', '<=', $r->date_to);
        }

return response()->json($q->paginate(50));
    }

    public function supplierPurchases(Request $r)
    {
        $q = PurchaseOrder::join('suppliers', 'suppliers.id', '=', 'purchase_orders.supplier_id')->select('suppliers.id', 'suppliers.company_name', \DB::raw('COUNT(purchase_orders.id) as order_count'), \DB::raw('SUM(purchase_orders.total) as total_purchases'))->groupBy('suppliers.id', 'suppliers.company_name');

        return response()->json(['data' => $q->get()]);
    }

    public function transfers(Request $r)
    {
        $q = Transfer::with('sourceWarehouse:id,name', 'destinationWarehouse:id,name')->latest();
        if ($r->warehouse_id) {
            $q->where(fn ($x) => $x->where('source_warehouse_id', $r->warehouse_id)->orWhere('destination_warehouse_id', $r->warehouse_id));
        }if ($r->status) {
            $q->where('status', $r->status);
        }

return response()->json($q->paginate(50));
    }

    public function adjustments(Request $r)
    {
        $q = StockAdjustment::with([])->latest();
        if ($r->warehouse_id) {
            $q->where('warehouse_id', $r->warehouse_id);
        }if ($r->status) {
            $q->where('status', $r->status);
        }

return response()->json($q->paginate(50));
    }

    public function valuation(Request $r)
    {
        $rows = Inventory::join('products', 'products.id', '=', 'inventories.product_id')->join('warehouses', 'warehouses.id', '=', 'inventories.warehouse_id')->select('warehouses.id', 'warehouses.name', \DB::raw('SUM(inventories.on_hand * products.purchase_cost) as value'))->groupBy('warehouses.id', 'warehouses.name')->get();

        return response()->json(['data' => $rows, 'total' => (float) $rows->sum('value')]);
    }

    private function csv(array $rows, string $name)
    {
        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            if ($rows) {
                fputcsv($out, array_keys($rows[0]));
                foreach ($rows as $row) {
                    fputcsv($out,$row);
                }
            }fclose($out);
        }, $name, ['Content-Type' => 'text/csv']);
    }
}
