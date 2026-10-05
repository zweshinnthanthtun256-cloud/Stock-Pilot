<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\PurchaseOrder;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_api_request_is_rejected(): void
    {
        $this->getJson('/api/v1/dashboard')->assertUnauthorized();
    }

    public function test_inactive_account_is_rejected(): void
    {
        $user = User::factory()->create(['status' => 'suspended']);
        $this->actingAs($user)->getJson('/api/v1/dashboard')->assertForbidden();
    }

    public function test_permission_middleware_protects_master_data(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $this->actingAs($user)->postJson('/api/v1/brands', ['name' => 'Restricted'])->assertForbidden();
        $permission = Permission::create(['name' => 'master.manage', 'label' => 'Manage master data']);
        $role = Role::create(['name' => 'admin', 'label' => 'Admin']);
        $role->permissions()->attach($permission);
        $user->roles()->attach($role);
        $this->actingAs($user)->postJson('/api/v1/brands', ['name' => 'Allowed'])->assertCreated();
    }

    public function test_policy_blocks_unassigned_warehouse_resource(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $assigned = Warehouse::create(['code' => 'A', 'name' => 'Assigned', 'status' => 'active']);
        $blocked = Warehouse::create(['code' => 'B', 'name' => 'Blocked', 'status' => 'active']);
        $user->warehouses()->attach($assigned);
        $supplier = Supplier::create(['code' => 'S', 'company_name' => 'Supplier']);
        $order = PurchaseOrder::create(['number' => 'PO-1', 'supplier_id' => $supplier->id, 'warehouse_id' => $blocked->id, 'order_date' => now(), 'status' => 'draft', 'created_by' => $user->id]);
        $this->actingAs($user)->getJson("/api/v1/purchase-orders/{$order->id}")->assertForbidden();
    }
}
