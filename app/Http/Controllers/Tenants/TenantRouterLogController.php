<?php

namespace App\Http\Controllers\Tenants;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenants\TenantRouterLog;
use Inertia\Inertia;

class TenantRouterLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $logs = TenantRouterLog::with('router')->latest()->paginate(20);
        return Inertia::render('Tenants/RouterLogs/Index', [
            'logs' => $logs,
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
        $log = TenantRouterLog::with('router')->findOrFail($id);
        return Inertia::render('Tenants/RouterLogs/Show', [
            'log' => $log,
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
        $log = TenantRouterLog::findOrFail($id);
        $log->delete();
        return back()->with('success', 'Log deleted.');
    }
}
