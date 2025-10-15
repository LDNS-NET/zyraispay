<?php

namespace App\Http\Controllers\Tenants;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenants\TenantRadiusCredential;
use Inertia\Inertia;

class TenantRadiusCredentialController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $credentials = TenantRadiusCredential::all();
        return Inertia::render('Tenants/RadiusCredentials/Index', [
            'credentials' => $credentials,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Tenants/RadiusCredentials/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'secret' => 'required|string',
            'ip_range' => 'required|string',
            'nas_identifier' => 'required|string',
        ]);
        TenantRadiusCredential::create($data);
        return redirect()->route('tenants.radius-credentials.index')->with('success', 'Radius credential created.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $credential = TenantRadiusCredential::findOrFail($id);
        return Inertia::render('Tenants/RadiusCredentials/Show', [
            'credential' => $credential,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $credential = TenantRadiusCredential::findOrFail($id);
        return Inertia::render('Tenants/RadiusCredentials/Edit', [
            'credential' => $credential,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $credential = TenantRadiusCredential::findOrFail($id);
        $data = $request->validate([
            'secret' => 'required|string',
            'ip_range' => 'required|string',
            'nas_identifier' => 'required|string',
        ]);
        $credential->update($data);
        return redirect()->route('tenants.radius-credentials.index')->with('success', 'Radius credential updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $credential = TenantRadiusCredential::findOrFail($id);
        $credential->delete();
        return redirect()->route('tenants.radius-credentials.index')->with('success', 'Radius credential deleted.');
    }
}
