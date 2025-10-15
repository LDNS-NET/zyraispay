<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use App\Models\TenantSubscription;

class TestSubscriptionData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscription:test-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test subscription data for dashboard';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Subscription Data...');
        
        $tenant = Tenant::first();
        if (!$tenant) {
            $this->error('No tenants found');
            return;
        }
        
        $this->info("Testing tenant: {$tenant->business_name}");
        
        $subscription = TenantSubscription::where('tenant_id', $tenant->id)->first();
        if (!$subscription) {
            $this->error('No subscription found for this tenant');
            return;
        }
        
        $this->info("Subscription Status: {$subscription->status}");
        $this->info("Is on trial: " . ($subscription->isOnTrial() ? 'Yes' : 'No'));
        $this->info("Trial days remaining: {$subscription->getTrialDaysRemaining()}");
        $this->info("Trial ends at: {$subscription->trial_ends_at}");
        $this->info("Amount: KES {$subscription->amount}");
        
        // Test the data structure that would be passed to the dashboard
        $dashboardData = [
            'status' => $subscription->status,
            'is_active' => $subscription->isActive(),
            'is_on_trial' => $subscription->isOnTrial(),
            'is_expired' => $subscription->isExpired(),
            'trial_days_remaining' => $subscription->getTrialDaysRemaining(),
            'current_period_days_remaining' => $subscription->getCurrentPeriodDaysRemaining(),
            'next_billing_date' => $subscription->next_billing_date,
            'amount' => $subscription->amount,
            'trial_ends_at' => $subscription->trial_ends_at,
            'current_period_ends_at' => $subscription->current_period_ends_at,
        ];
        
        $this->info("\nDashboard Data Structure:");
        $this->line(json_encode($dashboardData, JSON_PRETTY_PRINT));
        
        $this->info("\n✅ Subscription data test completed successfully!");
    }
}
