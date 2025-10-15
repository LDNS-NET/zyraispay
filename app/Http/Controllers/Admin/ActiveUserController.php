<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use Inertia\Inertia;

class ActiveUserController extends Controller
{
    public function activeUsers()
    {
        $activeUsers = User::where('role', 'tenant')
            ->where('last_login_at', '>=', Carbon::now()->subDays(7))
            ->select('id', 'name', 'email', 'phone', 'last_login_at', 'trial_expires_at', 'tenant_id')
            ->orderByDesc('last_login_at')
            ->get()
            ->map(function ($user) {
                $trialExpires = $user->trial_expires_at ? Carbon::parse($user->trial_expires_at) : null;

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'last_login_at' => $user->last_login_at,
                    'trial_expires_at' => $user->trial_expires_at,
                    'trial_remaining_seconds' => $trialExpires ? now()->diffInSeconds($trialExpires, false) : 0,
                    'is_trial_active' => $trialExpires && $trialExpires->isFuture(),
                    'tenant_id' => $user->tenant_id,
                    'tenant_name' => $user->tenant_id, // Optional: you can look up the actual name
                ];
            });

        return Inertia::render('Admin/Users/Active', ['activeUsers' => $activeUsers]);
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'trial_expires_at' => 'nullable|date',
        ]);

        $user->update([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'trial_expires_at' => $request->input('trial_expires_at'),
        ]);

        return redirect()->back()->with('success', 'User updated.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return back()->with('success', 'User deleted.');
    }
}
