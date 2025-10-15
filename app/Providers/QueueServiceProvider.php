<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class QueueServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Only run in production or when explicitly enabled in .env
        if (!app()->runningInConsole() && (app()->environment('production') || env('AUTO_START_QUEUE_WORKER', false))) {
            $this->startQueueWorker();
        }
    }

    /**
     * Start the queue worker in the background.
     */
    protected function startQueueWorker(): void
    {
        // Check if the worker is already running
        if ($this->isWorkerRunning()) {
            Log::info('MikroTik queue worker is already running');
            return;
        }

        // Start the worker in the background
        $command = 'start /B php "' . base_path('artisan') . '" queue:work database --queue=mikrotik --tries=3 --timeout=300 --sleep=3 --max-jobs=50 --max-time=3600 > NUL 2>&1';
        
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            pclose(popen($command, 'r'));
        } else {
            exec(sprintf("%s > %s 2>&1 & echo $! >> %s", 
                $command, 
                base_path('storage/logs/queue-worker.log'),
                base_path('storage/logs/queue-worker.pid'))
            );
        }

        Log::info('MikroTik queue worker started in the background');
    }

    /**
     * Check if the queue worker is already running.
     */
    protected function isWorkerRunning(): bool
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // For Windows
            $output = [];
            exec('tasklist /FI "IMAGENAME eq php.exe" /FO LIST', $output);
            $processes = implode("\n", $output);
            return str_contains($processes, 'queue:work database --queue=mikrotik');
        } else {
            // For Unix/Linux
            $command = 'ps aux | grep "[q]ueue:work database --queue=mikrotik" | wc -l';
            return (int)shell_exec($command) > 0;
        }
    }
}
