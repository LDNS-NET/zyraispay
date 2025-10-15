<?php

namespace App\Http\Controllers\Tenants; // Retaining your specified namespace

use App\Http\Controllers\Controller;
use App\Models\Voucher; // Ensure your Voucher model is correctly imported
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia; // Essential for Inertia responses
use Illuminate\Support\Facades\Log; // For robust error logging
use Illuminate\Support\Str;


class VoucherController extends Controller
{
    /**
     * Display a listing of the vouchers with filters, search, and pagination.
     * Passes 'vouchers', 'creating' state, and 'filters' to the Inertia component.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Inertia\Response
     */
    public function index(Request $request): \Inertia\Response
    {
        $query = Voucher::query()
            ->where('created_by', auth()->id()) // Scope to vouchers created by the current user
            ->latest(); // Order by latest created

        // Apply search filter on 'code' and 'name'
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        // Apply status filter if present in the request (assuming you might add this to your UI later)
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        // Paginate the results and append query string for pagination links
        $vouchers = $query->paginate(10)->withQueryString();

        return Inertia::render('Tenants/Vouchers/Index', [
            'vouchers' => $vouchers,
             'voucherCount' => Voucher::count(),
            'filters' => $request->only('search', 'status', 'page'), // Pass current filters back for persistence
            'creating' => $request->boolean('create'), // Control modal state via query param
            'editing' => $request->boolean('edit'), // NEW: Control editing modal state via query param
            'voucherToEdit' => $request->boolean('edit') && $request->has('voucher_id') ?
                                Voucher::find($request->query('voucher_id')) : null, // NEW: Load voucher if editing
            'flash' => [ // Pass flash messages for display
                'success' => session('success'),
                'error' => session('error'),
            ],
        ]);
    }

    /**
     * Show the form for creating a new voucher.
     * This method is typically used if you have a separate page for creation,
     * not just a modal on the index page.
     *
     * @return \Inertia\Response
     */
    public function create(): \Inertia\Response
    {   $packages = Package::all();
        return Inertia::render('Tenants/Vouchers/Create', [
            'packages'=>$packages,
        ]);  // Assumes a dedicated Create.vue component
    }

    /**
     * Store a newly created voucher in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
{
    $validated = $request->validate([
        'prefix' => ['nullable', 'string', 'max:4'],
        'length' => ['required', 'integer', 'min:6'],
        'quantity'=> ['required', 'integer','min:1','max:1000'],
        'package_id'=> ['required', 'exists:packages,id'],
    ]);

    $package = Package::findOrFail($request->package_id); // ✅ Fixed semicolon and var name

    $vouchers = [];

    for ($i = 0; $i < $request->quantity; $i++) {
        $code = strtoupper($request->prefix ?? '') . strtoupper(Str::random($request->length - strlen($request->prefix ?? '')));

        $vouchers[] = [
            'code' => $code,
            'package_id' => $package->id,
            'value' => $package->price,
            'expires_at' => now()->addDays($package->validity_days ?? 30),
            'created_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    try {
        Voucher::insert($vouchers); // ✅ Correct way to insert multiple vouchers

        return redirect()
            ->route('tenants.vouchers.index')
            ->with('success', "{$request->quantity} vouchers created successfully.");
    } catch (\Throwable $e) {
        Log::error("Failed to create vouchers: " . $e->getMessage(), [
            'trace' => $e->getTraceAsString(),
            'user_id' => auth()->id(),
        ]);

        return redirect()
            ->route('tenants.vouchers.index', ['create' => true])
            ->with('error', 'Failed to create vouchers. ' . $e->getMessage())
            ->withInput();
    }
}


    /**
     * Display the specified voucher.
     * This is typically an API endpoint or a dedicated show page.
     *
     * @param  \App\Models\Voucher  $voucher
     * @return \Illuminate\Http\JsonResponse|\Inertia\Response
     */
    public function show(Voucher $voucher)
    {
        $this->authorizeAccess($voucher); // Ensure authorization

        // You can choose to return JSON for an API, or an Inertia component for a dedicated page.
        // For a full CRUD application, a separate Inertia page is common.
        return Inertia::render('Tenants/Vouchers/Show', [ // Assumes Tenants/Vouchers/Show.vue exists
            'voucher' => $voucher,
        ]);
    }

