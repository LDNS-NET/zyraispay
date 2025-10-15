<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MikrotikService;
use App\Models\Tenants\NetworkUser;

class PollMikrotikOnlineUsers extends Command
{
    protected $signature = 'mikrotik:poll-online-users';
    protected $description = 'Poll Mikrotik for online users and update DB status';

    public function handle()
    {
        $routerHost = config('mikrotik.host', '192.168.88.1');
        $routerUser = config('mikrotik.username', 'admin');
        $routerPass = config('mikrotik.password', 'password');
        $routerPort = config('mikrotik.port', 8728);
        $mikrotik = new MikrotikService($routerHost, $routerUser, $routerPass, $routerPort);
        $onlineUsers = $mikrotik->getOnlineUsers();
        $onlineUsernames = collect($onlineUsers)->pluck('name')->toArray();

        // Set all users offline first
        NetworkUser::query()->update(['online' => false]);
        // Set online users
        NetworkUser::whereIn('username', $onlineUsernames)->update(['online' => true]);

        $this->info('Online user statuses updated.');
    }
}
