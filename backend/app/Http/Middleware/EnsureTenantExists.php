<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Tenant;
use Illuminate\Support\Facades\Log;
use Spatie\Multitenancy\Exceptions\NoCurrentTenant;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantExists
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */

    // public function handle(Request $request, Closure $next): Response
    // {
    //     $host = $request->getHost();
    //     $port = $request->getPort();

    //     $candidates = [$host];
    //     if ($port) $candidates[] = $host . ':' . $port;

    //     $tenant = Tenant::whereIn('domain', $candidates)->first();

    //     if ($tenant) {
    //         $tenant->makeCurrent();
    //         Log::info('Tenant found and made current: ' . $tenant->domain);
    //         return $next($request);
    //     }

    //     return response()->json([
    //         'error' => 'No tenant found for this domain',
    //         'domain' => $host
    //     ], 400);
    // }

    // public function handle(Request $request, Closure $next): Response
    // {
    //     $host = $request->getHost();
    //     $port = $request->getPort();

    //     // Extract subdomain from host
    //     $parts = explode('.', $host);
    //     // dd($parts);
    //     if (count($parts) > 2) {
    //         $subdomain = $parts[0];
    //         // dd("subdomain", $subdomain);

    //         // Find tenant by domain
    //         $tenant = Tenant::where('domain', "{$host}:8000")->first();
    //         // dd($tenant);

    //         if ($tenant) {
    //             $tenant->makeCurrent();
    //             return $next($request);
    //         }
    //     }

    //     // If we reach here, no tenant was found or set
    //     return response()->json([
    //         'error' => 'No tenant found for this domain',
    //         'domain' => $host
    //     ], 400);
    // }

    public function handle(Request $request, Closure $next)
    {
        $host = $request->getHost(); // example: myshop.127.0.0.1.nip.io

        $tenant = Tenant::where('domain', $host)->first();

        if (! $tenant) {
            return response()->json([
                'error' => 'Tenant not found for this domain',
                'domain' => $host,
            ], 404);
        }

        // Switch tenant context
        $tenant->makeCurrent();

        try {
            Log::info('Tenant resolved', [
                'tenant_id' => $tenant->id,
                'domain' => $tenant->domain,
            ]);

            return $next($request);
        } finally {
            // VERY IMPORTANT
            $tenant->forgetCurrent();
        }
    }
}
