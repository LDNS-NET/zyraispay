<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TenantSubscription;
use App\Models\Tenant;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class SubscriptionAnalyticsController extends Controller
{
    protected $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Display subscription analytics dashboard.
     */
    public function index()
    {
        $analytics = $this->subscriptionService->getSubscriptionAnalytics();
        
        // Get detailed subscription data
        $subscriptions = TenantSubscription::with('tenant')->get();
        
        // Monthly revenue calculation
        $monthlyRevenue = TenantSubscription::where('status', 'active')
            ->sum('amount');
            
        // Trial conversion rate
        $totalTrials = TenantSubscription::where('status', 'trial')->count();
        $convertedTrials = TenantSubscription::where('status', 'active')
            ->whereNotNull('trial_ends_at')
            ->count();
        $conversionRate = $totalTrials > 0 ? round(($convertedTrials / $totalTrials) * 100, 2) : 0;
        
        // Subscriptions ending soon
        $endingSoon = $this->subscriptionService->getSubscriptionsEndingSoon(7);
        
        // Recent renewals
        $recentRenewals = TenantSubscription::where('status', 'active')
            ->where('last_payment_at', '>=', now()->subDays(30))
            ->with('tenant')
            ->orderBy('last_payment_at', 'desc')
            ->limit(10)
            ->get();

        return Inertia::render('Admin/SubscriptionAnalytics', [
            'analytics' => $analytics,
            'monthlyRevenue' => $monthlyRevenue,
            'conversionRate' => $conversionRate,
            'endingSoon' => $endingSoon,
            'recentRenewals' => $recentRenewals,
            'subscriptions' => $subscriptions->map(function ($subscription) {
                return [
                    'id' => $subscription->id,
                    'tenant_id' => $subscription->tenant_id,
                    'tenant_name' => $subscription->tenant->business_name ?? 'Unknown',
                    'status' => $subscription->status,
                    'amount' => $subscription->amount,
                    'trial_ends_at' => $subscription->trial_ends_at,
                    'current_period_ends_at' => $subscription->current_period_ends_at,
                    'last_payment_at' => $subscription->last_payment_at,
                    'trial_days_remaining' => $subscription->getTrialDaysRemaining(),
                    'current_period_days_remaining' => $subscription->getCurrentPeriodDaysRemaining(),
                ];
            }),
        ]);
    }

    /**
     * Get subscription statistics for API.
     */
    public function statistics()
    {
        $analytics = $this->subscriptionService->getSubscriptionAnalytics();
        
        return response()->json([
            'analytics' => $analytics,
            'monthly_revenue' => TenantSubscription::where('status', 'active')->sum('amount'),
            'ending_soon_count' => $this->subscriptionService->getSubscriptionsEndingSoon(7)->count(),
            'expired_count' => $this->subscriptionService->getExpiredSubscriptions()->count(),
        ]);
    }

    /**
     * Send subscription reminders.
     */
    public function sendReminders()
    {
        $this->subscriptionService->sendSubscriptionReminders();
        
        return response()->json(['message' => 'Reminders sent successfully']);
    }

    /**
     * Process expired subscriptions.
     */
    public function processExpired()
    {
        $this->subscriptionService->processExpiredSubscriptions();
        
        return response()->json(['message' => 'Expired subscriptions processed']);
    }
}
