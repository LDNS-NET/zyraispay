<?php

namespace App\Http\Controllers\Tenants;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TenantHotspotSetting;
use Inertia\Inertia;

class TenantHotspotSettingsController extends Controller
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
        $setting = TenantHotspotSetting::where('tenant_id', $tenantId)
            ->remember(60)
            ->first();
        return Inertia::render('Tenants/Settings/Hotspot', [
            'settings' => $setting,
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
            'portal_template' => 'required|string',
            'logo_url' => 'nullable|url',
            'user_prefix' => 'nullable|string|max:30',
            'prune_inactive_days' => 'nullable|integer|min:1',
            'description' => 'nullable|string',
            'allowed_networks' => 'nullable|array',
            'default_package_id' => 'nullable|integer',
            'captive_portal_url' => 'nullable|url',
            'advanced_options' => 'nullable|array',
        ]);
        $setting = TenantHotspotSetting::updateOrCreate(
            ['tenant_id' => $tenantId],
            array_merge($data, [
                'created_by' => auth()->id(),
                'last_updated_by' => auth()->id(),
            ])
        );
        cache()->forget("tenant_hotspot_setting_{$tenantId}");
        return redirect()->back()->with('success', 'Hotspot settings updated.');
    }
}
