<?php

namespace App\Http\Controllers\Tenants;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TenantWhatsappGateway;
use Inertia\Inertia;

class TenantWhatsappGatewayController extends Controller
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
        $userId = auth()->id();
        $gateways = TenantWhatsappGateway::where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->remember(60)
            ->get();
        return Inertia::render('Tenants/Settings/WhatsappGateway', [
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
            'provider' => 'required|in:meta,twilio,gupshup,custom',
            'api_key' => 'nullable|string',
            'api_secret' => 'nullable|string',
            'phone_number' => 'nullable|string',
            'webhook_url' => 'nullable|url',
            'status_callback_url' => 'nullable|url',
            'region' => 'nullable|string',
            'custom_parameters' => 'nullable|array',
            'label' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);
        $userId = auth()->id();
        $gateway = TenantWhatsappGateway::updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'user_id' => $userId,
                'provider' => $data['provider'],
                'label' => $data['label'] ?? $data['provider'],
            ],
            array_merge($data, [
                'created_by' => $userId,
                'last_updated_by' => $userId,
            ])
        );
        cache()->forget("tenant_whatsapp_gateways_{$tenantId}");
        return redirect()->back()->with('success', 'WhatsApp gateway settings updated.');
    }
}
