<?php

namespace App\Http\Controllers;

use App\Models\TenantSubscription;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use IntaSend\IntaSendPHP\APIService;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    protected $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Show the expired subscription page.
     */
    public function expired()
    {
        $tenant = tenant();
        $subscription = TenantSubscription::where('tenant_id', $tenant->id)->first();

        if (!$subscription) {
            $subscription = TenantSubscription::createForTenant($tenant);
        }

        return Inertia::render('Subscription/Expired', [
            'subscription' => $subscription,
            'tenant' => $tenant,
            'trialDaysRemaining' => $subscription->getTrialDaysRemaining(),
            'currentPeriodDaysRemaining' => $subscription->getCurrentPeriodDaysRemaining(),
        ]);
    }

    /**
     * Show the payment page.
     */
    public function payment()
    {
        $tenant = tenant();
        $subscription = TenantSubscription::where('tenant_id', $tenant->id)->first();

        if (!$subscription) {
            return redirect()->route('subscription.expired');
        }

        return Inertia::render('Subscription/Payment', [
            'subscription' => $subscription,
            'tenant' => $tenant,
            'amount' => $subscription->amount,
            'currency' => $subscription->currency,
        ]);
    }

    /**
     * Process the subscription payment.
     */
    public function processPayment(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|min:10|max:15',
            'amount' => 'required|numeric|min:1',
        ]);

        $tenant = tenant();
        $subscription = TenantSubscription::where('tenant_id', $tenant->id)->first();

        if (!$subscription) {
            return redirect()->route('subscription.expired');
        }

        try {
            // Initialize IntaSend API
            $service = new APIService([
                'token' => env('INTASEND_SECRET_KEY'),
                'publishable_key' => env('INTASEND_PUBLIC_KEY'),
                'test' => env('APP_ENV') !== 'production',
            ]);

            // Create payment request
            $paymentData = [
                'amount' => $request->amount,
                'currency' => 'KES',
                'method' => 'MPESA',
                'phone_number' => $request->phone,
                'email' => $tenant->email,
                'first_name' => explode(' ', $tenant->business_name)[0] ?? 'Customer',
                'last_name' => explode(' ', $tenant->business_name)[1] ?? '',
                'reference' => 'SUBSCRIPTION_' . $tenant->id . '_' . time(),
                'callback_url' => route('subscription.payment.callback'),
            ];

            $response = $service->collections->mpesa($paymentData);

            if ($response['status'] === 'success') {
                // Store payment reference for callback verification
                session(['subscription_payment_ref' => $response['reference']]);
                
                return Inertia::render('Subscription/PaymentProcessing', [
                    'paymentReference' => $response['reference'],
                    'amount' => $request->amount,
                    'phone' => $request->phone,
                ]);
            } else {
                return back()->withErrors(['payment' => 'Payment initiation failed. Please try again.']);
            }

        } catch (\Exception $e) {
            Log::error('Subscription payment error', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['payment' => 'Payment processing failed. Please try again.']);
        }
    }

    /**
     * Handle payment callback from IntaSend.
     */
    public function paymentCallback(Request $request)
    {
        try {
            $reference = $request->input('reference');
            $status = $request->input('status');
            $amount = $request->input('amount');

            Log::info('Subscription payment callback', [
                'reference' => $reference,
                'status' => $status,
                'amount' => $amount,
            ]);

            if ($status === 'COMPLETED') {
                // Find tenant by payment reference
                $tenantId = $this->extractTenantIdFromReference($reference);
                $tenant = \App\Models\Tenant::find($tenantId);
                
                if ($tenant) {
                    $subscription = TenantSubscription::where('tenant_id', $tenant->id)->first();
                    
                    if ($subscription) {
                        // Start new billing period
                        $subscription->startNewPeriod();
                        
                        Log::info('Subscription renewed successfully', [
                            'tenant_id' => $tenant->id,
                            'amount' => $amount,
                        ]);
                    }
                }
            }

            return response()->json(['status' => 'received']);

        } catch (\Exception $e) {
            Log::error('Subscription payment callback error', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);

            return response()->json(['status' => 'error'], 500);
        }
    }

    /**
     * Show payment success page.
     */
    public function success()
    {
        $tenant = tenant();
        $subscription = TenantSubscription::where('tenant_id', $tenant->id)->first();

        return Inertia::render('Subscription/Success', [
            'subscription' => $subscription,
            'tenant' => $tenant,
        ]);
    }

    /**
     * Show payment cancellation page.
     */
    public function cancel()
    {
        return Inertia::render('Subscription/Cancel');
    }

    /**
     * Get subscription status for API.
     */
    public function status()
    {
        $tenant = tenant();
        $subscription = TenantSubscription::where('tenant_id', $tenant->id)->first();

        if (!$subscription) {
            $subscription = TenantSubscription::createForTenant($tenant);
        }

        return response()->json([
            'subscription' => $subscription,
            'is_active' => $subscription->isActive(),
            'is_on_trial' => $subscription->isOnTrial(),
            'is_expired' => $subscription->isExpired(),
            'trial_days_remaining' => $subscription->getTrialDaysRemaining(),
            'current_period_days_remaining' => $subscription->getCurrentPeriodDaysRemaining(),
        ]);
    }

    /**
     * Extract tenant ID from payment reference.
     */
    private function extractTenantIdFromReference(string $reference): ?string
    {
        if (preg_match('/SUBSCRIPTION_(\w+)_\d+/', $reference, $matches)) {
            return $matches[1];
        }
        
        return null;
    }
}
