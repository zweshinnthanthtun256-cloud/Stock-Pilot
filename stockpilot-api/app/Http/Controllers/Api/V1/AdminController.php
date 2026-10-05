<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function users(Request $r)
    {
        $q = User::with('roles:id,name,label', 'warehouses:id,code,name');
        if ($r->search) {
            $q->where(fn ($x) => $x->where('name', 'like', '%'.$r->search.'%')->orWhere('email', 'like', '%'.$r->search.'%'));
        }if ($r->status) {
            $q->where('status', $r->status);
        }

return response()->json($q->latest()->paginate(min($r->integer('per_page', 20), 100)));
    }

    public function user(User $user)
    {
        return response()->json(['data' => $user->load('roles:id,name,label', 'warehouses:id,code,name')]);
    }

    public function storeUser(Request $r)
    {
        $d = $r->validate(['name' => 'required|string|max:150', 'email' => 'required|email|unique:users', 'phone' => 'nullable|string|max:40', 'password' => 'required|string|min:10', 'status' => 'required|in:active,inactive,suspended', 'role_ids' => 'array', 'role_ids.*' => 'exists:roles,id', 'warehouse_ids' => 'array', 'warehouse_ids.*' => 'exists:warehouses,id']);
        $user = User::create([...$d, 'password' => Hash::make($d['password'])]);
        $user->roles()->sync($d['role_ids'] ?? []);
        $user->warehouses()->sync($d['warehouse_ids'] ?? []);

        return response()->json(['message' => 'User created.', 'data' => $user->load('roles', 'warehouses')], 201);
    }

    public function updateUser(Request $r, User $user)
    {
        $d = $r->validate(['name' => 'sometimes|string|max:150', 'phone' => 'nullable|string|max:40', 'status' => 'sometimes|in:active,inactive,suspended', 'role_ids' => 'array', 'role_ids.*' => 'exists:roles,id', 'warehouse_ids' => 'array', 'warehouse_ids.*' => 'exists:warehouses,id']);
        $user->update($d);
        if (array_key_exists('role_ids', $d)) {
            $user->roles()->sync($d['role_ids']);
        }if (array_key_exists('warehouse_ids', $d)) {
            $user->warehouses()->sync($d['warehouse_ids']);
        }

return response()->json(['message' => 'User updated.', 'data' => $user->load('roles', 'warehouses')]);
    }

    public function roles()
    {
        return response()->json(['data' => Role::with('permissions')->get()]);
    }

    public function permissions()
    {
        return response()->json(['data' => Permission::orderBy('name')->get()]);
    }

    public function storeRole(Request $r)
    {
        $d = $r->validate(['name' => 'required|alpha_dash|unique:roles,name', 'label' => 'required|string|max:100', 'permission_ids' => 'array', 'permission_ids.*' => 'exists:permissions,id']);
        $role = Role::create(['name' => $d['name'], 'label' => $d['label']]);
        $role->permissions()->sync($d['permission_ids'] ?? []);

        return response()->json(['message' => 'Role created.', 'data' => $role->load('permissions')], 201);
    }

    public function updateRole(Request $r, Role $role)
    {
        abort_if($role->name === 'super-admin', 422, 'The super administrator role is protected.');
        $d = $r->validate(['label' => 'required|string|max:100', 'permission_ids' => 'array', 'permission_ids.*' => 'exists:permissions,id']);
        $role->update(['label' => $d['label']]);
        $role->permissions()->sync($d['permission_ids'] ?? []);

        return response()->json(['message' => 'Role updated.', 'data' => $role->load('permissions')]);
    }
}
