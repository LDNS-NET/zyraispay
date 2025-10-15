<?php

namespace App\Http\Controllers\Tenants;

use App\Http\Controllers\Controller;
use App\Models\Tenants\TenantSMSTemplate;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TenantSMSTemplateController extends Controller
{
    public function index()
    {
        $templates = TenantSMSTemplate::all();
        return Inertia::render('Tenants/SMS/Templates', [
            'templates' => $templates,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'required|string|max:1000',
        ]);
        $validated['created_by'] = auth()->id();
        TenantSMSTemplate::create($validated);
        return back()->with('success', 'Template created.');
    }

    public function update(Request $request, TenantSMSTemplate $template)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'required|string|max:1000',
        ]);
        $template->update($validated);
        return back()->with('success', 'Template updated.');
    }

    public function destroy(TenantSMSTemplate $template)
    {
        $template->delete();
        return back()->with('success', 'Template deleted.');
    }
}
