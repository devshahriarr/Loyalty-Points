<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class TenantRouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Load tenant routes under the 'tenant' middleware group
        Route::middleware(['tenant'])
        ->prefix('api')->group(base_path('routes/tenant.php'));
    }
}
