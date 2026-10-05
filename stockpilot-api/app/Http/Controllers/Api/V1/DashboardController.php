<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\StockAlert;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\Transfer;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke()
    {
        return response()->json(['data' => ['kpis' => ['products' => Product::count(), 'warehouses' => Warehouse::count(), 'suppliers' => Supplier::count(), 'inventory_quantity' => (float) Inventory::sum('on_hand'), 'inventory_value' => (float) Inventory::join('products', 'products.id', '=', 'inventories.product_id')->sum(DB::raw('inventories.on_hand * products.purchase_cost')), 'low_stock' => StockAlert::whereNull('resolved_at')->count(), 'pending_purchase_orders' => PurchaseOrder::whereIn('status', ['pending_approval', 'approved', 'ordered', 'partially_received'])->count(), 'pending_transfers' => Transfer::whereNotIn('status', ['completed', 'cancelled', 'rejected'])->count()], 'recent_movements' => StockMovement::with('product:id,name,sku', 'warehouse:id,name')->latest()->limit(8)->get(), 'alerts' => StockAlert::with([])->whereNull('resolved_at')->latest()->limit(8)->get(), 'stock_by_warehouse' => Inventory::join('warehouses', 'warehouses.id', '=', 'inventories.warehouse_id')->select('warehouses.name', DB::raw('SUM(on_hand) as quantity'))->groupBy('warehouses.id', 'warehouses.name')->get()]]);
    }
}
