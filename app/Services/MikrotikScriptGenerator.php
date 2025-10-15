<?php

namespace App\Services;

class MikrotikScriptGenerator
{
    /**
     * Generate a full, system-ready onboarding script for Mikrotik routers.
     * Only username, password, and desired name are required.
     *
     * @param array $options
     *   - name: Desired router name
     *   - username: API/system username
     *   - password: API/system password
     * @return string
     */
    public function generate(array $options): string
    {
        $name = $options['name'] ?? 'ISP-Managed';
        $username = $options['username'] ?? 'apiuser';
        $password = $options['password'] ?? 'apipassword';
        $router_id = $options['router_id'] ?? 'ROUTER_ID';
        $tenant_id = $options['tenant_id'] ?? 'TENANT_ID';
        $vpn_server = $options['vpn_server'] ?? 'vpn.example.com';
        $vpn_user = $options['vpn_user'] ?? $tenant_id;
        $vpn_pass = $options['vpn_pass'] ?? $password;
        $ca_url = $options['ca_url'] ?? null;
        if (!$ca_url && !empty($options['router_id'])) {
            $ca_url = route('tenants.mikrotiks.downloadCACert', ['mikrotik' => $options['router_id']]);
        }
        if (!$ca_url) {
            $ca_url = "https://api.example.com/tenant/$tenant_id/ca.crt";
        }
        $radius_ip = $options['radius_ip'] ?? '10.0.0.1';
        $radius_secret = $options['radius_secret'] ?? 'RADIUS_SECRET';
        $sync_token = $options['sync_token'] ?? null;
        $sync_url = $options['sync_url'] ?? null;
        if (!$sync_url && !empty($router_id)) {
            $sync_url = "https://api.example.com/router/sync/$router_id";
            if ($sync_token) {
                $sync_url .= "?token=$sync_token";
            }
        }
        if (!$sync_url) {
            $sync_url = "https://api.example.com/router/sync/$router_id";
        }
        $trusted_ip = $options['trusted_ip'] ?? '0.0.0.0';

        $templatePath = resource_path('scripts/mikrotik_onboarding.rsc.stub');
        $template = file_exists($templatePath) ? file_get_contents($templatePath) : '';
        if (!$template) return '';
        // Replace placeholders
        $replacements = compact('name','username','password','router_id','tenant_id','vpn_server','vpn_user','vpn_pass','ca_url','radius_ip','radius_secret','sync_url','trusted_ip');
        foreach ($replacements as $key => $value) {
            $template = str_replace('{{'.$key.'}}', $value, $template);
        }
        return $template;
    }
} 