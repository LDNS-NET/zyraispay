<?php

namespace App\Http\Controllers\Tenants;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Package;
use App\Models\Tenants\NetworkUser;
use App\Models\Voucher;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Tenants\Payment;
use Illuminate\Support\Facades\Http;
use IntaSend\IntaSendPHP\Collection;
use App\Models\TenantSetting;
use App\Services\TenantPayoutService;
use Inertia\Inertia;
use App\Services\AfricaTalkingService;
use Illuminate\Support\Facades\Log;

class CaptivePortalController extends Controller
{
    protected TenantPayoutService $payoutService;

    public function __construct(TenantPayoutService $payoutService)
    {
        $this->payoutService = $payoutService;
    }

    /**
     * Generate a system-wide unique account number for NetworkUser.
     */
    private function generateAccountNumber(): string
    {
        do {
            // Example: 10-digit random number prefixed with 'NU'
            $accountNumber = 'NU' . mt_rand(1000000000, 9999999999);
        } while (NetworkUser::where('account_number', $accountNumber)->exists());
        return $accountNumber;
    }
    public function packages()
    {
        $packages = Package::query()->where('type', 'hotspot')->get();
        return response()->json(['packages' => $packages]);
    }

    // POST /hotspot/login
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);
        $user = NetworkUser::where('username', $request->username)
            ->where('type', 'hotspot')
            ->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['success' => false, 'message' => 'Invalid credentials'], 401);
        }
        if ($user->expires_at && now()->greaterThan($user->expires_at)) {
            return response()->json(['success' => false, 'message' => 'Account expired'], 403);
        }
        return response()->json(['success' => true, 'user' => $user]);
    }

    // POST /hotspot/voucher
    /*public function voucher(Request $request)
    {
        $request->validate([
            'voucher_code' => 'required|string',
        ]);

        $voucher = Voucher::where('code', $request->voucher_code)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->first();

        if (!$voucher) {
            return response()->json(['success' => false, 'message' => 'Invalid or expired voucher'], 404);
        }

        $username = 'HS' . strtoupper(Str::random(6));
        $password = Str::random(8);

        $user = NetworkUser::create([
            'account_number' => $this->generateAccountNumber(),
            'username' => $username,
            'password' => bcrypt($password),
            'type' => 'hotspot',
            'package_id' => $voucher->package_id,
            'expires_at' => $voucher->expires_at,
            'registered_at' => now(),
        ]);

        $voucher->update([
            'status' => 'used',
            'used_by' => $user->id,
            'used_at' => now(),
        ]);

        return response()->json(['success' => true, 'user' => $user, 'plain_password' => $password]);
    }*/



    public function voucher(Request $request)
    {
        $request->validate([
            'voucher_code' => 'required|string',
        ]);

        // Find voucher and ensure it's active + not expired
        $voucher = Voucher::where('code', $request->voucher_code)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->first();

        if (!$voucher) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired voucher'
            ], 404);
        }

        // Generate random hotspot user
        $username = 'HS' . strtoupper(Str::random(6));
        $plainPassword = Str::random(8);

        $networkUser = NetworkUser::create([
            'account_number' => $this->generateAccountNumber(),
            'username' => $username,
            'password' => bcrypt($plainPassword),
            'type' => 'hotspot',
            'package_id' => $voucher->package_id,
            'expires_at' => $voucher->expires_at,   // expiry comes from voucher
            'registered_at' => now(),
        ]);

        // Mark voucher as used
        $voucher->update([
            'status' => 'used',
            'used_by' => $networkUser->id,
            'used_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'user' => [
                'username' => $username,
                'password' => $plainPassword,
            ],
            'message' => 'Voucher accepted and account created',
        ]);
    }





    /*public function show()
    {
        $tenant = tenant();
        $businessName = $tenant?->getAttribute('business_name') ?? 'Hotspot';

        $packages = Package::where('type', 'hotspot')->get();

        return Inertia::render('Tenants/CaptivePortal/Index', [
            'businessName' => $businessName,
            'packages' => $packages,
        ]);
    }*/

    public function show()
    {
        $packages = Package::where('type', 'hotspot')->get();
        $tenant = tenant();
        return Inertia::render('Tenants/CaptivePortal/Index', [
            'packages' => $packages,
            'business' => [
                'name' => $tenant->business_name,
                'phone' => $tenant->phone,
            ],
        ]);
    }



    // POST /hotspot/pay
    public function pay(Request $request)
    {
        try {
            $request->validate([
                'package_id' => 'required|exists:packages,id',
                'phone' => 'required|string',
            ]);
            $package = Package::findOrFail($request->package_id);
            $amount = $package->price;
            $phone = $request->phone;

            // Log the incoming request
            \Log::info('STK Push initiation request', [
                'package_id' => $request->package_id,
                'phone' => $phone,
                'amount' => $amount,
            ]);

            // Accept phone in 07xxxxxxxx or 01xxxxxxxx or 2547xxxxxxxx or 2541xxxxxxxx
            if (preg_match('/^(07|01)\d{8}$/', $phone)) {
                // Convert to 2547xxxxxxxx or 2541xxxxxxxx
                $phone = '254' . substr($phone, 1);
            }
            if (!preg_match('/^254(7|1)\d{8}$/', $phone)) {
                \Log::error('Invalid phone format for STK Push', ['phone' => $phone]);
                return response()->json(['success' => false, 'message' => 'Invalid phone number format. Use 07xxxxxxxx, 01xxxxxxxx, or 2547xxxxxxxx/2541xxxxxxxx.']);
            }

            $credentials = [
                'token' => env('INTASEND_SECRET_KEY'),
                'publishable_key' => env('INTASEND_PUBLIC_KEY'),
                'test' => env('APP_ENV') !== 'production',
            ];

            // Mask credentials for logging
            $maskedCreds = $credentials;
            $maskedCreds['token'] = substr($credentials['token'] ?? '', 0, 6) . '...';
            $maskedCreds['publishable_key'] = substr($credentials['publishable_key'] ?? '', 0, 6) . '...';
            \Log::info('Using IntaSend credentials', $maskedCreds);

            $collection = new Collection();
            $collection->init($credentials);

            $api_ref = 'HS-' . uniqid();
            $tenantId = tenant('id') ?? (request()->user() ? request()->user()->tenant_id : null);
            $tenant = \App\Models\Tenant::find($tenantId);
            if (!$tenant || !$tenant->wallet_id) {
                \Log::error('No wallet_id found for tenant', ['tenant_id' => $tenantId]);
                return response()->json(['success' => false, 'message' => 'No wallet ID configured for this tenant. Please contact support.']);
            }
            $walletId = $tenant->wallet_id;
            \Log::info('Using IntaSend wallet_id', ['wallet_id' => $walletId, 'tenant_id' => $tenantId]);

            $response = $collection->create(
                $amount,
                $phone,
                'KES',
                'MPESA_STK_PUSH',
                $api_ref,
                '', // name (optional)
                'customer@example.com', // email (optional)
                [
                    'wallet_id' => $walletId,
                ]
            );

            \Log::info('IntaSend SDK response', ['response' => json_decode(json_encode($response), true)]);

            if (empty($response->invoice)) {
                \Log::error('IntaSend SDK error', ['response' => $response]);
                return response()->json(['success' => false, 'message' => 'Failed to initiate payment.']);
            }

            // Store payment request
            $payment = Payment::create([
                'phone' => $phone,
                'package_id' => $package->id,
                'amount' => $amount,
                'status' => 'pending',
                'intasend_reference' => $response->invoice,
                'intasend_checkout_id' => $response->checkout_id ?? null,
                'response' => json_decode(json_encode($response), true),
            ]);

            return response()->json(['success' => true, 'message' => 'STK Push sent. Complete payment on your phone.', 'payment_id' => $payment->id]);
        } catch (\Exception $e) {
            \Log::error('IntaSend SDK exception', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Payment error. ' . $e->getMessage()]);
        }
    }

    // POST /hotspot/payment/callback
    public function paymentCallback(Request $request, AfricaTalkingService $smsService)
    {
        try {
            $request->validate([
                'phone' => 'required|string',
                'package_id' => 'required|exists:packages,id',
            ]);
            $payment = Payment::where('phone', $request->phone)
                ->where('package_id', $request->package_id)
                ->orderByDesc('id')
                ->first();
            if (!$payment) {
                return response()->json(['success' => false, 'message' => 'No payment found.']);
            }
            if ($payment->status === 'paid') {
                // Already paid, create user if not already created
                $package = Package::find($request->package_id);
                $username = 'HS' . strtoupper(Str::random(6));
                $password = Str::random(8);
                $user = NetworkUser::create([
                    'account_number' => $this->generateAccountNumber(),
                    'username' => $username,
                    'password' => bcrypt($password),
                    'phone' => $request->phone,
                    'type' => 'hotspot',
                    'package_id' => $package->id,
                    'expires_at' => now()->addDays($package->duration),
                    'registered_at' => now(),
                ]);
                return $this->_handleSuccessfulPayment($payment, $package, $smsService);
            }
            // SSL verification enabled for production and secure local dev
            $statusResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.intasend.secret_key'),
                'Content-Type' => 'application/json',
            ])->get(config('services.intasend.base_url') . '/mpesa/transaction-status/', [
                        'invoice' => $payment->intasend_reference,
                    ]);
            $statusData = $statusResponse->json();
            if ($statusResponse->successful() && isset($statusData['status']) && $statusData['status'] === 'PAID') {
                $payment->status = 'paid';
                $payment->response = $statusData;
                $payment->save();
                $package = Package::find($request->package_id);
                $username = 'HS' . strtoupper(Str::random(6));
                $password = Str::random(8);
                $user = NetworkUser::create([
                    'account_number' => $this->generateAccountNumber(),
                    'username' => $username,
                    'password' => bcrypt($password),
                    'phone' => $request->phone,
                    'type' => 'hotspot',
                    'package_id' => $package->id,
                    'expires_at' => now()->addDays($package->duration),
                    'registered_at' => now(),
                ]);
                return $this->_handleSuccessfulPayment($payment, $package, $smsService);
            }
            return response()->json(['success' => false, 'message' => 'Payment not confirmed yet.']);
        } catch (\Exception $e) {
            \Log::error('IntaSend callback exception', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Payment status error. ' . $e->getMessage()]);
        }
    }

    /**
     * Handle all post-payment success actions.
     */
    private function _handleSuccessfulPayment(Payment $payment, Package $package, AfricaTalkingService $smsService)
    {
        $username = 'HS' . strtoupper(Str::random(6));
        $password = Str::random(8);

        $user = NetworkUser::create([
            'account_number' => $this->generateAccountNumber(),
            'username' => $username,
            'password' => bcrypt($password),
            'phone' => $payment->phone,
            'type' => 'hotspot',
            'package_id' => $package->id,
            'expires_at' => now()->addDays($package->duration),
            'registered_at' => now(),
        ]);

        // --- Send Payment Confirmation SMS ---
        $notificationSettings = TenantSetting::where('category', 'notifications')->first()->settings ?? [];
        if (!empty($notificationSettings['payment_confirmation']['enabled'])) {
            $messageTemplate = $notificationSettings['payment_confirmation']['message'] ?? 'Thank you for your payment.';
            $placeholders = [
                '{name}' => $user->full_name ?? $user->username,
                '{package_name}' => $package->name,
                '{amount}' => $payment->amount,
                '{transaction_id}' => $payment->transaction_id,
            ];
            $message = str_replace(array_keys($placeholders), array_values($placeholders), $messageTemplate);
            if ($user->phone) {
                try {
                    $smsService->sendSMS([$user->phone], $message);
                    \Log::info('Sent payment confirmation SMS', ['user_id' => $user->id, 'phone' => $user->phone]);
                } catch (\Exception $e) {
                    \Log::error('Failed to send payment confirmation SMS', ['error' => $e->getMessage()]);
                }
            }
        }

        // --- Payout Logic ---
        $this->disburseToTenant($payment);

        return response()->json(['success' => true, 'user' => $user, 'plain_password' => $password]);
    }

    /**
     * Disburse 99% of payment to tenant's payout method using IntaSend Payout API
     */
    protected function disburseToTenant($payment)
    {
        $tenantId = tenant('id') ?? (request()->user() ? request()->user()->tenant_id : null);
        if (!$tenantId) return;
        $meta = ['payment_id' => $payment->id];
        app(\App\Services\TenantPayoutService::class)->disburse($payment->amount, $tenantId, $meta);
    }
}
