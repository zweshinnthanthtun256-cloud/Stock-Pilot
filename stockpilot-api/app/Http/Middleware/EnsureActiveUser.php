<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
class EnsureActiveUser { public function handle(Request $request, Closure $next) { abort_unless($request->user()?->status === 'active', 403, 'This account is not active.'); return $next($request); } }
