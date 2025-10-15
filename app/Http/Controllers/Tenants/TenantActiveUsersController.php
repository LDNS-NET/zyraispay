<?php

namespace App\Http\Controllers\Tenants;

use App\Http\Controllers\Controller;
use App\Models\Tenants\TenantMikrotik;
use App\Services\MikrotikService;
use Illuminate\Http\Request;

class TenantActiveUsersController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = tenant('id'); // or auth()->user()->tenant_id
        $mikrotikModel = TenantMikrotik::where('tenant_id', $tenantId)->firstOrFail();

        $mikrotik = new MikrotikService($mikrotikModel);
        $activeUsers = $mikrotik->getOnlineUsers();

        return inertia('Tenants/Activeusers/Index', [
            'activeUsers' => $activeUsers,
        ]);
    }
}
