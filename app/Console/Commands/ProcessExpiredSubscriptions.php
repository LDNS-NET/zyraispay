<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SubscriptionService;
use App\Models\TenantSubscription;

class ProcessExpiredSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:process-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process expired subscriptions and mark them as expired';

    protected $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        parent::__construct();
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Processing expired subscriptions...');

        // Process expired subscriptions
        $this->subscriptionService->processExpiredSubscriptions();

        // Send reminders for subscriptions ending soon
        $this->subscriptionService->sendSubscriptionReminders();

        // Get analytics
        $analytics = $this->subscriptionService->getSubscriptionAnalytics();

        $this->info('Subscription processing completed!');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Subscriptions', $analytics['total']],
                ['Active Subscriptions', $analytics['active']],
                ['Expired Subscriptions', $analytics['expired']],
                ['Trial Subscriptions', $analytics['trial']],
                ['Active Percentage', $analytics['active_percentage'] . '%'],
            ]
        );

        return Command::SUCCESS;
    }
}
