<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Stancl\Tenancy\Facades\Tenancy;

class LogLastLogin
{
    public function handle(Login $event)
    {
        Log::info('Login event triggered for: ' . $event->user->email);

        if ($event->user->is_super_admin ?? false) {
            Log::info('Login skipped for super admin.');
            return;
        }

        $tenant = tenant(); // ✅ fixed here
        if (!$tenant) {
            Log::warning('No tenant context found during login for: ' . $event->user->email);
            return;
        }

        if (Schema::hasColumn('users', 'last_login_at')) {
            DB::table('users')
                ->where('id', $event->user->id)
                ->update(['last_login_at' => now()]);

            Log::info("last_login_at updated for user ID: {$event->user->id}");
        } else {
            Log::warning('Column last_login_at missing in users table for tenant: ' . $tenant->id);
        }
    }
}
