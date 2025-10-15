<?php

namespace App\Http\Controllers\Admin;
use App\Models\Lead;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


class LeadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return inertia('Admin/Leads/Index', [
        'leads' => Lead::latest()->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return inertia('Admin/Leads/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'isp_name' => 'required|string|unique:leads,isp_name',
            'email' => 'required|email|unique:leads,email',
            'phone' => 'required|string|unique:leads,phone',
        ]);

        Lead::create($validated);

        return back()->with('success', 'Lead created.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $lead = Lead::findOrFail($id);
        return inertia('Admin/Leads/Edit', compact('lead'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $lead = Lead::findOrFail($id);
        $validated = $request->validate([
            'isp_name'=> 'required|string|unique:leads,isp_name,'.$lead->id,
            'email'=> 'required|email|unique:leads,email,'.$lead->id,
            'phone'=> 'required|string|unique:leads,phone,'.$lead->id,
            ]);
            $lead->update($validated);
            return back()->with('success', 'Lead updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Lead::findOrFail($id)->delete();
        return back()->with('success', 'Lead deleted.');
    }
}
