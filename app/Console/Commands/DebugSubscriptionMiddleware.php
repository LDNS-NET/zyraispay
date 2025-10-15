<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use Illuminate\Support\Facades\Log;

class DebugSubscriptionMiddleware extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscription:debug-middleware';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug subscription middleware';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Debugging Subscription Middleware...');
        
        // Test tenant() helper
        $this->info('Testing tenant() helper...');
        $tenant = tenant();
        if ($tenant) {
            $this->info("✅ tenant() helper works: {$tenant->business_name}");
        } else {
            $this->warn('⚠️ tenant() helper returned null');
        }
        
        // Test subscription retrieval
        $this->info('Testing subscription retrieval...');
        $subscription = TenantSubscription::where('tenant_id', $tenant->id ?? 'test')->first();
        if ($subscription) {
            $this->info("✅ Subscription found: {$subscription->status}");
        } else {
            $this->warn('⚠️ No subscription found');
        }
        
        // Test view sharing
        $this->info('Testing view sharing...');
        $sharedData = [
            'status' => $subscription->status ?? 'unknown',
            'is_active' => $subscription ? $subscription->isActive() : false,
            'is_on_trial' => $subscription ? $subscription->isOnTrial() : false,
            'is_expired' => $subscription ? $subscription->isExpired() : false,
            'trial_days_remaining' => $subscription ? $subscription->getTrialDaysRemaining() : 0,
            'current_period_days_remaining' => $subscription ? $subscription->getCurrentPeriodDaysRemaining() : 0,
            'next_billing_date' => $subscription->next_billing_date ?? null,
            'amount' => $subscription->amount ?? 0,
            'trial_ends_at' => $subscription->trial_ends_at ?? null,
            'current_period_ends_at' => $subscription->current_period_ends_at ?? null,
        ];
        
        $this->info('Shared data structure:');
        $this->line(json_encode($sharedData, JSON_PRETTY_PRINT));
        
        // Check if middleware is registered
        $this->info('Checking middleware registration...');
        $middleware = app('router')->getMiddleware();
        if (isset($middleware['web'])) {
            $webMiddleware = $middleware['web'];
            if (in_array(\App\Http\Middleware\ShareSubscriptionData::class, $webMiddleware)) {
                $this->info('✅ ShareSubscriptionData middleware is registered in web group');
            } else {
                $this->warn('⚠️ ShareSubscriptionData middleware NOT found in web group');
            }
        }
        
        $this->info("\n✅ Debug completed!");
    }
}
