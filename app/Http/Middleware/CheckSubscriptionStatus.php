<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\TenantSubscription;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscriptionStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = tenant();
        
        // If no tenant context, allow the request to proceed
        if (!$tenant) {
            return $next($request);
        }

        // Get the tenant's subscription
        $subscription = TenantSubscription::where('tenant_id', $tenant->id)->first();

        // If no subscription exists, create one with trial period
        if (!$subscription) {
            $subscription = TenantSubscription::createForTenant($tenant);
        }

        // Check if subscription is expired or past due
        if ($subscription->isExpired() || $subscription->isPastDue()) {
            // Allow access to subscription-related routes
            if ($this->isSubscriptionRoute($request)) {
                return $next($request);
            }

            // Redirect to subscription page
            return redirect()->route('subscription.expired');
        }

        // Check if subscription is ending soon (within 3 days) and show warning
        if ($subscription->isActive() && $subscription->getCurrentPeriodDaysRemaining() <= 3) {
            // You can add a flash message here to show warning
            session()->flash('subscription_warning', [
                'message' => "Your subscription expires in {$subscription->getCurrentPeriodDaysRemaining()} days.",
                'days_remaining' => $subscription->getCurrentPeriodDaysRemaining()
            ]);
        }

        return $next($request);
    }

    /**
     * Check if the current route is subscription-related.
     */
    private function isSubscriptionRoute(Request $request): bool
    {
        $subscriptionRoutes = [
            'subscription.expired',
            'subscription.payment',
            'subscription.payment.callback',
            'subscription.success',
            'subscription.cancel',
            'logout',
        ];

        return in_array($request->route()?->getName(), $subscriptionRoutes);
    }
}
