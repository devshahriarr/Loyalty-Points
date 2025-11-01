<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Tenant;
use Spatie\Multitenancy\Exceptions\NoCurrentTenant;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantExists
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        
        // Extract subdomain from host
        $parts = explode('.', $host);
        if (count($parts) > 2) {
            $subdomain = $parts[0];
            
            // Find tenant by domain
            $tenant = Tenant::where('domain', $subdomain)->first();
            
            if ($tenant) {
                $tenant->makeCurrent();
                return $next($request);
            }
        }
        
        // If we reach here, no tenant was found or set
        return response()->json([
            'error' => 'No tenant found for this domain',
            'domain' => $host
        ], 400);
    }
}