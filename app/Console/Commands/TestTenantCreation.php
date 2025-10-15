<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use Illuminate\Support\Str;

class TestTenantCreation extends Command
{
    protected $signature = 'tenants:test-creation';
    protected $description = 'Test tenant creation process';

    public function handle()
    {
        $this->info('Testing tenant creation...');

        // Create a test tenant
        $tenantId = Str::uuid()->toString();
        $domain = 'test-' . time();
        $fullDomain = $domain . '.zyraispay.test';

        $this->line("Creating tenant with ID: {$tenantId}");
        $this->line("Domain: {$fullDomain}");

        try {
            // Create tenant
            $tenant = Tenant::create([
                'id' => $tenantId,
                'business_name' => 'Test Tenant ' . time(),
                'username' => $domain,
                'email' => 'test' . time() . '@example.com',
                'phone' => '254700000000',
                'wallet_id' => null,
                'wallet_balance' => 0,
            ]);

            $this->info("Tenant created successfully: {$tenant->id}");

            // Create domain
            $tenant->domains()->create([
                'domain' => $fullDomain,
            ]);

            $this->info("Domain created: {$fullDomain}");

            // Create subscription
            $subscription = TenantSubscription::createForTenant($tenant);
            $this->info("Subscription created: {$subscription->id}");
            $this->info("Trial ends: {$subscription->trial_ends_at}");
            $this->info("Days remaining: {$subscription->getTrialDaysRemaining()}");

            // Verify
            $this->line("\nVerification:");
            $this->line("Total tenants: " . Tenant::count());
            $this->line("Total subscriptions: " . TenantSubscription::count());

        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
        }

        return Command::SUCCESS;
    }
}
