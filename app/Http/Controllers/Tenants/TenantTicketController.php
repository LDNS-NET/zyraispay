<?php

namespace App\Http\Controllers\Tenants;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenants\TenantTickets;
use App\Models\Tenants\NetworkUser;
use App\Models\Tenants\TenantLeads;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Validate\Rule;

class TenantTicketController extends Controller
{
    /**
     * Display a listing of tickets by status (open or closed).
     */
    public function index(Request $request)
    {
        $status = $request->get('status', 'open');

        $tickets = TenantTickets::with('client')
            ->where('status', $status)
            ->latest()
            ->paginate(10)
            ->withQueryString();

            $users = NetworkUser::select('id', 'full_name')->get(); // should be stored in tenant DB
            $leads = TenantLeads::select('id', 'name')->get(); // also stored per tenant


        return Inertia::render('Tenants/Tickets/Index', [
            'tickets' => $tickets,
            'statusFilter' => $status,
            'users' => $users,
            'leads' => $leads,
        ]);
    }

    /**
     * Store a new ticket.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_type' => 'required|in:user,lead',
            'client_id' => 'required|integer',
            'priority' => 'required|in:low,medium,high',
            'status' => 'required|in:open,closed',
            'description' => 'required|string|max:2000',
        ]);

        $validated['ticket_number'] = 'TCK-' . strtoupper(Str::random(8));
        $validated['created_by'] = auth()->id(); // Tenant Specific

        // Check for existing open ticket for this client
        $existing = TenantTickets::where('client_type', $validated['client_type'])
            ->where('client_id', $validated['client_id'])
            ->where('status', 'open')
            ->first();
        if ($existing) {
            return redirect()->back()->withErrors(['client_id' => 'User has open ticket']);
        }

        TenantTickets::create($validated);


        return redirect()->route('tenants.tickets.index')->with('success', 'Ticket created successfully.');
    }

    /**
     * Update an existing ticket.
     */
    public function update(Request $request, TenantTickets $ticket)
    {
        $validated = $request->validate([
            'client_type' => 'required|in:user,lead',
            'client_id' => 'required|integer',
            'priority' => 'required|in:low,medium,high',
            'status' => 'required|in:open,closed',
            'description' => 'required|string|max:2000',
        ]);

        $ticket->update($validated);

        return redirect()->route('tenants.tickets.index')->with('success', 'Ticket updated successfully.');
    }

    /**
     * Remove the specified ticket.
     */
    public function destroy(TenantTickets $ticket)
    {
        $ticket->delete();

        return redirect()->route('tenants.tickets.index')->with('success', 'Ticket deleted successfully.');
    }

    /**
     * Optional: Get client options for dropdowns.
     */
    public function clients()
    {
        return response()->json([
            'users' => NetworkUser::select('id', 'full_name')->get(), // tenant scoped
            'leads' => TenantLeads::select('id', 'name')->get(),      // tenant scoped
        ]);
    }



    //bulk delete

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:tenant_tickets,id',
        ]);

        TenantTickets::whereIn('id', $request->ids)->delete();

        return back()->with('success', 'Selected Tickets deleted successfully.');
    }

    //ticket resolution
    public function resolve(Request $request, TenantTickets $ticket)
    {
        $validated = $request->validate([
            'resolution_note' => 'required|string|min:5',
        ]);

        $ticket->update([
            'status' => 'closed',
            'resolution_note' => $validated['resolution_note'],
            'closed_at' => now(),
        ]);

        return back()->with('success', 'Ticket resolved');
    }

}
