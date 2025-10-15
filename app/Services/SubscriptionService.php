<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\TenantSubscription;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SubscriptionService
{
    /**
     * Create a new subscription for a tenant.
     */
    public function createSubscription(Tenant $tenant, int $trialDays = 10): TenantSubscription
    {
        return TenantSubscription::createForTenant($tenant, $trialDays);
    }

    /**
     * Check if a tenant's subscription is active.
     */
    public function isSubscriptionActive(Tenant $tenant): bool
    {
        $subscription = TenantSubscription::where('tenant_id', $tenant->id)->first();
        
        if (!$subscription) {
            return false;
        }

        return $subscription->isActive() || $subscription->isOnTrial();
    }

    /**
     * Get subscription status for a tenant.
     */
    public function getSubscriptionStatus(Tenant $tenant): array
    {
        $subscription = TenantSubscription::where('tenant_id', $tenant->id)->first();
        
        if (!$subscription) {
            $subscription = $this->createSubscription($tenant);
        }

        return [
            'subscription' => $subscription,
            'is_active' => $subscription->isActive(),
            'is_on_trial' => $subscription->isOnTrial(),
            'is_expired' => $subscription->isExpired(),
            'is_past_due' => $subscription->isPastDue(),
            'trial_days_remaining' => $subscription->getTrialDaysRemaining(),
            'current_period_days_remaining' => $subscription->getCurrentPeriodDaysRemaining(),
            'next_billing_date' => $subscription->next_billing_date,
            'amount' => $subscription->amount,
            'currency' => $subscription->currency,
        ];
    }

    /**
     * Process subscription renewal after successful payment.
     */
    public function processRenewal(Tenant $tenant, float $amount): bool
    {
        try {
            $subscription = TenantSubscription::where('tenant_id', $tenant->id)->first();
            
            if (!$subscription) {
                Log::error('No subscription found for tenant during renewal', [
                    'tenant_id' => $tenant->id,
                ]);
                return false;
            }

            // Start new billing period
            $subscription->startNewPeriod();

            Log::info('Subscription renewed successfully', [
                'tenant_id' => $tenant->id,
                'amount' => $amount,
                'new_period_end' => $subscription->current_period_ends_at,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Subscription renewal failed', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Mark subscription as expired.
     */
    public function markAsExpired(Tenant $tenant): bool
    {
        try {
            $subscription = TenantSubscription::where('tenant_id', $tenant->id)->first();
            
            if ($subscription) {
                $subscription->markAsExpired();
                
                Log::info('Subscription marked as expired', [
                    'tenant_id' => $tenant->id,
                ]);

                return true;
            }

            return false;

        } catch (\Exception $e) {
            Log::error('Failed to mark subscription as expired', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Mark subscription as past due.
     */
    public function markAsPastDue(Tenant $tenant): bool
    {
        try {
            $subscription = TenantSubscription::where('tenant_id', $tenant->id)->first();
            
            if ($subscription) {
                $subscription->markAsPastDue();
                
                Log::info('Subscription marked as past due', [
                    'tenant_id' => $tenant->id,
                    'failed_payment_count' => $subscription->failed_payment_count,
                ]);

                return true;
            }

            return false;

        } catch (\Exception $e) {
            Log::error('Failed to mark subscription as past due', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get subscriptions that are ending soon.
     */
    public function getSubscriptionsEndingSoon(int $days = 3): \Illuminate\Database\Eloquent\Collection
    {
        return TenantSubscription::endingSoon($days)->with('tenant')->get();
    }

    /**
     * Get expired subscriptions.
     */
    public function getExpiredSubscriptions(): \Illuminate\Database\Eloquent\Collection
    {
        return TenantSubscription::expired()->with('tenant')->get();
    }

    /**
     * Send subscription reminder notifications.
     */
    public function sendSubscriptionReminders(): void
    {
        $endingSoon = $this->getSubscriptionsEndingSoon(3);
        
        foreach ($endingSoon as $subscription) {
            // You can implement SMS/email notifications here
            Log::info('Subscription ending soon reminder', [
                'tenant_id' => $subscription->tenant_id,
                'days_remaining' => $subscription->getCurrentPeriodDaysRemaining(),
            ]);
        }
    }

    /**
     * Process expired subscriptions (mark as expired).
     */
    public function processExpiredSubscriptions(): void
    {
        $expiredSubscriptions = TenantSubscription::where('current_period_ends_at', '<', now())
            ->where('status', 'active')
            ->get();

        foreach ($expiredSubscriptions as $subscription) {
            $subscription->markAsExpired();
            
            Log::info('Processed expired subscription', [
                'tenant_id' => $subscription->tenant_id,
            ]);
        }
    }

    /**
     * Get subscription analytics.
     */
    public function getSubscriptionAnalytics(): array
    {
        $totalSubscriptions = TenantSubscription::count();
        $activeSubscriptions = TenantSubscription::active()->count();
        $expiredSubscriptions = TenantSubscription::expired()->count();
        $trialSubscriptions = TenantSubscription::where('status', 'trial')->count();

        return [
            'total' => $totalSubscriptions,
            'active' => $activeSubscriptions,
            'expired' => $expiredSubscriptions,
            'trial' => $trialSubscriptions,
            'active_percentage' => $totalSubscriptions > 0 ? round(($activeSubscriptions / $totalSubscriptions) * 100, 2) : 0,
        ];
    }
}
