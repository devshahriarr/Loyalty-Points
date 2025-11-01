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
        // Register our custom Tenant model
        $tenantModelClass = config('multitenancy.tenant_model');
        app()->singleton(BaseTenant::class, function () use ($tenantModelClass) {
            return new $tenantModelClass;
        });
        
        // Make sure the current() method returns the correct type
        app()->extend('currentTenant', function ($service, $app) {
            return $app->make(config('multitenancy.tenant_model'));
        });
    }
}
