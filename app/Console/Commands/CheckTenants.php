<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use App\Models\TenantSubscription;

class CheckTenants extends Command
{
    protected $signature = 'tenants:check';
    protected $description = 'Check current tenants and their subscriptions';

    public function handle()
    {
        $this->info('Current Tenants:');
        $this->table(
            ['ID', 'Business Name', 'Email', 'Domain'],
            Tenant::all()->map(function($tenant) {
                $domain = $tenant->domains()->first();
                return [
                    $tenant->id,
                    $tenant->business_name,
                    $tenant->email,
                    $domain ? $domain->domain : 'No domain'
                ];
            })
        );

        $this->info('Current Subscriptions:');
        $this->table(
            ['Tenant ID', 'Status', 'Trial Ends', 'Days Remaining'],
            TenantSubscription::all()->map(function($sub) {
                return [
                    $sub->tenant_id,
                    $sub->status,
                    $sub->trial_ends_at ? $sub->trial_ends_at->format('Y-m-d H:i:s') : 'N/A',
                    $sub->getTrialDaysRemaining()
                ];
            })
        );

        return Command::SUCCESS;
    }
}
