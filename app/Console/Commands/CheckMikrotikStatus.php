<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use App\Models\Tenants\TenantMikrotik;
use App\Models\TenantSetting;
use App\Services\AfricaTalkingService;
use App\Services\MikrotikService;
use Illuminate\Support\Facades\Log;

class CheckMikrotikStatus extends Command
{
    protected $signature = 'mikrotik:check-status';
    protected $description = 'Check the status of all Mikrotik routers for all tenants and send alerts for offline devices.';

    public function handle(AfricaTalkingService $smsService)
    {
        $this->info('Starting Mikrotik status check for all tenants...');
        Log::info('Starting CheckMikrotikStatus command.');

        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            tenancy()->initialize($tenant);
            $this->info("Processing tenant: {$tenant->id}");
            Log::info("Processing tenant: {$tenant->id}");

            $settings = TenantSetting::where('category', 'notifications')->first()->settings ?? [];

            if (empty($settings['mikrotik_status_alert']['enabled'])) {
                $this->warn("Tenant {$tenant->id} has Mikrotik status alerts disabled. Skipping.");
                continue;
            }

            $notificationPhone = $settings['notification_phone'] ?? null;
            if (empty($notificationPhone)) {
                $this->warn("Tenant {$tenant->id} has no notification phone number configured. Skipping.");
                continue;
            }

            $routers = TenantMikrotik::all();
            if ($routers->isEmpty()) {
                $this->info("Tenant {$tenant->id} has no routers to check.");
                continue;
            }

            foreach ($routers as $router) {
                $mikrotikService = new MikrotikService($router);
                $isOnline = $mikrotikService->ping();

                if (!$isOnline && $router->status !== 'offline') {
                    // Router just went offline
                    $router->update(['status' => 'offline']);
                    $this->error("Router {$router->name} ({$router->ip_address}) is offline!");
                    Log::error("Router {$router->name} ({$router->ip_address}) for tenant {$tenant->id} is offline.");

                    $messageTemplate = $settings['mikrotik_status_alert']['message'] ?? 'ALERT: Mikrotik router {router_name} ({router_ip}) appears to be offline.';
                    $placeholders = [
                        '{router_name}' => $router->name,
                        '{router_ip}' => $router->ip_address,
                    ];
                    $message = str_replace(array_keys($placeholders), array_values($placeholders), $messageTemplate);

                    try {
                        $smsService->sendSMS([$notificationPhone], $message);
                        Log::info("Sent offline alert for router {$router->name} to {$notificationPhone}");
                    } catch (\Exception $e) {
                        Log::error("Failed to send offline alert for router {$router->name}", ['error' => $e->getMessage()]);
                    }

                } elseif ($isOnline && $router->status === 'offline') {
                    // Router came back online
                    $router->update(['status' => 'online']);
                    $this->info("Router {$router->name} is back online.");
                    Log::info("Router {$router->name} for tenant {$tenant->id} is back online.");
                }
            }

            tenancy()->end();
        }

        Log::info('CheckMikrotikStatus command finished.');
        $this->info('Mikrotik status check complete.');
        return 0;
    }
}
