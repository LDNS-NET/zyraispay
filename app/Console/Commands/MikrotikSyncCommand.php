<?php

namespace App\Console\Commands;

use App\Jobs\SyncUserToMikrotik;
use App\Models\Tenants\NetworkUser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

class MikrotikSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mikrotik:sync 
                            {--user= : Sync a specific user by ID}
                            {--all : Sync all active users}
                            {--force : Force sync even if user exists}'; // Not implemented in the job yet

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync users to MikroTik routers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($userId = $this->option('user')) {
            return $this->syncSingleUser($userId);
        }

        if ($this->option('all')) {
            return $this->syncAllUsers();
        }

        $this->error('Please specify either --user=ID or --all');
        return 1;
    }

    /**
     * Sync a single user to all MikroTik routers.
     *
     * @param int|string $userId
     * @return int
     */
    protected function syncSingleUser($userId): int
    {
        $user = NetworkUser::find($userId);

        if (!$user) {
            $this->error("User with ID {$userId} not found.");
            return 1;
        }

        $this->info("Syncing user: {$user->username} (ID: {$user->id}) to all MikroTik routers...");
        
        try {
            $job = new SyncUserToMikrotik($user, 'update');
            dispatch($job);
            
            $this->info('Sync job queued successfully!');
            $this->info("Check the logs for details: storage/logs/laravel.log");
            
            return 0;
        } catch (\Exception $e) {
            $this->error("Failed to queue sync job: " . $e->getMessage());
            Log::error("Failed to queue user sync job", [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return 1;
        }
    }

    /**
     * Sync all active users to all MikroTik routers.
     *
     * @return int
     */
    protected function syncAllUsers(): int
    {
        if (!$this->confirm('This will sync ALL active users to ALL MikroTik routers. Continue?')) {
            $this->info('Sync cancelled.');
            return 0;
        }

        $this->info('Starting to sync all active users to all MikroTik routers...');
        
        try {
            // Use our enhanced batch sync method
            $batch = SyncUserToMikrotik::dispatchForAllUsers();
            
            $this->info('Batch sync job has been queued!');
            $this->info("Batch ID: {$batch->id}");
            $this->info("Check the Horizon dashboard or logs for progress.");
            
            return 0;
        } catch (\Exception $e) {
            $this->error("Failed to queue batch sync: " . $e->getMessage());
            Log::error("Failed to queue batch sync job", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return 1;
        }
    }
}
