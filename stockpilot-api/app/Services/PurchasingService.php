<?php

namespace App\Services;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchasingService
{
    public function __construct(private InventoryService $inventory) {}

    public function receive(PurchaseOrder $order, array $lines, User $actor, ?string $notes = null): GoodsReceipt
    {
        return DB::transaction(function () use ($order, $lines, $actor, $notes) {
            $order = PurchaseOrder::whereKey($order->id)->lockForUpdate()->firstOrFail();
            if (! in_array($order->status, ['approved', 'ordered', 'partially_received'])) {
                throw ValidationException::withMessages(['status' => 'This purchase order cannot be received.']);
            }
            $receipt = GoodsReceipt::create(['number' => ReferenceNumber::next('goods_receipts', 'GRN'), 'purchase_order_id' => $order->id, 'received_by' => $actor->id, 'received_at' => now(), 'notes' => $notes]);
            foreach ($lines as $line) {
                $item = PurchaseOrderItem::where('purchase_order_id', $order->id)->whereKey($line['item_id'])->lockForUpdate()->firstOrFail();
                $qty = (float) $line['quantity'];
                if ($qty <= 0 || (float) $item->received_quantity + $qty > (float) $item->quantity) {
                    throw ValidationException::withMessages(['items' => 'Receipt quantity exceeds the outstanding ordered quantity.']);
                } GoodsReceiptItem::create(['goods_receipt_id' => $receipt->id, 'purchase_order_item_id' => $item->id, 'quantity' => $qty]);
                $item->increment('received_quantity', $qty);
                $this->inventory->change($item->product_id, $order->warehouse_id, $qty, 'purchase_receive', $receipt->number, $actor, $receipt, 'Purchase order receipt');
            }
            $hasOutstanding = $order->items()->whereColumn('received_quantity', '<', 'quantity')->exists();
            $order->update(['status' => $hasOutstanding ? 'partially_received' : 'received']);

            return $receipt->load('items');
        }, 3);
    }
}
