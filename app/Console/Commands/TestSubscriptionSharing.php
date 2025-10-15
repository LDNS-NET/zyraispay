<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Http\Middleware\ShareSubscriptionData;
use Illuminate\Http\Request;

class TestSubscriptionSharing extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscription:test-sharing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test subscription data sharing middleware';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Subscription Data Sharing...');
        
        $tenant = Tenant::first();
        if (!$tenant) {
            $this->error('No tenants found');
            return;
        }
        
        $this->info("Testing tenant: {$tenant->business_name}");
        
        // Simulate the middleware behavior
        $subscription = TenantSubscription::where('tenant_id', $tenant->id)->first();
        
        if (!$subscription) {
            $this->error('No subscription found for this tenant');
            return;
        }
        
        // Create the data structure that would be shared
        $sharedData = [
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
        
        $this->info("\nShared Subscription Data:");
        $this->line(json_encode($sharedData, JSON_PRETTY_PRINT));
        
        // Test what would be available in Vue components
        $this->info("\nVue Component Access:");
        $this->line("\$page.props.subscription.status: " . $sharedData['status']);
        $this->line("\$page.props.subscription.is_on_trial: " . ($sharedData['is_on_trial'] ? 'true' : 'false'));
        $this->line("\$page.props.subscription.trial_days_remaining: " . $sharedData['trial_days_remaining']);
        
        $this->info("\n✅ Subscription data sharing test completed!");
    }
}
