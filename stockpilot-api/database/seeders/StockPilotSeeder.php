<?php
namespace Database\Seeders;
use App\Models\{Brand,Category,Inventory,Permission,Product,Role,Supplier,Unit,User,Warehouse};
use App\Services\InventoryService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StockPilotSeeder extends Seeder
{
    public function run(): void
    {
        $permissions=collect(['master.manage','users.manage','inventory.adjust','inventory.view','purchases.manage','purchases.approve','purchases.receive','transfers.manage','sales.manage','reports.view','audit.view','settings.manage'])->mapWithKeys(fn($name)=>[$name=>Permission::firstOrCreate(['name'=>$name],['label'=>ucwords(str_replace('.',' ',$name))])]);
        $roles=['super-admin'=>$permissions->keys()->all(),'admin'=>['master.manage','users.manage','inventory.view','settings.manage'],'warehouse-manager'=>['inventory.adjust','inventory.view','transfers.manage','purchases.receive'],'inventory-officer'=>['inventory.adjust','inventory.view'],'purchasing-officer'=>['purchases.manage','purchases.receive','inventory.view'],'sales-officer'=>['sales.manage','inventory.view'],'auditor'=>['inventory.view','reports.view','audit.view']];
        foreach($roles as $name=>$grants){$role=Role::firstOrCreate(['name'=>$name],['label'=>ucwords(str_replace('-',' ',$name))]);$role->permissions()->sync($permissions->only($grants)->pluck('id'));}
        $admin=User::firstOrCreate(['email'=>'admin@stockpilot.test'],['name'=>'Avery Morgan','phone'=>'+95 9 555 0100','password'=>Hash::make('StockPilot123!'),'status'=>'active','email_verified_at'=>now()]);$admin->roles()->sync([Role::where('name','super-admin')->value('id')]);
        $yangon=Warehouse::firstOrCreate(['code'=>'YGN-01'],['name'=>'Yangon Central','address'=>'Hlaing Township, Yangon','manager_id'=>$admin->id,'phone'=>'+95 1 555 0120','status'=>'active']);
        $mandalay=Warehouse::firstOrCreate(['code'=>'MDY-01'],['name'=>'Mandalay Hub','address'=>'Chanayethazan, Mandalay','manager_id'=>$admin->id,'phone'=>'+95 2 555 0140','status'=>'active']);$admin->warehouses()->sync([$yangon->id,$mandalay->id]);
        $electronics=Category::firstOrCreate(['slug'=>'electronics'],['name'=>'Electronics']);$laptops=Category::firstOrCreate(['slug'=>'laptops'],['name'=>'Laptops','parent_id'=>$electronics->id]);
        $dell=Brand::firstOrCreate(['name'=>'Dell']);$unit=Unit::firstOrCreate(['name'=>'Piece'],['symbol'=>'pc','precision'=>0]);
        $supplier=Supplier::firstOrCreate(['code'=>'SUP-000001'],['company_name'=>'TechSource Myanmar','contact_person'=>'Mya Thida','phone'=>'+95 9 420 555 100','email'=>'orders@techsource.test','payment_terms'=>'Net 30']);
        $products=[['sku'=>'DL-LAT-5440','product_code'=>'PRD-000001','barcode'=>'884116450001','name'=>'Dell Latitude 5440','purchase_cost'=>980,'selling_price'=>1199,'minimum_stock'=>10,'reorder_quantity'=>30],['sku'=>'DL-P2422H','product_code'=>'PRD-000002','barcode'=>'884116450002','name'=>'Dell 24-inch Monitor','purchase_cost'=>170,'selling_price'=>229,'minimum_stock'=>15,'reorder_quantity'=>40],['sku'=>'CS-CBS250','product_code'=>'PRD-000003','barcode'=>'889728295001','name'=>'Cisco CBS250 Switch','purchase_cost'=>310,'selling_price'=>389,'minimum_stock'=>8,'reorder_quantity'=>20]];
        foreach($products as $i=>$data){$p=Product::firstOrCreate(['sku'=>$data['sku']],$data+['category_id'=>$i? $electronics->id:$laptops->id,'brand_id'=>$dell->id,'unit_id'=>$unit->id]);$qty=[100,42,16][$i];if(!Inventory::where(['product_id'=>$p->id,'warehouse_id'=>$yangon->id])->exists())app(InventoryService::class)->change($p->id,$yangon->id,$qty,'opening_stock','OPEN-2026-'.str_pad($i+1,4,'0',STR_PAD_LEFT),$admin,null,'Demo opening balance');}
    }
}