    /**
     * Show the form for editing the specified voucher.
     *
     * @param  \App\Models\Voucher  $voucher
     * @return \Inertia\Response|\Illuminate\Http\RedirectResponse
     */
    public function edit(Voucher $voucher)
    {
        $this->authorizeAccess($voucher); // Ensure authorization

        // This method could redirect to the index page with 'edit' and 'voucher_id' query parameters
        // to open the modal, or render a separate edit page.
        // Using redirect to index for modal approach, similar to 'create'.
        return redirect()
            ->route('tenants.vouchers.index', ['edit' => true, 'voucher_id' => $voucher->id])
            ->with('voucherToEdit', $voucher); // Pass the voucher directly through session for convenience
    }


    /**
     * Update the specified voucher in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Voucher  $voucher
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Voucher $voucher): \Illuminate\Http\RedirectResponse
    {
        $this->authorizeAccess($voucher); // Ensure authorization

        $validated = $request->validate([
            'code' => ['sometimes', 'string', 'max:255', Rule::unique('vouchers', 'code')->ignore($voucher->id)],
            'name' => ['sometimes', 'string', 'max:255'],
            'type' => ['sometimes', Rule::in(['percentage', 'fixed'])],
            'value' => ['sometimes', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'expires_at' => ['nullable', 'date'],
            'status' => ['sometimes', Rule::in(['active', 'used', 'expired', 'revoked'])], // Allow updating status
            'is_active' => ['sometimes', 'boolean'], // Allow updating active status
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $voucher->update($validated);
            return redirect()
                ->route('tenants.vouchers.index') // Redirect to index after successful update
                ->with('success', 'Voucher updated successfully.');
        } catch (\Throwable $e) {
            Log::error("Failed to update voucher: " . $e->getMessage(), ['trace' => $e->getTraceAsString(), 'voucher_id' => $voucher->id]);
            return redirect()
                ->route('tenants.vouchers.index', ['edit' => true, 'voucher_id' => $voucher->id]) // Keep modal open on error
                ->with('error', 'Failed to update voucher. ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified voucher from storage.
     *
     * @param  \App\Models\Voucher  $voucher
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Voucher $voucher): \Illuminate\Http\RedirectResponse
    {
        $this->authorizeAccess($voucher); // Ensure authorization

        try {
            $voucher->delete();
            return redirect()
                ->route('tenants.vouchers.index')
                ->with('success', 'Voucher deleted successfully.');
        } catch (\Throwable $e) {
            Log::error("Failed to delete voucher: " . $e->getMessage(), ['trace' => $e->getTraceAsString(), 'voucher_id' => $voucher->id]);
            return redirect()
                ->route('tenants.vouchers.index')
                ->with('error', 'Failed to delete voucher. ' . $e->getMessage());
        }
    }

    /**
     * Send a voucher to a specific user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Voucher  $voucher
     * @return \Illuminate\Http\RedirectResponse
     */
    public function send(Request $request, Voucher $voucher): \Illuminate\Http\RedirectResponse
    {
        $this->authorizeAccess($voucher); // Ensure authorization

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'message' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $voucher->update([
                'sent_to' => $validated['user_id'],
                'sent_at' => now(), // Set sent timestamp
            ]);

            // You would typically dispatch a notification or an event here
            // e.g., Notification::send(User::find($validated['user_id']), new VoucherSentNotification($voucher));

            return redirect()
                ->back() // Redirect back to wherever the send action was triggered
                ->with('success', 'Voucher sent successfully.');
        } catch (\Throwable $e) {
            Log::error("Failed to send voucher: " . $e->getMessage(), ['trace' => $e->getTraceAsString(), 'voucher_id' => $voucher->id]);
            return redirect()
                ->back()
                ->with('error', 'Failed to send voucher. ' . $e->getMessage());
        }
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:vouchers,id',
        ]);

        Voucher::whereIn('id', $request->ids)->delete();

        return redirect()->back()->with('success', 'Selected vouchers deleted successfully.');
    }




    /**
     * Authorize access to a voucher based on 'created_by' field.
     * For more robust authorization, consider using Laravel Policies.
     *
     * @param  \App\Models\Voucher  $voucher
     * @return void
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     */
    protected function authorizeAccess(Voucher $voucher): void
    {
        if ($voucher->created_by !== auth()->id()) {
            abort(403, 'Unauthorized. You do not own this voucher.');
        }
    }
}
