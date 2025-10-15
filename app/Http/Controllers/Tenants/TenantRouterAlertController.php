<?php

namespace App\Http\Controllers\Tenants;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenants\TenantRouterAlert;
use Inertia\Inertia;

class TenantRouterAlertController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $alerts = TenantRouterAlert::with('router')->latest()->paginate(20);
        return Inertia::render('Tenants/RouterAlerts/Index', [
            'alerts' => $alerts,
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
        $alert = TenantRouterAlert::with('router')->findOrFail($id);
        return Inertia::render('Tenants/RouterAlerts/Show', [
            'alert' => $alert,
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
        $alert = TenantRouterAlert::findOrFail($id);
        $alert->delete();
        return back()->with('success', 'Alert deleted.');
    }
}
