<?php
use App\Http\Controllers\Api\V1\{AdminController,AuthController,CatalogController,DashboardController,InventoryController,ReportController,WorkflowController};
use Illuminate\Support\Facades\Route;
Route::prefix('v1')->group(function(){
 Route::post('/auth/login',[AuthController::class,'login'])->middleware('throttle:6,1');
 Route::post('/auth/forgot-password',[AuthController::class,'forgot'])->middleware('throttle:6,1');
 Route::middleware(['auth:sanctum','active'])->group(function(){
  Route::get('/auth/user',[AuthController::class,'user']); Route::post('/auth/logout',[AuthController::class,'logout']); Route::put('/auth/password',[AuthController::class,'changePassword']);
  Route::get('/dashboard',DashboardController::class);
  Route::get('/users',[AdminController::class,'users'])->middleware('permission:users.manage'); Route::post('/users',[AdminController::class,'storeUser'])->middleware('permission:users.manage'); Route::put('/users/{user}',[AdminController::class,'updateUser'])->middleware('permission:users.manage'); Route::get('/roles',[AdminController::class,'roles'])->middleware('permission:users.manage');
  Route::get('/reports/inventory',[ReportController::class,'inventory'])->middleware('permission:reports.view'); Route::get('/reports/movements',[ReportController::class,'movements'])->middleware('permission:reports.view');
  Route::get('/inventory',[InventoryController::class,'index']); Route::get('/movements',[InventoryController::class,'movements']); Route::post('/inventory/manual',[InventoryController::class,'manual'])->middleware('permission:inventory.adjust');
  Route::get('/purchase-orders',[WorkflowController::class,'purchaseOrders']); Route::post('/purchase-orders',[WorkflowController::class,'createPurchaseOrder'])->middleware('permission:purchases.manage'); Route::post('/purchase-orders/{order}/submit',[WorkflowController::class,'submitPurchaseOrder'])->middleware('permission:purchases.manage'); Route::post('/purchase-orders/{order}/approve',[WorkflowController::class,'approvePurchaseOrder'])->middleware('permission:purchases.approve'); Route::post('/purchase-orders/{order}/receive',[WorkflowController::class,'receive'])->middleware('permission:purchases.receive');
  Route::get('/transfers',[WorkflowController::class,'transfers']); Route::post('/transfers',[WorkflowController::class,'createTransfer'])->middleware('permission:transfers.manage'); Route::post('/transfers/{transfer}/{action}',[WorkflowController::class,'transferAction'])->whereIn('action',['approve','ship','receive'])->middleware('permission:transfers.manage');
  Route::get('/sales-orders',[WorkflowController::class,'salesOrders']); Route::post('/sales-orders',[WorkflowController::class,'createSalesOrder'])->middleware('permission:sales.manage'); Route::post('/sales-orders/{order}/{action}',[WorkflowController::class,'salesAction'])->whereIn('action',['reserve','dispatch'])->middleware('permission:sales.manage');
  Route::get('/adjustments',[WorkflowController::class,'adjustments']); Route::post('/adjustments',[WorkflowController::class,'createAdjustment'])->middleware('permission:inventory.adjust'); Route::post('/adjustments/{adjustment}/approve',[WorkflowController::class,'approveAdjustment'])->middleware('permission:inventory.adjust');
  Route::get('/{resource}',[CatalogController::class,'index'])->whereIn('resource',['products','categories','brands','units','suppliers','warehouses']);
  Route::post('/{resource}',[CatalogController::class,'store'])->whereIn('resource',['products','categories','brands','units','suppliers','warehouses'])->middleware('permission:master.manage');
  Route::get('/{resource}/{id}',[CatalogController::class,'show'])->whereIn('resource',['products','categories','brands','units','suppliers','warehouses']);
  Route::put('/{resource}/{id}',[CatalogController::class,'update'])->whereIn('resource',['products','categories','brands','units','suppliers','warehouses'])->middleware('permission:master.manage');
 });
});
