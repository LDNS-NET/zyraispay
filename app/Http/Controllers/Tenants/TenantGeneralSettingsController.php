<?php

namespace App\Http\Controllers\Tenants;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TenantGeneralSetting;
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\Tenant;

class TenantGeneralSettingsController extends Controller
{
    /**
     * Show the general settings form.
     */
    public function edit(Request $request)
    {
        $tenantId = tenant('id') ?? ($request->user() ? $request->user()->tenant_id : null);
        if (!$tenantId && app()->environment('local')) {
            $tenantId = \App\Models\Tenant::first()?->id;
        }
        if (!$tenantId) {
            abort(400, 'No tenant context available');
        }

        $setting = TenantGeneralSetting::where('tenant_id', $tenantId)
            ->remember(60)
            ->first();
        $tenant = Tenant::find($tenantId);
        $settings = $setting ? $setting->toArray() : [];
        if (empty($settings['business_name']) && $tenant) {
            $settings['business_name'] = $tenant->business_name;
        }
        if (empty($settings['logo']) && !empty($tenant->logo)) {
            $settings['logo'] = $tenant->logo;
        }
        return Inertia::render('Tenants/Settings/General', [
            'settings' => $settings,
        ]);
    }

    /**
     * Update the general settings.
     */
    public function update(Request $request)
    {
        $tenantId = tenant('id') ?? ($request->user() ? $request->user()->tenant_id : null);
        if (!$tenantId && app()->environment('local')) {
            $tenantId = \App\Models\Tenant::first()?->id;
        }
        if (!$tenantId) {
            abort(400, 'No tenant context available');
        }

        $validator = Validator::make($request->all(), [
            // Business Information
            'business_type' => 'required|in:isp,wisp,telecom,other',
            // Contact Information
            'support_email' => 'nullable|email|max:255',
            'support_phone' => 'nullable|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            // Address Information
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            // Online Presence
            'website' => 'nullable|url|max:255',
            'facebook' => 'nullable|url|max:255',
            'twitter' => 'nullable|url|max:255',
            'instagram' => 'nullable|url|max:255',
            // Business Hours & Preferences
            'business_hours' => 'nullable|string|max:500',
            'timezone' => 'required|string|max:50',
            'currency' => 'required|string|max:10',
            'language' => 'required|string|max:10',
            // Branding
            'logo' => 'nullable|file|image|max:2048',
            'theme' => 'nullable|in:light,dark,system',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');
            $logoPath = $logo->store('public/logos');
            $data['logo'] = Storage::url($logoPath);
        } elseif ($request->get('remove_logo')) {
            $data['logo'] = null;
        }

        // Update or create the general settings
        TenantGeneralSetting::updateOrCreate(
            [
                'tenant_id' => $tenantId,
            ],
            array_merge($data, [
                'created_by' => auth()->id(),
                'last_updated_by' => auth()->id(),
            ])
        );
        cache()->forget("tenant_general_setting_{$tenantId}");

        // Optionally update business_name/logo in Tenant model for global use
        $tenant = Tenant::find($tenantId);
        if ($tenant) {
            if (!empty($data['business_name'])) {
                $tenant->business_name = $data['business_name'];
            }
            if (isset($data['logo'])) {
                $tenant->logo = $data['logo'];
            }
            $tenant->save();
        }

        return redirect()->back()->with('success', 'General settings updated successfully.');
    }
}
