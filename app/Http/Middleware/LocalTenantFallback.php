<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Stancl\Tenancy\Facades\Tenancy;

class LocalTenantFallback
{
    public function handle(Request $request, Closure $next)
    {
        if (app()->environment('local') && $request->getHost() === '127.0.0.1' && !tenant()) {
            $fallbackTenant = \App\Models\Tenant::first();
            if ($fallbackTenant) {
                Tenancy::initialize($fallbackTenant);
            }
        }
        return $next($request);
    }
} 