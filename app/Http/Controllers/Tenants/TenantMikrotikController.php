<?php

namespace App\Http\Controllers\Tenants;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenants\TenantMikrotik;
use App\Models\Tenants\TenantOpenVPNProfile;
use App\Models\Tenants\TenantRouterLog;
use App\Models\Tenants\TenantBandwidthUsage;
use App\Models\Tenants\TenantActiveSession;
use App\Models\Tenants\TenantRouterAlert;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use App\Services\MikrotikService;
use App\Services\MikrotikScriptGenerator;
use Illuminate\Support\Str;

class TenantMikrotikController extends Controller
{
    public function index()
    {
        $routers = TenantMikrotik::with(['openvpnProfile', 'logs', 'bandwidthUsage', 'alerts'])
            ->orderByDesc('last_seen_at')
            ->get();
        return Inertia::render('Tenants/Mikrotiks/Index', [
            'routers' => $routers,
        ]);
    }

    public function show($id)
    {
        $router = TenantMikrotik::with(['openvpnProfile', 'logs', 'bandwidthUsage', 'activeSessions', 'alerts'])
            ->findOrFail($id);
        return Inertia::render('Tenants/Mikrotiks/Show', [
            'router' => $router,
        ]);
    }

    private function pingRouterInternal($ip)
    {
        $os = strtoupper(substr(PHP_OS, 0, 3));
        $success = false;
        for ($i = 0; $i < 8; $i++) {
            $pingCmd = $os === 'WIN' ? "ping -n 1 -w 400 $ip" : "ping -c 1 -W 1 $ip";
            exec($pingCmd, $output, $result);
            if ($result === 0) {
                $success = true;
                break;
            }
            // Sleep 0.4 seconds between attempts (except after last)
            if ($i < 7) usleep(400000);
        }
        return $success;
    }

