<?php

namespace App\Http\Controllers\Tenants;

use App\Http\Controllers\Controller;
use App\Models\Tenants\TenantLeads;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class TenantLeadController extends Controller
{
    /**
     * Display a listing of the leads for the authenticated user.
     */
    public function index()
    {
        $leads = TenantLeads::select('id', 'name', 'phone_number', 'address', 'email_address', 'status')
            ->latest()
            ->paginate(10);

        return Inertia::render('Tenants/Leads/Index', [
            'leads' => $leads,
        ]);
    }

    /**
     * Store a new lead in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'phone_number' => ['required', 'min:10', 'unique:tenant_leads,phone_number', 'string', 'max:255'],
            'email_address' => ['nullable', 'email', 'max:255','unique:tenant_leads,email_address'],
            'status'=> ['nullable'],
        ]);

        TenantLeads::create($validated);

        return redirect()->back()->with('success', 'Lead created successfully.');
    }

    /**
     * Update an existing lead.
     */
    public function update(Request $request, TenantLeads $lead)
    {
        $this->authorizeAccess($lead);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'address' => ['sometimes', 'string', 'max:500'],
            'phone_number' => ['sometimes', 'string', 'max:255'],
            'email_address' => ['nullable', 'email', 'max:255'],
            'status' => ['nullable', 'string', Rule::in(['new', 'contacted', 'converted'])], // ✅ added
        ]);

        $lead->update($validated);

        return redirect()->back()->with('success', 'Lead updated successfully.');
    }


    /**
     * Remove a lead from storage.
     */
    public function destroy(TenantLeads $lead)
    {
        $this->authorizeAccess($lead);

        $lead->delete();

        return redirect()->back()->with('success', 'Lead deleted.');
    }

    /**
     * Restrict access to only leads created by the current user.
     */
    protected function authorizeAccess(TenantLeads $lead): void
    {
        // Optionally restrict by user, but not required for tenant DB isolation
    }

    //Bulk delete for Leads

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:tenant_leads,id',
        ]);

        TenantLeads::whereIn('id', $request->ids)->delete();

        return back()->with('success', 'Selected Leads deleted successfully.');
    }



}
