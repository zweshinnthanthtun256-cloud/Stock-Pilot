<?php

namespace App\Providers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Models\Setting;
use App\Models\StockAdjustment;
use App\Models\Supplier;
use App\Models\Transfer;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use App\Observers\AuditObserver;
use App\Policies\WarehouseResourcePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        foreach ([Brand::class, Category::class, Product::class, PurchaseOrder::class, SalesOrder::class, Setting::class, StockAdjustment::class, Supplier::class, Transfer::class, Unit::class, User::class, Warehouse::class] as $model) {
            $model::observe(AuditObserver::class);
        }
        Gate::before(fn (User $user) => $user->roles()->where('name', 'super-admin')->exists() ? true : null);
        Gate::policy(PurchaseOrder::class, WarehouseResourcePolicy::class);
        Gate::policy(Transfer::class, WarehouseResourcePolicy::class);
        Gate::policy(SalesOrder::class, WarehouseResourcePolicy::class);
        Gate::policy(StockAdjustment::class, WarehouseResourcePolicy::class);
    }
}
