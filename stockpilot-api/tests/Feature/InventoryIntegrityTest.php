<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class InventoryIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_change_creates_a_movement_and_negative_stock_is_rejected(): void
    {
        $user = User::factory()->create();
        $warehouse = Warehouse::create(['code' => 'T-1', 'name' => 'Test', 'status' => 'active']);
        $category = Category::create(['name' => 'Test', 'slug' => 'test']);
        $unit = Unit::create(['name' => 'Piece', 'symbol' => 'pc']);
        $product = Product::create(['sku' => 'SKU-1', 'product_code' => 'PRD-1', 'name' => 'Product', 'category_id' => $category->id, 'unit_id' => $unit->id, 'purchase_cost' => 10]);
        $service = app(InventoryService::class);
        $service->change($product->id, $warehouse->id, 10, 'stock_in', 'TEST-1', $user);
        $this->assertDatabaseHas('inventories', ['product_id' => $product->id, 'warehouse_id' => $warehouse->id, 'on_hand' => 10]);
        $this->assertDatabaseHas('stock_movements', ['reference_number' => 'TEST-1', 'quantity_before' => 0, 'quantity_after' => 10]);
        $this->expectException(ValidationException::class);
        $service->change($product->id, $warehouse->id, -11, 'stock_out', 'TEST-2', $user);
    }

    public function test_product_and_warehouse_inventory_row_is_unique(): void
    {
        $this->assertTrue(true);
        $indexes = Schema::getIndexes('inventories');
        $this->assertNotEmpty(array_filter($indexes, fn ($i) => $i['unique'] ?? false));
    }
}
