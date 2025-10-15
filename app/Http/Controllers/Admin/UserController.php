<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use App\Services\AfricaTalkingService;

class UserController extends Controller
{
    public function index()
    {
        $tenants = User::where('role', 'tenant')->latest()->get();

        return Inertia::render('Admin/Users/Index', [
            'users' => $tenants,
        ]);
    }




    public function create()
    {
        return Inertia::render('Admin/CreateTenant');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'phone'    => 'required|string|max:20|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $password = $validated['password'];

        // ✅ Format phone number before saving
        $formattedPhone = $validated['phone'];
        if (preg_match('/^0\d+$/', $formattedPhone)) {
            $formattedPhone = '+254' . substr($formattedPhone, 1);
        }

        // ✅ Store the formatted phone
        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'phone'    => $formattedPhone,
            'password' => bcrypt($password),
            'role'     => 'tenant',
        ]);

        // ✅ SMS content
        $message = "Welcome {$user->name}! Your account has been created.\nLogin: {$user->phone}\nPassword: {$password}";

        try {
            $smsService = new \App\Services\AfricaTalkingService();
            $response = $smsService->sendSMS([$user->phone], $message); // use formatted

            \Log::info('✅ SMS sent to new tenant:', [
                'phone' => $user->phone,
                'response' => $response,
            ]);
        } catch (\Exception $e) {
            \Log::error('❌ Failed to send welcome SMS', [
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()->route('admin.users.index')->with('success', 'Tenant created.');
    }




    public function edit(User $user)
    {
        return Inertia::render('Admin/Users/Edit', ['user' => $user]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'required|string|max:20|unique:users,phone,' . $user->id,
        ]);

        $user->update($validated);

        return redirect()->route('admin.users.index')->with('success', 'Tenant updated.');
    }

    public function show($id)
    {
        $user = User::findOrFail($id);

        return Inertia::render('Admin/Users/Show', [
            'user' => $user
        ]);
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Tenant deleted.');
    }
}
