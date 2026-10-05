<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\SalesOrder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SalesService
{
    public function __construct(private InventoryService $inventory) {}

    public function reserve(SalesOrder $order): SalesOrder
    {
        return DB::transaction(function () use ($order) {
            $order = SalesOrder::whereKey($order->id)->lockForUpdate()->with('items')->firstOrFail();
            if (! in_array($order->status, ['draft', 'confirmed'])) {
                throw ValidationException::withMessages(['status' => 'Order cannot be reserved.']);
            }foreach ($order->items as $item) {
                $this->inventory->reserve($item->product_id, $order->warehouse_id, (float) $item->quantity);
            }$order->update(['status' => 'reserved']);

            return $order;
        }, 3);
    }

    public function dispatch(SalesOrder $order, User $actor): SalesOrder
    {
        return DB::transaction(function () use ($order, $actor) {
            $order = SalesOrder::whereKey($order->id)->lockForUpdate()->with('items')->firstOrFail();
            if ($order->status !== 'reserved') {
                throw ValidationException::withMessages(['status' => 'Only reserved orders may be dispatched.']);
            }foreach ($order->items as $item) {
                $inventory = Inventory::where(['product_id' => $item->product_id, 'warehouse_id' => $order->warehouse_id])->lockForUpdate()->firstOrFail();
                $inventory->decrement('reserved', (float) $item->quantity);
                $this->inventory->change($item->product_id, $order->warehouse_id, -(float) $item->quantity, 'sales_issue', $order->number, $actor, $order, 'Sales order dispatch');
            }$order->update(['status' => 'completed']);

            return $order;
        }, 3);
    }
}
