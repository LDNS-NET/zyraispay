<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Lead;
use Illuminate\Http\Request;
use Inertia\Inertia;
use IntaSend\IntaSendPHP\APIService;

class TenantController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'required|string|unique:domains,domain',
            'email' => 'required|email',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:8',
        ]);

        // Generate a unique tenant ID
        $tenantId = \Illuminate\Support\Str::uuid()->toString();
        
        // Ensure domain is unique
        $domain = $validated['domain'];
        $fullDomain = $domain . '.zyraispay.test';
        
        // Check if domain already exists
        if (\Stancl\Tenancy\Database\Models\Domain::where('domain', $fullDomain)->exists()) {
            return back()->withErrors(['domain' => 'This domain is already taken. Please choose a different domain.']);
        }

        // Create IntaSend wallet for this tenant
        $walletId = null;
        try {
            $service = new APIService([
                'token' => env('INTASEND_SECRET_KEY'),
                'test' => env('APP_ENV') !== 'production',
            ]);
            $walletResponse = $service->wallets->create([
                'currency' => 'KES',
                'label' => $validated['domain'],
                'can_disburse' => true,
            ]);
            $walletId = $walletResponse['wallet_id'] ?? null;
            \Log::info('IntaSend wallet created for tenant', ['domain' => $validated['domain'], 'wallet_id' => $walletId]);
        } catch (\Exception $e) {
            \Log::error('Failed to create IntaSend wallet for tenant', ['domain' => $validated['domain'], 'error' => $e->getMessage()]);
        }

        $tenant = Tenant::create([
            'id' => $tenantId, // Use UUID instead of domain
            'wallet_id' => $walletId,
            'business_name' => $validated['name'],
            'username' => $validated['domain'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
        ]);

        $tenant->domains()->create([
            'domain' => $fullDomain,
        ]);

        $tenant->run(function () use ($validated) {
            \App\Models\User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => bcrypt($validated['password']),
            ]);
        });

        // Create subscription for the tenant
        \App\Models\TenantSubscription::createForTenant($tenant);

        \Log::info('New tenant created successfully', [
            'tenant_id' => $tenant->id,
            'domain' => $fullDomain,
            'business_name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        return redirect()->route('admin.dashboard')->with('success', 'Tenant created successfully.');
    }

    public function edit($id)
    {
        $tenant = Tenant::findOrFail($id);

        return Inertia::render('Admin/EditTenant', [
            'tenant' => $tenant,
        ]);
    }

    public function update(Request $request, $id)
    {
        $tenant = Tenant::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'required|string|unique:domains,domain,' . $tenant->id . ',tenant_id',
            'email' => 'required|email',
            'phone' => 'required|string|max:20',
        ]);

        $tenant->update([
            'data' => [
                'name' => $validated['name'],
                'domain' => $validated['domain'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
            ],
        ]);

        return redirect()->route('admin.dashboard')->with('success', 'Tenant updated.');
    }

    public function destroy($id)
    {
        $tenant = Tenant::findOrFail($id);
        $tenant->delete();

        return redirect()->route('admin.dashboard')->with('success', 'Tenant deleted.');
    }

    public function create(Request $request)
    {
        $lead = $request->has('lead')
            ? Lead::find($request->input('lead'))
            : null;

        return Inertia::render('Admin/CreateTenant', [
            'lead' => $lead,
        ]);
    }
}
