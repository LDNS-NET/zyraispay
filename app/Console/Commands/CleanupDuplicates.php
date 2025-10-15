<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use Illuminate\Support\Str;

class CleanupDuplicates extends Command
{
    protected $signature = 'tenants:cleanup-duplicates';
    protected $description = 'Clean up duplicate tenant records';

    public function handle()
    {
        $this->info('Cleaning up duplicate tenant records...');

        // Get all tenants
        $tenants = Tenant::all();
        $tenantIds = $tenants->pluck('id')->toArray();
        
        // Find duplicates
        $duplicates = array_diff_assoc($tenantIds, array_unique($tenantIds));
        
        if (empty($duplicates)) {
            $this->info('No duplicate tenant IDs found.');
            return Command::SUCCESS;
        }

        $this->info('Found duplicate tenant IDs: ' . implode(', ', $duplicates));

        // For each duplicate, keep the first one and delete the rest
        foreach ($duplicates as $duplicateId) {
            $duplicateTenants = Tenant::where('id', $duplicateId)->get();
            
            if ($duplicateTenants->count() > 1) {
                $this->line("Processing duplicate ID: {$duplicateId}");
                
                // Keep the first tenant, delete the rest
                $keepTenant = $duplicateTenants->first();
                $deleteTenants = $duplicateTenants->skip(1);
                
                foreach ($deleteTenants as $deleteTenant) {
                    $this->line("Deleting duplicate tenant: {$deleteTenant->business_name}");
                    
                    // Delete associated records
                    TenantSubscription::where('tenant_id', $deleteTenant->id)->delete();
                    $deleteTenant->domains()->delete();
                    
                    // Delete the tenant
                    $deleteTenant->delete();
                }
            }
        }

        $this->info('Cleanup completed!');
        return Command::SUCCESS;
    }
}
