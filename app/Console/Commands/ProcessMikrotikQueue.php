<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessMikrotikQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:process-mikrotik';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process the MikroTik queue';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting MikroTik queue worker...');
        
        $this->call('queue:work', [
            '--queue' => 'mikrotik',
            '--tries' => 3,
            '--timeout' => 300,
            '--sleep' => 3,
            '--max-jobs' => 50,
            '--max-time' => 3600, // Restart the worker every hour
            '--stop-when-empty' => false,
        ]);

        return Command::SUCCESS;
    }
}
