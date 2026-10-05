<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class WarehouseResourcePolicy
{
    public function before(User $user): ?bool
    {
        return $user->roles()->where('name', 'super-admin')->exists() ? true : null;
    }

    public function view(User $user, Model $resource): bool
    {
        return $this->access($user, $resource);
    }

    public function update(User $user, Model $resource): bool
    {
        return $this->access($user, $resource);
    }

    private function access(User $user, Model $resource): bool
    {
        $warehouseId = $resource->warehouse_id ?? $resource->source_warehouse_id ?? null;

        return $warehouseId && $user->canAccessWarehouse((int) $warehouseId);
    }
}
