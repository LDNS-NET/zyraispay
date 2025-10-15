<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;

class MikrotikApiService
{
    protected $host;
    protected $port;
    protected $username;
    protected $password;

    public function __construct($host, $username, $password, $port = 8728)
    {
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Connect to Mikrotik and fetch online users (hotspot, pppoe, static)
     * Returns array: [['username' => ..., 'mac' => ..., 'ip' => ..., 'user_type' => ...], ...]
     */
    public function getOnlineUsers(): array
    {
        try {
            $config = [
                'host' => $this->host,
                'user' => $this->username,
                'pass' => $this->password,
                'port' => $this->port,
            ];
            $client = new \RouterOS\Client($config);
            $users = [];

            // Hotspot users
            $hotspot = $client->query('/ip/hotspot/active')->read();
            foreach ($hotspot as $row) {
                $users[] = [
                    'username' => $row['user'] ?? null,
                    'mac' => $row['mac-address'] ?? null,
                    'ip' => $row['address'] ?? null,
                    'user_type' => 'hotspot',
                    'session_start' => $row['login-by'] ?? null,
                    'session_end' => null,
                ];
            }

            // PPPoE users
            $pppoe = $client->query('/ppp/active')->read();
            foreach ($pppoe as $row) {
                $users[] = [
                    'username' => $row['name'] ?? null,
                    'mac' => null,
                    'ip' => $row['address'] ?? null,
                    'user_type' => 'pppoe',
                    'session_start' => $row['uptime'] ?? null,
                    'session_end' => null,
                ];
            }

            // Static DHCP leases (optional, if you want static users)
            $static = $client->query('/ip/dhcp-server/lease')->read();
            foreach ($static as $row) {
                if (($row['status'] ?? '') === 'bound' && ($row['dynamic'] ?? 'true') === 'false') {
                    $users[] = [
                        'username' => $row['host-name'] ?? null,
                        'mac' => $row['mac-address'] ?? null,
                        'ip' => $row['address'] ?? null,
                        'user_type' => 'static',
                        'session_start' => null,
                        'session_end' => null,
                    ];
                }
            }

            return $users;
        } catch (Exception $e) {
            Log::error('Mikrotik API error: ' . $e->getMessage());
            return [];
        }
    }
}
