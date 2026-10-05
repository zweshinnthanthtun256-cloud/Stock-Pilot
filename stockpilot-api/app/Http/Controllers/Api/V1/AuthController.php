<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $r)
    {
        $data = $r->validate(['email' => 'required|email', 'password' => 'required|string']);
        if (! Auth::attempt($data, $r->boolean('remember'))) {
            throw ValidationException::withMessages(['email' => 'The supplied credentials are invalid.']);
        } $r->session()->regenerate();
        $r->user()->update(['last_login_at' => now()]);

        return response()->json(['message' => 'Signed in.', 'data' => $this->userData($r->user())]);
    }

    public function user(Request $r)
    {
        return response()->json(['data' => $this->userData($r->user())]);
    }

    public function logout(Request $r)
    {
        Auth::guard('web')->logout();
        $r->session()->invalidate();
        $r->session()->regenerateToken();

        return response()->json(['message' => 'Signed out.']);
    }

    public function forgot(Request $r)
    {
        $r->validate(['email' => 'required|email']);

        return response()->json(['message' => trans(Password::sendResetLink($r->only('email')))]);
    }

    public function changePassword(Request $r)
    {
        $d = $r->validate(['current_password' => 'required|current_password', 'password' => 'required|confirmed|min:10']);
        $r->user()->update(['password' => Hash::make($d['password'])]);

        return response()->json(['message' => 'Password changed.']);
    }

    private function userData($u)
    {
        $u->load('roles.permissions', 'warehouses');

        return ['id' => $u->id, 'name' => $u->name, 'email' => $u->email, 'status' => $u->status, 'roles' => $u->roles->pluck('name'), 'permissions' => $u->roles->flatMap->permissions->pluck('name')->unique()->values(), 'warehouses' => $u->warehouses];
    }
}
