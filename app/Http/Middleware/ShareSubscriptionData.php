<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\TenantSubscription;

class ShareSubscriptionData
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = tenant();
        
        if ($tenant) {
            $subscription = TenantSubscription::where('tenant_id', $tenant->id)->first();
            
            // Create subscription if it doesn't exist
            if (!$subscription) {
                $subscription = TenantSubscription::createForTenant($tenant);
            }
            
            // Share subscription data with all views
            view()->share('subscription', [
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
            ]);
        }

        return $next($request);
    }
}
