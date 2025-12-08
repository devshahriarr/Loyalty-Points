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
        // dd("host", $host);

        // Extract subdomain from host
        $parts = explode('.', $host);
        // dd($parts);
        if (count($parts) > 2) {
            $subdomain = $parts[0];
            // dd("subdomain", $subdomain);

            // Find tenant by domain
            $tenant = Tenant::where('domain', $host)->first();
            // dd($tenant);

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

    // public function handle(Request $request, Closure $next): Response
    // {
    //     // Use getHttpHost() which includes port if present
    //     $httpHost = $request->getHttpHost(); // e.g. "fashion-hubs-business.127.0.0.1.nip.io:8000"
    //     // Also prepare host without port for fallback
    //     $hostOnly = $request->getHost(); // e.g. "fashion-hubs-business.127.0.0.1.nip.io"

    //     // Try exact match first (with port), then host-only
    //     $tenant = Tenant::where('domain', $httpHost)
    //         ->orWhere('domain', $hostOnly)
    //         ->first();

    //     if ($tenant) {
    //         // Make tenant current (Spatie multitenancy)
    //         $tenant->makeCurrent();
    //         return $next($request);
    //     }

    //     return response()->json([
    //         'error' => 'No tenant found for this domain',
    //         'domain_checked' => [$httpHost, $hostOnly],
    //     ], 400);
    // }
}
