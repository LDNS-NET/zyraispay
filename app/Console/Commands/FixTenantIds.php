<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use Illuminate\Support\Str;

class FixTenantIds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenants:fix-ids';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix tenant IDs that might be using domain names instead of UUIDs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking tenant IDs...');

        $tenants = Tenant::all();
        $fixed = 0;

        foreach ($tenants as $tenant) {
            // Check if the ID looks like a domain (contains dots or is not a UUID)
            if (str_contains($tenant->id, '.') || !Str::isUuid($tenant->id)) {
                $oldId = $tenant->id;
                $newId = Str::uuid()->toString();
                
                $this->line("Fixing tenant ID: {$oldId} -> {$newId}");
                
                // Update the tenant ID
                $tenant->update(['id' => $newId]);
                
                // Update any related records
                \App\Models\TenantSubscription::where('tenant_id', $oldId)->update(['tenant_id' => $newId]);
                
                $fixed++;
            }
        }

        $this->info("Fixed {$fixed} tenant IDs.");
        
        if ($fixed === 0) {
            $this->info('All tenant IDs are already properly formatted.');
        }

        return Command::SUCCESS;
    }
}
