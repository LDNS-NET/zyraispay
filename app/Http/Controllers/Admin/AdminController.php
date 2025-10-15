<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Stancl\Tenancy\Database\Models\Tenant;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function dashboard()
    {
        $tenants = Tenant::all()->map(function ($tenant) {
            $data = is_array($tenant->data) ? $tenant->data : (array) $tenant->data;

            return [
                'id' => $tenant->id,
                'name' => $data['name'] ?? 'N/A',
                'email' => $data['email'] ?? 'N/A',
                'created_at' => $tenant->created_at?->toDateTimeString() ?? 'N/A',
                'payments_count' => $data['payments_count'] ?? 0,
                'mikrotiks_count' => $data['mikrotiks_count'] ?? 0,
                'users_count' => $data['users_count'] ?? 0,
            ];
        });

        return Inertia::render('Admin/Users/Index', [
            'users' => $tenants,
            'flash' => [
                'success' => Session::get('success'),
            ],
        ]);
    }

    public function deleteUser($tenantId, $userId)
    {
        $tenant = Tenant::findOrFail($tenantId);
        tenancy()->initialize($tenant);

        DB::table('users')
            ->where('id', $userId)
            ->where('is_super_admin', false)
            ->delete();

        tenancy()->end();

        return redirect()->back()->with('success', 'User deleted successfully.');
    }
}
