<?php

namespace App\Jobs;

use App\Models\Tenants\NetworkUser;
use App\Models\Tenants\TenantMikrotik;
use App\Services\MikrotikService;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Bus;

class SyncUserToMikrotik implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The user to sync or null for batch operations.
     *
     * @var NetworkUser|null
     */
    protected $user;

    /**
     * The operation to perform: 'create', 'update', 'delete', or 'sync-all'.
     *
     * @var string
     */
    protected $operation;

    /**
     * The original package ID for update operations.
     *
     * @var int|null
     */
    protected $originalPackageId;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 300; // Increased for batch operations

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var array
     */
    public $backoff = [60, 300, 900]; // 1 min, 5 mins, 15 mins

    /**
     * Create a new job instance.
     *
     * @param NetworkUser|null $user User to sync (null for batch operations)
     * @param string $operation Operation: 'create', 'update', 'delete', or 'sync-all'
     * @param int|null $originalPackageId For update operations
     */
    public function __construct(?NetworkUser $user = null, string $operation = 'create', ?int $originalPackageId = null)
    {
        $this->user = $user ? $user->withoutGlobalScopes() : null;
        $this->operation = $operation;
        $this->originalPackageId = $originalPackageId;
        
        // Set the queue name from config
        $this->onQueue(config('mikrotik.queue.name', 'mikrotik'));
    }
    
    /**
     * Create a batch job to sync all active users to all MikroTik routers.
     *
     * @return void
     */
    public static function dispatchForAllUsers()
    {
        $batch = Bus::batch([])
            ->name('sync-all-users-to-mikrotik')
            ->onQueue(config('mikrotik.queue.name', 'mikrotik'))
            ->allowFailures()
            ->dispatch();
            
        // Process users in chunks to avoid memory issues
        NetworkUser::where('status', 'active')
            ->with('package')
            ->chunk(100, function ($users) use ($batch) {
                $jobs = $users->map(function ($user) {
                    return new static($user, 'update');
                });
                
                $batch->add($jobs);
            });
            
        return $batch;
    }

    /**
     * Execute the job.
     *
     * @param MikrotikService $mikrotikService
     * @return void
     */
    public function handle(MikrotikService $mikrotikService)
    {
        // Handle batch operation
        if ($this->operation === 'sync-all') {
            $this->syncAllUsers($mikrotikService);
            return;
        }

        // Handle single user operation
        if (!$this->user) {
            Log::error('Cannot perform operation: No user provided', [
                'operation' => $this->operation,
            ]);
            return;
        }

        try {
            $mikrotiks = $this->user->tenantMikrotiks()->get();
            
            if ($mikrotiks->isEmpty()) {
                Log::warning('No MikroTik routers found for tenant', [
                    'user_id' => $this->user->id,
                    'tenant_id' => $this->user->tenant_id,
                ]);
                return;
            }
            
            foreach ($mikrotiks as $mikrotik) {
                if ($this->batch() && $this->batch()->cancelled()) {
                    Log::info('Batch cancelled, stopping user sync', [
                        'user_id' => $this->user->id,
                        'mikrotik_id' => $mikrotik->id,
                    ]);
                    return;
                }
                
                try {
                    $mikrotikService->setConnection(
                        $mikrotik->host,
                        $mikrotik->username,
                        $mikrotik->password,
                        $mikrotik->port ?? 8728,
                        $mikrotik->use_ssl ?? false
                    );

                    $this->performOperation($mikrotikService, $mikrotik);
                } catch (\Exception $e) {
                    Log::error("Failed to sync user to MikroTik: " . $e->getMessage(), [
                        'user_id' => $this->user->id,
                        'mikrotik_id' => $mikrotik->id,
                        'operation' => $this->operation,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    
                    // Continue with other MikroTiks even if one fails
                    continue;
                }
            }
        } catch (\Exception $e) {
            Log::error("Failed to sync user to MikroTiks: " . $e->getMessage(), [
                'user_id' => $this->user->id ?? null,
                'operation' => $this->operation,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            if ($this->attempts() >= $this->tries) {
                $this->fail($e);
            } else {
                $this->release($this->backoff[$this->attempts() - 1] ?? 60);
            }
            
            throw $e;
        }
    }

    /**
     * Perform the operation on the MikroTik.
     *
     * @param MikrotikService $mikrotikService
     * @param TenantMikrotik $mikrotik
     * @return void
     */
    private function performOperation(MikrotikService $mikrotikService, TenantMikrotik $mikrotik)
    {
        Log::info("Processing MikroTik sync job", [
            'user_id' => $this->user->id,
            'operation' => $this->operation,
            'mikrotik_id' => $mikrotik->id,
            'mikrotik_ip' => $mikrotik->host,
        ]);

        // Handle the operation
        switch ($this->operation) {
            case 'create':
                $this->createUser($mikrotikService);
                break;
            case 'update':
                $this->updateUser($mikrotikService);
                break;
            case 'delete':
                $this->deleteUser($mikrotikService);
                break;
            default:
                Log::error("Unknown operation: {$this->operation}", [
                    'user_id' => $this->user->id,
                    'mikrotik_id' => $mikrotik->id,
                ]);
                $this->fail(new \Exception("Unknown operation: {$this->operation}"));
        }
    }

    /**
     * Sync all active users to all MikroTik routers.
     *
     * @param MikrotikService $mikrotikService
     * @return void
     */
    protected function syncAllUsers(MikrotikService $mikrotikService)
    {
        Log::info('Starting batch sync of all users to MikroTik routers');
        
        // Get all active users with their packages
        $users = NetworkUser::where('status', 'active')
            ->with('package')
            ->get();
            
        $totalUsers = $users->count();
        $successCount = 0;
        $failCount = 0;
        
        foreach ($users as $user) {
            try {
                // Create a new job for each user
                $job = new static($user, 'update');
                $job->handle($mikrotikService);
                $successCount++;
            } catch (\Exception $e) {
                Log::error("Failed to sync user in batch: " . $e->getMessage(), [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
                $failCount++;
            }
        }
        
        Log::info('Completed batch sync of users to MikroTik routers', [
            'total_users' => $totalUsers,
            'successful' => $successCount,
            'failed' => $failCount,
        ]);
    }
    
    /**
     * Handle a job failure.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        // Log the failure
        $context = [
            'operation' => $this->operation,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ];
        
        if ($this->user) {
            $context['user_id'] = $this->user->id;
        }
        
        Log::error('MikroTik sync job failed after all attempts', $context);
        
        // Optionally, notify admins about the failure
        // if (config('mikrotik.notifications.enabled', false)) {
        //     Notification::route('mail', config('mikrotik.notifications.email'))
        //         ->notify(new MikrotikSyncFailed(
        //             $this->user,
        //             $this->operation,
        //             $exception
        //         ));
        // }
}
}
