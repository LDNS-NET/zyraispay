<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use App\Models\TenantSubscription;

class CreateMissingSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:create-missing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create subscriptions for existing tenants who don\'t have them';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Creating missing subscriptions for existing tenants...');

        $tenants = Tenant::all();
        $created = 0;

        foreach ($tenants as $tenant) {
            $existingSubscription = TenantSubscription::where('tenant_id', $tenant->id)->first();
            
            if (!$existingSubscription) {
                TenantSubscription::createForTenant($tenant);
                $created++;
                $this->line("Created subscription for tenant: {$tenant->business_name} ({$tenant->id})");
            }
        }

        $this->info("Created {$created} new subscriptions.");
        
        if ($created === 0) {
            $this->info('All tenants already have subscriptions.');
        }

        return Command::SUCCESS;
    }
}
