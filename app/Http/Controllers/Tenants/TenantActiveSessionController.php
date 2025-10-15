<?php

namespace App\Http\Controllers\Tenants;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenants\TenantActiveSession;
use Inertia\Inertia;

class TenantActiveSessionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sessions = TenantActiveSession::with(['router', 'user'])->latest()->paginate(20);
        return Inertia::render('Tenants/ActiveSessions/Index', [
            'sessions' => $sessions,
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
        $session = TenantActiveSession::with(['router', 'user'])->findOrFail($id);
        return Inertia::render('Tenants/ActiveSessions/Show', [
            'session' => $session,
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
        $session = TenantActiveSession::findOrFail($id);
        $session->delete();
        return back()->with('success', 'Session deleted.');
    }
}
