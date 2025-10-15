<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
     *
     * @throws \Illuminate\Validation\ValidationException
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
            // Generate subdomain from business name
            $baseSubdomain = strtolower(preg_replace('/[^a-z0-9]+/', '-', $request->business_name));
            $subdomain = $baseSubdomain;
            $i = 1;
            while (Tenant::query()->whereHas('domains', function($q) use ($subdomain) {
                $q->where('domain', $subdomain . '.yourapp.com');
            })->exists()) {
                $subdomain = $baseSubdomain . '-' . $i;
                $i++;
            }

            // --- IntaSend wallet creation ---
            $walletId = null;
            $response = null;
            try {
                $credentials = [
                    'token' => env('INTASEND_SECRET_KEY'),
                    'publishable_key' => env('INTASEND_PUBLIC_KEY'),
                    'test' => env('APP_ENV') !== 'production',
                ];
                $wallet = new Wallet();
                $wallet->init($credentials);
                $response = $wallet->create('KES', $subdomain, true);
                $walletId = $response->wallet_id ?? null;
            } catch (\Exception $e) {
                \Log::error('Failed to create IntaSend wallet for tenant', ['subdomain' => $subdomain, 'error' => $e->getMessage()]);
            }
            \Log::info('Wallet creation result', ['walletId' => $walletId, 'response' => $response ?? null]);
            // --- End IntaSend wallet creation ---

            // Fallback for local/test environment if wallet creation fails
            if (!$walletId) {
                if (app()->environment(['local', 'testing'])) {
                    $walletId = 'DUMMY-' . uniqid();
                    \Log::warning('Using dummy wallet ID for tenant in local/test environment.', ['wallet_id' => $walletId]);
                } else {
                    DB::rollBack();
                    return back()->withErrors(['wallet' => 'Failed to create IntaSend wallet. Please try again or contact support.']);
                }
            }

            // 1. Create the tenant with wallet_id
            $tenant = Tenant::create([
                'id' => (string) Str::uuid(),
                'business_name' => $request->business_name,
                'username' => $request->username,
                'email' => $request->email,
                'phone' => $request->phone,
                'wallet_id' => $walletId,
                // ...existing code...
            ]);

            // 2. Assign a domain/subdomain
            $tenant->domains()->create([
                'domain' => $subdomain . '.billyetu-production.up.railway.app',
            ]);

            // 3. Create a tenant admin user in the tenant's DB
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
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        // 4. Redirect to tenant's subdomain dashboard
        // return \Inertia\Inertia::location('https://' . $subdomain . '.billyetu-production.up.railway.app/dashboard'); // Uncomment for production subdomain redirect
        return redirect()->route('dashboard');
    }
}
