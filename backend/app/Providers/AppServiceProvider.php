<?php

namespace App\Providers;

use App\Models\Tenant;
use Illuminate\Support\ServiceProvider;
use Spatie\Multitenancy\Models\Tenant as BaseTenant;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    // public function boot(): void
    // {
    //     // Register our custom Tenant model with Spatie's multitenancy package
    //     \Spatie\Multitenancy\Models\Tenant::useModel(\App\Models\Tenant::class);
    // }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // BaseTenant::useModel(Tenant::class);
    }
}
