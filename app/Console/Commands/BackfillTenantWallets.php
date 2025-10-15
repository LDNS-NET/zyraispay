<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;

class BackfillTenantWallets extends Command
{
    protected $signature = 'tenants:backfill-wallets';
    protected $description = 'Backfill wallet_id for tenants missing one using IntaSend SDK';

    public function handle()
    {
        $tenants = Tenant::whereNull('wallet_id')->get();
        $this->info('Found ' . $tenants->count() . ' tenants without wallet_id.');
        foreach ($tenants as $tenant) {
            try {
                $service = new \IntaSend\IntaSendPHP\APIService([
                    'token' => env('INTASEND_SECRET_KEY'),
                    'test' => env('APP_ENV') !== 'production',
                ]);
                $walletResponse = $service->wallets->create([
                    'currency' => 'KES',
                    'label' => $tenant->id,
                    'can_disburse' => true,
                ]);
                $walletId = $walletResponse['wallet_id'] ?? null;
                if ($walletId) {
                    $tenant->wallet_id = $walletId;
                    $tenant->save();
                    $this->info("Wallet created for tenant {$tenant->id}: $walletId");
                } else {
                    $this->error("Failed to create wallet for tenant {$tenant->id}");
                }
            } catch (\Exception $e) {
                $this->error("Exception for tenant {$tenant->id}: " . $e->getMessage());
            }
        }
        $this->info('Backfill complete.');
    }
} 