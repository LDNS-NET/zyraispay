<?php

namespace App\Http\Controllers\Tenants;

use App\Http\Controllers\Controller;
use App\Models\Tenants\TenantInvoice;
use Illuminate\Http\Request;
use App\Models\Tenants\NetworkUser;
class TenantInvoiceController extends Controller
{
    public function index()
    {
        $paginated = TenantInvoice::latest()->paginate(10);
        $allData = TenantInvoice::all()->map(function ($invoice) {
            return [
                'id' => $invoice->id,
                'user' => $invoice->user,
                'amount' => $invoice->amount,
                'package' => $invoice->package,
                'issued_on' => $invoice->issued_on,
                'due_on' => $invoice->due_on,
            ];
        });
    $users = NetworkUser::with('package')->get(['id', 'full_name', 'package_id'])->map(function($user) {
        return [
            'id' => $user->id,
            'full_name' => $user->full_name,
            'package_id' => $user->package_id,
            'package' => $user->package ? [
                'id' => $user->package->id ?? null,
                'type' => $user->package->type ?? ($user->package->name ?? ''),
                'price' => $user->package->price ?? $user->package->amount ?? '',
            ] : null,
        ];
    })->toArray();
    return inertia('Tenants/Invoices/Index', [
        'invoices' => array_merge($paginated->toArray(), ['allData' => $allData]),
        'networkUsers' => $users,
        'can' => [
            'create_invoice' => true, // Set to true or use your permission logic
        ],
    ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:network_users,id',
            'amount' => 'required|numeric',
            'package' => 'required|string',
            'issued_on' => 'required|date',
            'due_on' => 'required|date|after_or_equal:issued_on',
        ]);

        TenantInvoice::create($validated);

        return back()->with('success', 'Invoice created.');
    }

    public function update(Request $request, string $id)
    {
        $invoice = TenantInvoice::findOrFail($id);

        $validated = $request->validate([
            'user_id' => 'required|exists:network_users,id',
            'amount' => 'required|numeric',
            'package' => 'required|string',
            'issued_on' => 'required|date',
            'due_on' => 'required|date|after_or_equal:issued_on',
        ]);

        $invoice->update($validated);

        return back()->with('success', 'Invoice updated.');
    }

    public function destroy(string $id)
    {
        $invoice = TenantInvoice::findOrFail($id);
        $invoice->delete();

        return back()->with('success', 'Invoice deleted.');
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        TenantInvoice::whereIn('id', $ids)->delete();

        return back()->with('success', 'Selected invoices deleted.');
    }
}
