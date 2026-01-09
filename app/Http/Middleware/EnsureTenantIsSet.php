<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantIsSet
{
    public function handle(Request $request, Closure $next): Response
    {
        // Get tenant from header or auth user
        $tenantId = $request->header('X-Tenant-ID');

        if (!$tenantId && Auth::check()) {
            $tenantId = Auth::user()->tenant_id;
        }

        if (!$tenantId) {
            return response()->json([
                'message' => 'Tenant ID is required',
                'code' => 'TENANT_REQUIRED',
            ], 400);
        }

        // Store tenant ID in request for later use
        $request->attributes->set('tenant_id', $tenantId);

        return $next($request);
    }
}
