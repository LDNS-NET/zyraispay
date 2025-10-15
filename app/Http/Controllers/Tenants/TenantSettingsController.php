<?php

namespace App\Http\Controllers\Tenants;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TenantSetting;
use Inertia\Inertia;

class TenantSettingsController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = $request->user()->tenant_id;
        $categories = [
            'general', 'notifications', 'billing', 'gateways', 'hotspot',
            'payout', 'payment_gateway', 'sms_gateway', 'whatsapp_gateway',
        ];
        $settings = TenantSetting::where('tenant_id', $tenantId)
            ->whereIn('category', $categories)
            ->get()
            ->keyBy('category')
            ->map(fn($s) => $s->settings);

        // Load gateways for main tabs
        $payment_gateways = \App\Models\TenantPaymentGateway::where('tenant_id', $tenantId)->get();
        $sms_gateways = \App\Models\TenantSmsGateway::where('tenant_id', $tenantId)->get();
        $whatsapp_gateways = \App\Models\TenantWhatsappGateway::where('tenant_id', $tenantId)->get();

        return Inertia::render('Tenants/Settings/Index', [
            'settings' => $settings,
            'payment_gateways' => $payment_gateways,
            'sms_gateways' => $sms_gateways,
            'whatsapp_gateways' => $whatsapp_gateways,
        ]);
    }
}
