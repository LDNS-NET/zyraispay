<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenants\NetworkUser;
use App\Models\TenantSetting;
use Carbon\Carbon;

class PruneInactiveHotspotUsers extends Command
{
    protected $signature = 'hotspot:prune-inactive-users';
    protected $description = 'Deletes inactive hotspot users for each tenant based on the prune_inactive_days setting.';

    public function handle()
    {
        $tenants = TenantSetting::where('category', 'hotspot')->get();
        $totalDeleted = 0;
        foreach ($tenants as $setting) {
            $days = (int)($setting->settings['prune_inactive_days'] ?? 0);
            if ($days < 1) {
                continue; // Skip if not set or disabled
            }
            $cutoff = Carbon::now()->subDays($days);
            // Assumes NetworkUser has tenant_id and last_activity_at
            $users = NetworkUser::where('tenant_id', $setting->tenant_id)
                ->where(function($q) use ($cutoff) {
                    $q->whereNull('last_activity_at')
                      ->orWhere('last_activity_at', '<', $cutoff);
                })
                ->get();
            $deleted = 0;
            foreach ($users as $user) {
                $user->delete();
                $deleted++;
            }
            if ($deleted > 0) {
                $this->info("Pruned $deleted inactive users for tenant {$setting->tenant_id}.");
                $totalDeleted += $deleted;
            }
        }
        $this->info("Total inactive users pruned: $totalDeleted");
        return 0;
    }
}