    public function store(Request $request, MikrotikScriptGenerator $scriptGenerator)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'router_username' => 'required|string|max:255',
            'router_password' => 'required|string|min:1',
            'notes' => 'nullable|string',
        ]);

        // Create router record (no IP required at this stage)
        $router = TenantMikrotik::create([
            'name' => $data['name'],
            'router_username' => $data['router_username'],
            'router_password' => $data['router_password'],
            'notes' => $data['notes'] ?? null,
            'status' => 'pending',
            'sync_token' => Str::random(40),
        ]);

        // Generate the advanced onboarding script (firewall, walled garden, profiles, etc. included)
        $script = $scriptGenerator->generate([
            'name' => $data['name'],
            'username' => $data['router_username'],
            'password' => $data['router_password'],
            'router_id' => $router->id,
            'sync_token' => $router->sync_token,
            'ca_url' => $router->openvpnProfile && $router->openvpnProfile->ca_cert_path ? route('tenants.mikrotiks.downloadCACert', ['mikrotik' => $router->id]) : null,
        ]);

        // Show the script to the user for download/copy before proceeding
        return Inertia::render('Tenants/Mikrotiks/SetupScript', [
            'router' => $router,
            'script' => $script,
        ]);
    }

    public function update(Request $request, $id)
    {
        $router = TenantMikrotik::findOrFail($id);
        $data = $request->validate([
            'name' => 'required|string',
            'ip_address' => 'required|ip',
            'api_port' => 'required|integer',
            'ssh_port' => 'required|integer',
            'openvpn_profile_id' => 'nullable|integer',
            'router_username' => 'required|string',
            'router_password' => 'nullable|string',
            'connection_type' => 'required|in:api,ssh,ovpn',
            'notes' => 'nullable|string',
        ]);
        if (empty($data['router_password'])) unset($data['router_password']);
        $router->update($data);
        // Use MikrotikService to test connection after update
        $mikrotikService = new \App\Services\MikrotikService(
            $router->ip_address,
            $router->router_username,
            $router->router_password,
            $router->api_port
        );
        $resources = $mikrotikService->testConnection();
        $isOnline = $resources !== false;
        $router->status = $isOnline ? 'online' : 'offline';
        if ($isOnline) {
            $router->last_seen_at = now();
        }
        $router->save();
        $router->logs()->create([
            'action' => 'update',
            'message' => $isOnline ? 'Router is online after update.' : 'Router is offline after update.',
            'status' => $isOnline ? 'success' : 'failed',
        ]);
        return redirect()->route('tenants.mikrotiks.index')->with('success', 'Router updated!');
    }

    public function destroy($id)
    {
        $router = TenantMikrotik::findOrFail($id);
        $router->delete();
        return redirect()->route('tenants.mikrotiks.index')->with('success', 'Router deleted!');
    }

    public function testConnection($id)
    {
        $router = TenantMikrotik::findOrFail($id);
        // Validate required fields
        $required = ['ip_address', 'api_port', 'ssh_port', 'router_username', 'router_password', 'connection_type'];
        foreach ($required as $field) {
            if (empty($router->$field)) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot test connection: missing $field. Please complete router details first.",
                ], 422);
            }
        }
        $mikrotikService = new \App\Services\MikrotikService(
            $router->ip_address,
            $router->router_username,
            $router->router_password,
            $router->api_port
        );
        $resources = $mikrotikService->testConnection();
        $isOnline = $resources !== false;
        $router->status = $isOnline ? 'online' : 'offline';
        if ($isOnline) {
            $router->last_seen_at = now();
        }
        $router->save();
        $router->logs()->create([
            'action' => 'test_connection',
            'message' => $isOnline ? 'Router is online.' : 'Router is offline.',
            'status' => $isOnline ? 'success' : 'failed',
            'response_data' => $resources,
            'execution_time' => 0,
        ]);
        return response()->json([
            'success' => $isOnline,
            'message' => $isOnline ? 'Router is online.' : 'Router is offline.',
            'router_info' => $resources,
            'status' => $router->status,
            'last_seen_at' => $router->last_seen_at,
        ]);
    }

    public function downloadSetupScript($id, MikrotikScriptGenerator $scriptGenerator)
    {
        $router = TenantMikrotik::findOrFail($id);
        $script = $scriptGenerator->generate([
            'name' => $router->name,
            'username' => $router->router_username,
            'password' => $router->router_password,
            'router_id' => $router->id,
            'ca_url' => $router->openvpnProfile && $router->openvpnProfile->ca_cert_path ? route('tenants.mikrotiks.downloadCACert', ['mikrotik' => $router->id]) : null,
        ]);
        $router->logs()->create([
            'action' => 'download_script',
            'message' => 'Setup script downloaded',
            'status' => 'success',
        ]);
        return response($script)
            ->header('Content-Type', 'text/plain')
            ->header('Content-Disposition', 'attachment; filename=setup_router_' . $router->id . '.rsc');
    }

    public function downloadRadiusScript($id, MikrotikScriptGenerator $scriptGenerator)
    {
        $router = TenantMikrotik::findOrFail($id);
        $script = $scriptGenerator->generate([
            'name' => $router->name,
            'username' => $router->router_username,
            'password' => $router->router_password,
            'router_id' => $router->id,
            'ca_url' => $router->openvpnProfile && $router->openvpnProfile->ca_cert_path ? route('tenants.mikrotiks.downloadCACert', ['mikrotik' => $router->id]) : null,
        ]);
        $router->logs()->create([
            'action' => 'download_radius_script',
            'message' => 'Radius configuration script downloaded',
            'status' => 'success',
        ]);
        return response($script)
            ->header('Content-Type', 'text/plain')
            ->header('Content-Disposition', 'attachment; filename=radius_config_' . $router->id . '.rsc');
    }

    public function remoteManagement($id)
    {
        $router = TenantMikrotik::findOrFail($id);
        // Validate required fields
        $required = ['ip_address', 'api_port', 'ssh_port'];
        foreach ($required as $field) {
            if (empty($router->$field)) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot provide remote management links: missing $field. Please complete router details first.",
                ], 422);
            }
        }
        $links = [
            'winbox' => 'winbox://'.$router->ip_address.':'.$router->api_port,
            'ssh' => 'ssh://'.$router->ip_address.':'.$router->ssh_port,
            'api' => 'http://'.$router->ip_address.':'.$router->api_port,
        ];
        return response()->json($links);
    }

    public function pingRouter($id)
    {
        $router = TenantMikrotik::findOrFail($id);
        // Validate required fields
        if (empty($router->ip_address)) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot ping: missing IP address. Please complete router details first.',
            ], 422);
        }
        $mikrotikService = new \App\Services\MikrotikService(
            $router->ip_address,
            $router->router_username,
            $router->router_password,
            $router->api_port
        );
        $resources = $mikrotikService->testConnection();
        $isOnline = $resources !== false;
        $router->status = $isOnline ? 'online' : 'offline';
        if ($isOnline) {
            $router->last_seen_at = now();
        }
        $router->save();
        $router->logs()->create([
            'action' => 'ping',
            'message' => $isOnline ? 'Router is online.' : 'Router is offline.',
            'status' => $isOnline ? 'success' : 'failed',
        ]);
        return response()->json([
            'success' => $isOnline,
            'message' => $isOnline ? 'Router is online!' : 'Router is offline!',
            'status' => $router->status,
            'last_seen_at' => $router->last_seen_at,
        ]);
    }

    public function validateRouter(Request $request)
    {
        $data = $request->validate([
            'ip_address' => 'required|ip',
            'api_port' => 'required|integer|min:1|max:65535',
            'ssh_port' => 'required|integer|min:1|max:65535',
            'router_username' => 'required|string',
            'router_password' => 'required|string',
            'connection_type' => 'required|in:api,ssh,ovpn',
        ]);
        $mikrotikService = new \App\Services\MikrotikService(
            $data['ip_address'],
            $data['router_username'],
            $data['router_password'],
            $data['api_port']
        );
        $resources = $mikrotikService->testConnection();
        $isOnline = $resources !== false;
        return response()->json([
            'success' => $isOnline,
            'message' => $isOnline ? 'Router is online.' : 'Router is offline.',
            'router_info' => $resources,
        ]);
    }

    public function downloadCACert($id)
    {
        $router = \App\Models\Tenants\TenantMikrotik::findOrFail($id);
        $profile = $router->openvpnProfile;
        if (!$profile || !$profile->ca_cert_path || !file_exists(storage_path('app/' . $profile->ca_cert_path))) {
            abort(404, 'CA certificate not found');
        }
        return response()->download(
            storage_path('app/' . $profile->ca_cert_path),
            'ca.crt',
            ['Content-Type' => 'application/x-x509-ca-cert']
        );
    }

    public function reprovision($id, MikrotikScriptGenerator $scriptGenerator)
    {
        $router = TenantMikrotik::findOrFail($id);
        $script = $scriptGenerator->generate([
            'name' => $router->name,
            'username' => $router->router_username,
            'password' => $router->router_password,
            'router_id' => $router->id,
            'sync_token' => $router->sync_token,
            'ca_url' => $router->openvpnProfile && $router->openvpnProfile->ca_cert_path ? route('tenants.mikrotiks.downloadCACert', ['mikrotik' => $router->id]) : null,
        ]);
        return Inertia::render('Tenants/Mikrotiks/SetupScript', [
            'router' => $router,
            'script' => $script,
        ]);
    }

    /**
     * Mikrotik phone-home sync endpoint.
     * Called by the router's scheduler to report it is online.
     */
    public function sync($router_id, Request $request)
    {
        $router = TenantMikrotik::find($router_id);
        if (!$router) {
            return response()->json(['success' => false, 'message' => 'Router not found.'], 404);
        }
        $token = $request->query('token');
        if (!$token || $token !== $router->sync_token) {
            return response()->json(['success' => false, 'message' => 'Invalid or missing sync token.'], 403);
        }
        $router->status = 'online';
        $router->last_seen_at = now();
        $router->save();
        $router->logs()->create([
            'action' => 'sync',
            'message' => 'Router phone-home sync received.',
            'status' => 'success',
            'response_data' => $request->all(),
        ]);
        return response()->json([
            'success' => true,
            'message' => 'Sync received. Router marked online.',
            'status' => 'online',
            'last_seen_at' => $router->last_seen_at,
        ]);
    }
}
