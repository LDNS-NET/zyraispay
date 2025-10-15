<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use App\Models\Tenants\NetworkUser;
use App\Models\TenantSetting;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Services\AfricaTalkingService;

class SendExpiryReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminders:send-expiry';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan all tenants and send expiry reminders to users based on their settings.';

    /**
     * Execute the console command.
     */
    public function handle(AfricaTalkingService $smsService)
    {
        $this->info('Starting to check for expiry reminders across all tenants...');
        Log::info('Starting SendExpiryReminders command.');

        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            tenancy()->initialize($tenant);
            $this->info("Processing tenant: {$tenant->id}");
            Log::info("Processing tenant: {$tenant->id}");

            $settings = TenantSetting::where('category', 'notifications')->first()->settings ?? [];

            if (empty($settings['reminders'])) {
                $this->warn("Tenant {$tenant->id} has no reminder settings configured. Skipping.");
                Log::warning("Tenant {$tenant->id} has no reminder settings configured. Skipping.");
                continue;
            }

            $userTypes = ['hotspot', 'pppoe', 'static'];

            foreach ($userTypes as $type) {
                if (!($settings['reminders'][$type]['enabled'] ?? false) || empty($settings['reminders'][$type]['days_before'])) {
                    continue;
                }

                $reminderDays = $settings['reminders'][$type]['days_before'];

                foreach ($reminderDays as $day) {
                    $targetDate = Carbon::today()->addDays($day);
                    $expiringUsers = NetworkUser::where('type', $type)
                        ->whereDate('expires_at', $targetDate->toDateString())
                        ->get();

                    if ($expiringUsers->isNotEmpty()) {
                        $this->info("Found {" . $expiringUsers->count() . "} {$type} users expiring in {$day} day(s).");
                        foreach ($expiringUsers as $user) {
                            $messageTemplate = $settings['reminders'][$type]['message'] ?? 'Hello {name}, your package is expiring in {days_left} days.';

                            $placeholders = [
                                '{name}' => $user->full_name,
                                '{package_name}' => $user->package->name ?? 'N/A',
                                '{expires_at}' => $user->expires_at->format('Y-m-d'),
                                '{days_left}' => $day,
                            ];

                            $message = str_replace(array_keys($placeholders), array_values($placeholders), $messageTemplate);

                            if ($user->phone) {
                                $response = $smsService->sendSMS([$user->phone], $message);
                                Log::info("Sent expiry reminder SMS to {$user->phone} for tenant {$tenant->id}", [
                                    'user_id' => $user->id,
                                    'response' => $response
                                ]);
                            } else {
                                Log::warning("User {$user->id} for tenant {$tenant->id} has no phone number. Cannot send SMS.");
                            }
                        }
                    }
                }
            }

            // --- Handle Final Expiry Notification ---
            if (!empty($settings['final_expiry_notification']['enabled'])) {
                $this->info("Checking for final expiry notifications for tenant {$tenant->id}.");
                Log::info("Checking for final expiry notifications for tenant {$tenant->id}.");

                $expiringTodayUsers = NetworkUser::whereDate('expires_at', Carbon::today()->toDateString())->get();

                if ($expiringTodayUsers->isNotEmpty()) {
                    $this->info("Found {" . $expiringTodayUsers->count() . "} users expiring today.");
                    $messageTemplate = $settings['final_expiry_notification']['message'] ?? 'Dear {name}, your internet package has expired today. Please renew your subscription to restore service.';

                    foreach ($expiringTodayUsers as $user) {
                        $placeholders = [
                            '{name}' => $user->full_name ?? $user->username,
                            '{package_name}' => $user->package->name ?? 'N/A',
                            '{expires_at}' => $user->expires_at->format('Y-m-d'),
                        ];

                        $message = str_replace(array_keys($placeholders), array_values($placeholders), $messageTemplate);

                        if ($user->phone) {
                            try {
                                $response = $smsService->sendSMS([$user->phone], $message);
                                Log::info("Sent final expiry SMS to {$user->phone} for tenant {$tenant->id}", [
                                    'user_id' => $user->id,
                                    'response' => $response
                                ]);
                            } catch (\Exception $e) {
                                Log::error('Failed to send final expiry SMS', ['error' => $e->getMessage(), 'user_id' => $user->id]);
                            }
                        } else {
                            Log::warning("User {$user->id} for tenant {$tenant->id} has no phone number for final expiry SMS.");
                        }
                    }
                }
            }

            tenancy()->end();
        }

        Log::info('SendExpiryReminders command finished.');
        $this->info('Expiry reminder check complete.');
        return 0;
    }
}
