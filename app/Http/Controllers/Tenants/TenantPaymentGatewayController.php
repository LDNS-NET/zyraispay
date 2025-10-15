<?php

namespace App\Http\Controllers\Tenants;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TenantPaymentGateway;
use Inertia\Inertia;
use IntaSend\IntaSendPHP\Collection;
use Illuminate\Support\Facades\Log;

class TenantPaymentGatewayController extends Controller
{
    public function edit(Request $request)
    {
        $tenantId = tenant('id') ?? ($request->user() ? $request->user()->tenant_id : null);
        if (!$tenantId && app()->environment('local')) {
            $tenantId = \App\Models\Tenant::first()?->id;
        }
        if (!$tenantId) {
            abort(400, 'No tenant context available');
        }
        $gateways = TenantPaymentGateway::where('tenant_id', $tenantId)
            ->remember(60)
            ->get();
        return Inertia::render('Tenants/Settings/PaymentGateway', [
            'gateways' => $gateways,
        ]);
    }

    public function update(Request $request)
    {
        $tenantId = tenant('id') ?? ($request->user() ? $request->user()->tenant_id : null);
        if (!$tenantId && app()->environment('local')) {
            $tenantId = \App\Models\Tenant::first()?->id;
        }
        if (!$tenantId) {
            abort(400, 'No tenant context available');
        }
        $data = $request->validate([
            'provider' => 'required|in:intasend,mpesa,bank,custom',
            'api_key' => 'nullable|string',
            'payout_method' => 'required|in:bank,mpesa_phone,till,paybill',
            'bank_name' => 'nullable|string',
            'bank_account' => 'nullable|string',
            'phone_number' => 'nullable|string',
            'till_number' => 'nullable|string',
            'paybill_business_number' => 'nullable|string',
            'paybill_account_number' => 'nullable|string',
            'label' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);
        $gateway = TenantPaymentGateway::updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'provider' => $data['provider'],
                'label' => $data['label'] ?? $data['provider'],
            ],
            array_merge($data, [
                'created_by' => auth()->id(),
                'last_updated_by' => auth()->id(),
            ])
        );
        cache()->forget("tenant_payment_gateways_{$tenantId}");
        return redirect()->back()->with('success', 'Settings updated.');
    }
}
