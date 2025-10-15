<?php

namespace App\Http\Controllers\Tenants;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TenantSetting;
use Inertia\Inertia;

class TenantPayoutSettingsController extends Controller
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
        $setting = TenantSetting::where('tenant_id', $tenantId)->where('category', 'payout')->first();
        return Inertia::render('Tenants/Settings/Payout', [
            'settings' => $setting ? $setting->settings : null,
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
            'payout_method' => 'required|in:phone,till,bank',
            'phone_number' => 'nullable|string',
            'till_number' => 'nullable|string',
            'bank_account' => 'nullable|string',
            'bank_code' => 'nullable|string',
            'account_name' => 'nullable|string',
        ]);
        $setting = TenantSetting::updateOrCreate(
            ['tenant_id' => $tenantId, 'category' => 'payout'],
            [
                'settings' => $data,
                'created_by' => auth()->id(),
            ]
        );
        return redirect()->back()->with('success', 'Payout settings updated.');
    }
}
