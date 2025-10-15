<?php

namespace App\Console\Commands;

use App\Models\Tenants\TenantMikrotik;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestMikrotikRadius extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mikrotik:test-radius 
                            {--mikrotik= : MikroTik ID to test}
                            {--username= : Username to test}
                            {--password= : Password to test}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test RADIUS authentication with a MikroTik router';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $mikrotikId = $this->option('mikrotik');
        $username = $this->option('username');
        $password = $this->option('password');

        if (!$mikrotikId) {
            $mikrotikId = $this->choice(
                'Select a MikroTik router',
                TenantMikrotik::pluck('name', 'id')->toArray()
            );
        }

        $mikrotik = TenantMikrotik::findOrFail($mikrotikId);

        $this->info("Testing RADIUS authentication with MikroTik: {$mikrotik->name} ({$mikrotik->ip_address})");

        if (!$username) {
            $username = $this->ask('Enter username to test');
        }

        if (!$password) {
            $password = $this->secret('Enter password to test');
        }

        $this->testRadiusAuthentication($mikrotik, $username, $password);
    }

    /**
     * Test RADIUS authentication with the given credentials
     */
    protected function testRadiusAuthentication($mikrotik, $username, $password): void
    {
        $url = route('api.mikrotik.auth');
        
        $this->info("\nSending request to: {$url}");
        $this->line("Username: {$username}");
        $this->line("MikroTik IP: {$mikrotik->ip_address}");

        try {
            $response = Http::post($url, [
                'username' => $username,
                'password' => $password,
                'mikrotik_ip' => $mikrotik->ip_address,
                'mac_address' => '00:11:22:33:44:55' // Test MAC address
            ]);

            $status = $response->successful() ? 'SUCCESS' : 'FAILED';
            $statusCode = $response->status();
            
            $this->line("\nResponse Status: <fg={$this->getStatusColor($statusCode)}>{$status} ({$statusCode})</>");
            
            $this->line("\nResponse Body:");
            $this->line(json_encode($response->json(), JSON_PRETTY_PRINT));
            
            if ($response->successful()) {
                $this->info("\n✅ Authentication successful!");
            } else {
                $this->error("\n❌ Authentication failed!");
            }
            
        } catch (\Exception $e) {
            $this->error("\nError: " . $e->getMessage());
            $this->error("\n❌ Failed to connect to the API endpoint.");
        }
    }

    /**
     * Get color for status code
     */
    protected function getStatusColor($statusCode): string
    {
        if ($statusCode >= 200 && $statusCode < 300) {
            return 'green';
        }
        
        if ($statusCode >= 400 && $statusCode < 500) {
            return 'yellow';
        }
        
        return 'red';
    }
}
