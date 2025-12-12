<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use App\Models\Customer;
use App\Services\CustomerAnalyticsService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\DB;

class RecalculateCustomerAnalytics extends Command
{
    protected $signature = 'analytics:recalculate {--tenant=} {--queue}';
    protected $description = 'Recalculate customer analytics for tenant (or all tenants)';

    protected CustomerAnalyticsService $service;

    public function __construct(CustomerAnalyticsService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function handle(): int
    {
        $tenantId = $this->option('tenant');

        if ($tenantId) {
            $tenant = Tenant::find($tenantId);
            if (!$tenant) {
                $this->error("Tenant not found: {$tenantId}");
                return 1;
            }

            $this->info("Processing tenant {$tenant->id} ({$tenant->name})");
            $this->processTenant($tenant);
            return 0;
        }

        $this->info('Processing all tenants from landlord DB...');

        $tenants = Tenant::all();
        foreach ($tenants as $t) {
            $this->info("Processing tenant {$t->id} ({$t->name})");
            $this->processTenant($t);
        }

        $this->info('All tenants processed.');
        return 0;
    }

    protected function processTenant($tenant): void
    {
        // activate tenant (Spatie v4)
        $tenant->makeCurrent();

        // ensure fresh connection
        DB::purge('tenant');
        DB::reconnect('tenant');

        // recalc for customers in this tenant
        Customer::chunkById(100, function ($customers) {
            foreach ($customers as $c) {
                $this->service->recalcCustomer($c);
            }
        });

        // leave tenant
        $tenant->forget();
    }

    public function schedule(Schedule $schedule): void
    {
        $tenants = Tenant::all();
        if ($tenants) {
            foreach ($tenants as $t) {
                $schedule->command('analytics:recalculate --tenant=' . $t->id)->dailyAt('02:00');
            }
        }
    }
}
