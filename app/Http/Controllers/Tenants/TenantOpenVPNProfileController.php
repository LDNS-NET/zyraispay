<?php

namespace App\Http\Controllers\Tenants;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenants\TenantOpenVPNProfile;
use Inertia\Inertia;

class TenantOpenVPNProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $profiles = TenantOpenVPNProfile::all();
        return Inertia::render('Tenants/OpenVPNProfiles/Index', [
            'profiles' => $profiles,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Tenants/OpenVPNProfiles/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'config_path' => 'required|string',
            'client_cert_path' => 'required|string',
            'client_key_path' => 'required|string',
            'ca_cert_path' => 'required|string',
            'status' => 'required|in:active,inactive',
        ]);
        TenantOpenVPNProfile::create($data);
        return redirect()->route('tenants.openvpn-profiles.index')->with('success', 'OpenVPN profile created.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $profile = TenantOpenVPNProfile::findOrFail($id);
        return Inertia::render('Tenants/OpenVPNProfiles/Show', [
            'profile' => $profile,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $profile = TenantOpenVPNProfile::findOrFail($id);
        return Inertia::render('Tenants/OpenVPNProfiles/Edit', [
            'profile' => $profile,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $profile = TenantOpenVPNProfile::findOrFail($id);
        $data = $request->validate([
            'config_path' => 'required|string',
            'client_cert_path' => 'required|string',
            'client_key_path' => 'required|string',
            'ca_cert_path' => 'required|string',
            'status' => 'required|in:active,inactive',
        ]);
        $profile->update($data);
        return redirect()->route('tenants.openvpn-profiles.index')->with('success', 'OpenVPN profile updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $profile = TenantOpenVPNProfile::findOrFail($id);
        $profile->delete();
        return redirect()->route('tenants.openvpn-profiles.index')->with('success', 'OpenVPN profile deleted.');
    }
}
