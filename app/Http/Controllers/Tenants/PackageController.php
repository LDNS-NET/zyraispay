<?php

namespace App\Http\Controllers\Tenants;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class PackageController extends Controller
{
    /**
     * Display a listing of the packages.
     */
    public function index()
    {
        $packages = Package::latest()->paginate(10)->withQueryString();

        return Inertia::render('Tenants/Packages/index', [
            'packages' => $packages->items(),
            'pagination' => $packages->toArray(),

        ]);
    }

    /**
     * Store a newly created package in storage.
     */
    public function store(Request $request)
    {
        $validated = $this->validatePackage($request);

        $validated['created_by'] = auth()->id();

        Package::create($validated);

        return redirect()->route('tenants.packages.index')
            ->with('success', 'Package created successfully.');
    }



    /**
     * Update the specified package in storage.
     */
    public function update(Request $request, Package $package)
    {
        $validated = $this->validatePackage($request, $package->id);

        $package->update($validated);

        return redirect()->route('tenants.packages.index')
            ->with('success', 'Package updated successfully.');
    }




    //bulk delete action for package lists
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:packages,id',
        ]);

        Package::whereIn('id', $request->ids)->delete();

        return back()->with('success', 'Selected packages deleted successfully.');
    }

    /**
     * Remove the specified package from storage.
     */
    public function destroy(Package $package)
    {
        $package->delete();

        return redirect()->route('tenants.packages.index')
            ->with('success', 'Package deleted successfully.');
    }

    /**
     * Shared validation for store and update.
     */
    protected function validatePackage(Request $request, $id = null)
    {
        $rules = [
            'name' => ['required', 'string', 'max:255', Rule::unique('packages')->ignore($id)],
            'type' => ['required', 'in:hotspot,pppoe,static'],
            'price' => ['required', 'numeric', 'min:0'],
            'duration_value' => ['required', 'integer', 'min:1'],
            'duration_unit' => ['required', 'in:minutes,hours,days,weeks,months'],
            'upload_speed' => ['required', 'numeric', 'min:1'],
            'download_speed' => ['required', 'numeric', 'min:1'],
            'burst_limit' => ['nullable', 'numeric', 'min:0'],
            'device_limit' => ['nullable', 'integer', 'min:1'],
        ];

        // Hotspot-specific rule
        if ($request->input('type') === 'hotspot') {
            $rules['device_limit'] = ['required', 'integer', 'min:1'];
        }

        return $request->validate($rules);
    }
}
