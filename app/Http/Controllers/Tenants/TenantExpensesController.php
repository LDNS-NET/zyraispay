<?php

namespace App\Http\Controllers\Tenants;
use App\Models\TenantExpenses;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


class TenantExpensesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $paginated = TenantExpenses::latest()->paginate(10);
        $allData = TenantExpenses::all()->map(function ($expense) {
            return [
                'id' => $expense->id,
                'description' => $expense->description,
                'amount' => $expense->amount,
                'incurred_on' => $expense->incurred_on,
                'category' => $expense->category,
            ];
        });
        return inertia('Tenants/Expenses/Index', [
            'expenses' => array_merge($paginated->toArray(), ['allData' => $allData]),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'description' => 'required|string',
            'amount' => 'required|numeric',
            'incurred_on' => 'required|date',
            'category' => 'nullable|string',
        ]);

        TenantExpenses::create($validated);

        return back()->with('success', 'Expense recorded.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $expense = TenantExpenses::findOrFail($id);

        $validated = $request->validate([
            'description' => 'required|string',
            'amount' => 'required|numeric',
            'incurred_on' => 'required|date',
            'category' => 'nullable|string',
        ]);

        $expense->update($validated);

        return back()->with('success', 'Expense updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $expense = TenantExpenses::findOrFail($id);
        $expense->delete();

        return back()->with('success', 'Expense deleted.');
    }

    /**
     * Bulk delete expenses.
     */
    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        if (!is_array($ids) || empty($ids)) {
            return back()->with('error', 'No expenses selected for deletion.');
        }

        TenantExpenses::whereIn('id', $ids)->delete();

        return back()->with('success', count($ids) . ' expenses deleted.');
    }
}