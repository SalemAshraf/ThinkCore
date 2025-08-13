<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class SetAdminApiGuard
{
    public function handle($request, Closure $next, $guard = 'admin_api')
    {
        Auth::shouldUse($guard);
        return $next($request);
    }
}
