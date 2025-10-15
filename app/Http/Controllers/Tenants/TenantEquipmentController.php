<?php

namespace App\Http\Controllers\Tenants;

use App\Http\Controllers\Controller;
use App\Models\Tenants\TenantEquipment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class TenantEquipmentController extends Controller
{
    public function index()
    {
        $equipment = TenantEquipment::latest()->paginate(10);
        $totalPrice = (float) TenantEquipment::sum('total_price');

        return Inertia::render('Tenants/Equipment/Index', [
            'equipment' => $equipment,
            'totalPrice' => $totalPrice,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'serial_number' => 'required|string|max:255|unique:tenant_equipments',
            'location' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'total_price' => 'nullable|numeric|min:0',
            'assigned_to' => 'nullable|string|max:255',
        ]);

        TenantEquipment::create($validated);

        return redirect()->back()->with('success', 'Equipment added successfully.');
    }

    protected function authorizeAccess(TenantEquipment $equipment): void
    {
        // Optionally restrict by user, but not required for tenant DB isolation
    }

    public function update(Request $request, TenantEquipment $equipment)
    {
        $this->authorizeAccess($equipment);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'serial_number' => 'required|string|max:255|unique:tenant_equipments,serial_number,' . $equipment->id,
            'location' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'total_price' => 'nullable|numeric|min:0',
            'assigned_to' => 'nullable|string|max:255',
        ]);

        $equipment->update($validated);

        return redirect()->back()->with('success', 'Equipment updated.');
    }

    public function destroy(TenantEquipment $equipment)
    {
        $equipment->delete();

        return back()->with('success', 'Equipment deleted.');
    }

     public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:tenant_equipments,id',
        ]);

        TenantEquipment::whereIn('id', $request->ids)->delete();

        return back()->with('success', 'Selected Equipment deleted successfully.');
    }
}
