<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Role;
use App\Models\SalesOrder;
use App\Models\StockAdjustment;
use App\Models\Supplier;
use App\Models\Transfer;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\AdjustmentService;
use App\Services\InventoryService;
use App\Services\PurchasingService;
use App\Services\SalesService;
use App\Services\TransferService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class WorkflowIntegrityTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Warehouse $source;

    private Warehouse $destination;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->source = Warehouse::create(['code' => 'SRC', 'name' => 'Source', 'status' => 'active']);
        $this->destination = Warehouse::create(['code' => 'DST', 'name' => 'Destination', 'status' => 'active']);
        $category = Category::create(['name' => 'Laptops', 'slug' => 'laptops']);
        $unit = Unit::create(['name' => 'Piece', 'symbol' => 'pc']);
        $this->product = Product::create(['sku' => 'LAP-1', 'product_code' => 'PRD-1', 'name' => 'Laptop', 'category_id' => $category->id, 'unit_id' => $unit->id, 'purchase_cost' => 100, 'minimum_stock' => 10, 'reorder_quantity' => 30]);
        $this->user->warehouses()->sync([$this->source->id, $this->destination->id]);
    }

    public function test_purchase_order_supports_partial_then_complete_receiving(): void
    {
        $supplier = Supplier::create(['code' => 'SUP-1', 'company_name' => 'Supplier']);
        $order = PurchaseOrder::create(['number' => 'PO-2026-000001', 'supplier_id' => $supplier->id, 'warehouse_id' => $this->source->id, 'order_date' => now(), 'status' => 'approved', 'subtotal' => 10000, 'total' => 10000, 'created_by' => $this->user->id]);
        $item = $order->items()->create(['product_id' => $this->product->id, 'quantity' => 100, 'received_quantity' => 0, 'unit_price' => 100, 'line_total' => 10000]);
        $service = app(PurchasingService::class);
        $service->receive($order, [['item_id' => $item->id, 'quantity' => 60]], $this->user);
        $this->assertSame('partially_received', $order->fresh()->status);
        $this->assertEquals(60, Inventory::where(['product_id' => $this->product->id, 'warehouse_id' => $this->source->id])->value('on_hand'));
        $service->receive($order, [['item_id' => $item->id, 'quantity' => 40]], $this->user);
        $this->assertSame('received', $order->fresh()->status);
        $this->assertEquals(100, $item->fresh()->received_quantity);
        $this->assertDatabaseCount('goods_receipts', 2);
        $this->assertDatabaseCount('stock_movements', 2);
    }

    public function test_receiving_more_than_ordered_is_rejected(): void
    {
        $supplier = Supplier::create(['code' => 'SUP-1', 'company_name' => 'Supplier']);
        $order = PurchaseOrder::create(['number' => 'PO-2026-000001', 'supplier_id' => $supplier->id, 'warehouse_id' => $this->source->id, 'order_date' => now(), 'status' => 'approved', 'subtotal' => 100, 'total' => 100, 'created_by' => $this->user->id]);
        $item = $order->items()->create(['product_id' => $this->product->id, 'quantity' => 1, 'unit_price' => 100, 'line_total' => 100]);
        $this->expectException(ValidationException::class);
        app(PurchasingService::class)->receive($order, [['item_id' => $item->id, 'quantity' => 2]], $this->user);
    }

    public function test_transfer_moves_stock_once_at_each_control_point(): void
    {
        app(InventoryService::class)->change($this->product->id, $this->source->id, 100, 'opening', 'OPEN-1', $this->user);
        $transfer = Transfer::create(['number' => 'TRF-2026-000001', 'source_warehouse_id' => $this->source->id, 'destination_warehouse_id' => $this->destination->id, 'status' => 'pending_approval', 'requested_by' => $this->user->id]);
        $transfer->items()->create(['product_id' => $this->product->id, 'quantity' => 20]);
        $service = app(TransferService::class);
        $service->transition($transfer, 'approve', $this->user);
        $service->transition($transfer, 'ship', $this->user);
        $this->assertEquals(80, Inventory::where(['product_id' => $this->product->id, 'warehouse_id' => $this->source->id])->value('on_hand'));
        $this->assertDatabaseMissing('inventories', ['product_id' => $this->product->id, 'warehouse_id' => $this->destination->id]);
        $service->transition($transfer, 'receive', $this->user);
        $this->assertEquals(20, Inventory::where(['product_id' => $this->product->id, 'warehouse_id' => $this->destination->id])->value('on_hand'));
        $this->assertSame('completed', $transfer->fresh()->status);
    }

    public function test_sales_reservation_prevents_overselling_and_dispatches(): void
    {
        app(InventoryService::class)->change($this->product->id, $this->source->id, 20, 'opening', 'OPEN-1', $this->user);
        $order = SalesOrder::create(['number' => 'SO-2026-000001', 'customer' => 'IT', 'warehouse_id' => $this->source->id, 'status' => 'draft', 'total' => 1500, 'created_by' => $this->user->id]);
        $order->items()->create(['product_id' => $this->product->id, 'quantity' => 15, 'unit_price' => 100]);
        $service = app(SalesService::class);
        $service->reserve($order);
        $inventory = Inventory::first();
        $this->assertEquals(15, $inventory->reserved);
        $service->dispatch($order, $this->user);
        $inventory->refresh();
        $this->assertEquals(5, $inventory->on_hand);
        $this->assertEquals(0, $inventory->reserved);
        $this->assertDatabaseHas('stock_alerts', ['product_id' => $this->product->id, 'level' => 'critical', 'resolved_at' => null]);
    }

    public function test_adjustment_rejects_a_stale_count(): void
    {
        app(InventoryService::class)->change($this->product->id, $this->source->id, 10, 'opening', 'OPEN-1', $this->user);
        $adjustment = StockAdjustment::create(['number' => 'ADJ-2026-000001', 'warehouse_id' => $this->source->id, 'product_id' => $this->product->id, 'system_quantity' => 10, 'counted_quantity' => 8, 'difference' => -2, 'reason' => 'counting_error', 'status' => 'pending', 'requested_by' => $this->user->id]);
        app(InventoryService::class)->change($this->product->id, $this->source->id, 1, 'stock_in', 'IN-1', $this->user);
        $this->expectException(ValidationException::class);
        app(AdjustmentService::class)->approve($adjustment, $this->user);
    }

    public function test_user_is_restricted_to_assigned_warehouses(): void
    {
        $other = User::factory()->create();
        $other->warehouses()->attach($this->source);
        $this->assertTrue($other->canAccessWarehouse($this->source->id));
        $this->assertFalse($other->canAccessWarehouse($this->destination->id));
        $super = Role::create(['name' => 'super-admin', 'label' => 'Super Admin']);
        $other->roles()->attach($super);
        $this->assertTrue($other->canAccessWarehouse($this->destination->id));
    }
}
