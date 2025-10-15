<?php

namespace App\Http\Controllers\Tenants;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TenantSmsGateway;
use Inertia\Inertia;

class TenantSmsGatewayController extends Controller
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
        $gateways = TenantSmsGateway::where('tenant_id', $tenantId)
            ->remember(60)
            ->get();
        return Inertia::render('Tenants/Settings/SmsGateway', [
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
        $baseRules = [
            'provider' => 'required|in:talksasa,bytewave,africastalking,textsms,mobitech,twilio,custom',
            'label' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'is_default' => 'nullable|boolean',
        ];
        $provider = $request->input('provider');
        $providerRules = [];
        if (in_array($provider, ['talksasa', 'bytewave'])) {
            $providerRules = [
                'api_key' => 'required|string',
                'sender_id' => 'required|string',
            ];
        } elseif (in_array($provider, ['africastalking', 'textsms', 'mobitech'])) {
            $providerRules = [
                'username' => 'required|string',
                'api_key' => 'required|string',
                'sender_id' => 'required|string',
            ];
        } else {
            $providerRules = [
                'api_key' => 'required|string',
                'api_secret' => 'required|string',
            ];
        }
        $data = $request->validate(array_merge($baseRules, $providerRules));

        // If no default exists, force this as default
        $hasDefault = \App\Models\TenantSmsGateway::where('tenant_id', $tenantId)->where('is_default', true)->exists();
        $data['is_default'] = $request->input('is_default', !$hasDefault);

        // Set all other gateways as not default if this is default
        if ($data['is_default']) {
            \App\Models\TenantSmsGateway::where('tenant_id', $tenantId)
                ->where('provider', '!=', $data['provider'])
                ->update(['is_default' => false]);
        }

        $gateway = TenantSmsGateway::where('tenant_id', $tenantId)->first();
        if ($gateway) {
            $gateway->provider = $data['provider'];
            $gateway->username = $data['username'] ?? null;
            $gateway->api_key = $data['api_key'] ?? null;
            $gateway->sender_id = $data['sender_id'] ?? null;
            $gateway->is_active = $data['is_active'] ?? true;
            $gateway->is_default = $data['is_default'] ?? false;
            $gateway->label = $data['label'] ?? (ucfirst($data['provider']) . ' (' . $tenantId . ')');
            $gateway->save();
        } else {
            $gateway = TenantSmsGateway::create([
                'tenant_id' => $tenantId,
                'provider' => $data['provider'],
                'username' => $data['username'] ?? null,
                'api_key' => $data['api_key'] ?? null,
                'sender_id' => $data['sender_id'] ?? null,
                'is_active' => $data['is_active'] ?? true,
                'is_default' => $data['is_default'] ?? false,
                'label' => $data['label'] ?? (ucfirst($data['provider']) . ' (' . $tenantId . ')'),
            ]);
        }
    cache()->forget("tenant_sms_gateways_{$tenantId}");
    return redirect()->back()->with('success', 'SMS gateway settings updated successfully.');
    }

    public function json(Request $request)
    {
        $tenantId = tenant('id') ?? ($request->user() ? $request->user()->tenant_id : null);
        if (!$tenantId && app()->environment('local')) {
            $tenantId = \App\Models\Tenant::first()?->id;
        }
        if (!$tenantId) {
            return response()->json(['error' => 'No tenant context available'], 400);
        }
        $gateways = TenantSmsGateway::where('tenant_id', $tenantId)
            ->remember(60)
            ->get();
        return response()->json(['gateways' => $gateways]);
    }
}
