<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenants\NetworkUser;
use App\Models\Tenants\TenantMikrotik;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MikrotikAuthController extends Controller
{
    /**
     * Authenticate a user via MikroTik
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function authenticate(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
            'mikrotik_ip' => 'required|ip',
            'mac_address' => 'nullable|string',
        ]);

        // Find the MikroTik by IP and ensure it belongs to a tenant
        $mikrotik = TenantMikrotik::where('ip_address', $request->mikrotik_ip)
            ->whereHas('tenant')
            ->first();

        if (!$mikrotik) {
            Log::warning('MikroTik not found or not associated with a tenant', [
                'ip' => $request->mikrotik_ip,
                'username' => $request->username
            ]);
            return response()->json(['success' => false, 'message' => 'Authentication failed'], 401);
        }

        // Set the current tenant context
        tenancy()->initialize($mikrotik->tenant);

        // Find the user by username
        $user = NetworkUser::where('username', $request->username)
            ->where('status', 'active')
            ->with('package')
            ->first();

        // Verify user exists and password matches
        if (!$user || !$this->verifyPassword($request->password, $user->password)) {
            Log::warning('Invalid credentials', [
                'username' => $request->username,
                'mikrotik_id' => $mikrotik->id
            ]);
            return response()->json(['success' => false, 'message' => 'Invalid credentials'], 401);
        }

        // Check if user is expired
        if ($user->expires_at && $user->expires_at->isPast()) {
            Log::warning('User account has expired', [
                'user_id' => $user->id,
                'expires_at' => $user->expires_at
            ]);
            return response()->json(['success' => false, 'message' => 'Account has expired'], 403);
        }

        // Log the successful authentication
        Log::info('User authenticated via MikroTik', [
            'user_id' => $user->id,
            'username' => $user->username,
            'mikrotik_id' => $mikrotik->id,
            'ip' => $request->ip(),
            'mac_address' => $request->mac_address
        ]);

        // Update last login time
        $user->update(['last_login_at' => now()]);

        // Return success with user's package profile name
        return response()->json([
            'success' => true,
            'profile' => $user->package->name,
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'full_name' => $user->full_name,
                'package' => $user->package->name
            ]
        ]);
    }

    /**
     * Verify the given password against the hashed password
     * 
     * @param string $plain
     * @param string $hashed
     * @return bool
     */
    protected function verifyPassword($plain, $hashed)
    {
        // If the password is already hashed with bcrypt
        if (Str::startsWith($hashed, '$2y$')) {
            return password_verify($plain, $hashed);
        }
        
        // For plain text comparison (not recommended, for backward compatibility)
        return hash_equals($hashed, $plain);
    }

    /**
     * Get user profile for MikroTik
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProfile(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'mikrotik_ip' => 'required|ip',
        ]);

        // Find the MikroTik by IP
        $mikrotik = TenantMikrotik::where('ip_address', $request->mikrotik_ip)
            ->whereHas('tenant')
            ->first();

        if (!$mikrotik) {
            return response()->json(['success' => false, 'message' => 'MikroTik not found'], 404);
        }

        // Set the current tenant context
        tenancy()->initialize($mikrotik->tenant);

        // Find the user by username
        $user = NetworkUser::where('username', $request->username)
            ->where('status', 'active')
            ->with('package')
            ->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }

        return response()->json([
            'success' => true,
            'profile' => $user->package->name,
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'full_name' => $user->full_name,
                'package' => $user->package->name
            ]
        ]);
    }
}
