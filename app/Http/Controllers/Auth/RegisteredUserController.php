<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use IntaSend\IntaSendPHP\Wallet;
use Illuminate\Support\Facades\Log;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(Request $request)
    {
        $request->validate([
            'business_name' => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:tenants,email',
            'phone' => 'required|string|max:20',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        DB::beginTransaction();

        try {
            // Generate clean subdomain from business name
$baseSubdomain = strtolower($request->business_name);
$baseSubdomain = preg_replace('/[^a-z0-9]+/', '-', $baseSubdomain); // replace invalid chars
$baseSubdomain = trim($baseSubdomain, '-'); // remove leading/trailing dashes
$baseSubdomain = preg_replace('/-+/', '-', $baseSubdomain); // collapse multiple dashes

// Ensure subdomain starts with a letter
if (!preg_match('/^[a-z]/', $baseSubdomain)) {
    $baseSubdomain = 'biz-' . $baseSubdomain;
}

// Limit to 30 chars (DNS label limit)
$baseSubdomain = substr($baseSubdomain, 0, 30);

$subdomain = $baseSubdomain;
$i = 1;

// Ensure uniqueness
while (Tenant::query()->whereHas('domains', function ($q) use ($subdomain) {
    $q->where('domain', $subdomain . '.zyraispay.zyraaf.cloud');
})->exists()) {
    $subdomain = $baseSubdomain . '-' . $i;
    $i++;
}


            // --- IntaSend wallet creation ---
            $walletId = null;
            try {
                $wallet = new Wallet();
                $wallet->init([
                    'token' => env('INTASEND_SECRET_KEY'),
                    'publishable_key' => env('INTASEND_PUBLIC_KEY'),
                    'test' => env('APP_ENV') !== 'production',
                ]);

                $response = $wallet->create('KES', $subdomain, true);
                $walletId = $response->wallet_id ?? null;
            } catch (\Exception $e) {
                Log::error('Failed to create IntaSend wallet for tenant', [
                    'subdomain' => $subdomain,
                    'error' => $e->getMessage()
                ]);
            }

            if (!$walletId) {
                if (app()->environment(['local', 'testing'])) {
                    $walletId = 'DUMMY-' . uniqid();
                    Log::warning('Using dummy wallet ID for tenant.', ['wallet_id' => $walletId]);
                } else {
                    DB::rollBack();
                    return back()->withErrors(['wallet' => 'Failed to create wallet. Try again later.']);
                }
            }

            // Create tenant
            $tenant = Tenant::create([
                'id' => (string) Str::uuid(),
                'business_name' => $request->business_name,
                'username' => $request->username,
                'email' => $request->email,
                'phone' => $request->phone,
                'wallet_id' => $walletId,
            ]);

            // Assign tenant domain
            $tenant->domains()->create([
                'domain' => $subdomain . '.zyraispay.zyraaf.cloud',
            ]);

            // Create admin user inside tenant DB
            $tenant->run(function () use ($request) {
                User::create([
                    'name' => $request->username,
                    'username' => $request->username,
                    'business_name' => $request->business_name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'password' => Hash::make($request->password),
                    'role' => 'admin',
                ]);
            });

            DB::commit();

            // Redirect to tenant dashboard (on the subdomain)
            $tenantUrl = 'https://' . $subdomain . '.zyraispay.zyraaf.cloud/dashboard';
            return Inertia::location($tenantUrl);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Tenant registration failed', ['error' => $e->getMessage()]);
            return back()->withErrors(['register' => 'Registration failed. Please try again.']);
        }
    }
}
