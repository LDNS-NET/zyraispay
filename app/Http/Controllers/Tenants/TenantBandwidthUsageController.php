<?php

namespace App\Http\Controllers\Tenants;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenants\TenantBandwidthUsage;
use Inertia\Inertia;

class TenantBandwidthUsageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $usages = TenantBandwidthUsage::with('router')->latest()->paginate(20);
        return Inertia::render('Tenants/BandwidthUsage/Index', [
            'usages' => $usages,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $usage = TenantBandwidthUsage::with('router')->findOrFail($id);
        return Inertia::render('Tenants/BandwidthUsage/Show', [
            'usage' => $usage,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $usage = TenantBandwidthUsage::findOrFail($id);
        $usage->delete();
        return back()->with('success', 'Bandwidth usage record deleted.');
    }
}
