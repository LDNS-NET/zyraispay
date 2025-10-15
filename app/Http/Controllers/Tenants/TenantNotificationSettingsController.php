<?php

namespace App\Http\Controllers\Tenants;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TenantNotificationTemplate;
use Inertia\Inertia;

class TenantNotificationSettingsController extends Controller
{
    public function edit(Request $request)
    {
        $tenantId = tenant('id') ?? ($request->user() ? $request->user()->tenant_id : null);
        if (!$tenantId && app()->environment('local')) {
            $tenantId = \App\Models\Tenant::first()?->id;
        }
        if (!$tenantId) {
            abort(400, 'No tenant context available');
        }
        $templates = TenantNotificationTemplate::where('tenant_id', $tenantId)
            ->remember(60)
            ->get();

        $defaults = [
            'email_enabled' => false,
            'sms_enabled' => false,
            'whatsapp_enabled' => false,
            'notification_email' => '',
            'notification_phone' => '',
            'payment_confirmation' => [
                'enabled' => false,
                'message' => 'Thank you {name} for your payment of {amount}. Your new package is {package_name}. Transaction ID: {transaction_id}.'
            ],
            'final_expiry_notification' => [
                'enabled' => false,
                'message' => 'Dear {name}, your internet package has expired today. Please renew your subscription to restore service.'
            ],
            'mikrotik_status_alert' => [
                'enabled' => false,
                'message' => 'ALERT: Mikrotik router {router_name} ({router_ip}) appears to be offline. Please check its status immediately.'
            ],
            'reminders' => [
                'hotspot' => [ 
                    'enabled' => false, 
                    'days_before' => [3, 1], 
                    'message' => 'Hello {name}, your hotspot package is expiring in {days_left} days. Please renew to stay connected.' 
                ],
                'pppoe' => [ 
                    'enabled' => false, 
                    'days_before' => [3, 1], 
                    'message' => 'Dear {name}, your PPPoE internet plan expires in {days_left} days. To avoid service interruption, please renew your subscription.' 
                ],
                'static' => [ 
                    'enabled' => false, 
                    'days_before' => [3, 1], 
                    'message' => 'Hi {name}, your static IP subscription will expire in {days_left} days. Please contact us to renew.' 
                ],
            ],
        ];

        return Inertia::render('Tenants/Settings/Notifications', [
            'templates' => $templates,
            'defaults' => $defaults,
        ]);
    }

    public function update(Request $request)
    {
        $tenantId = tenant('id') ?? ($request->user() ? $request->user()->tenant_id : null);
        if (!$tenantId && app()->environment('local')) {
            $tenantId = \App\Models\Tenant::first()?->id;
        }
        if (!$tenantId) {
            abort(400, 'No tenant context available');
        }
        $data = $request->validate([
            'email_enabled' => 'boolean',
            'sms_enabled' => 'boolean',
            'whatsapp_enabled' => 'boolean',
            'notification_email' => 'nullable|email',
            'notification_phone' => 'nullable|string',

            'payment_confirmation' => 'present|array',
            'payment_confirmation.enabled' => 'boolean',
            'payment_confirmation.message' => 'nullable|string|max:500',

            'final_expiry_notification' => 'present|array',
            'final_expiry_notification.enabled' => 'boolean',
            'final_expiry_notification.message' => 'nullable|string|max:500',

            'mikrotik_status_alert' => 'present|array',
            'mikrotik_status_alert.enabled' => 'boolean',
            'mikrotik_status_alert.message' => 'nullable|string|max:500',
            'reminders' => 'present|array',
            'reminders.hotspot' => 'present|array',
            'reminders.hotspot.enabled' => 'boolean',
            'reminders.hotspot.days_before' => 'array',
            'reminders.hotspot.days_before.*' => 'integer|min:0',
            'reminders.hotspot.message' => 'nullable|string|max:500',
            'reminders.pppoe' => 'present|array',
            'reminders.pppoe.enabled' => 'boolean',
            'reminders.pppoe.days_before' => 'array',
            'reminders.pppoe.days_before.*' => 'integer|min:0',
            'reminders.pppoe.message' => 'nullable|string|max:500',
            'reminders.static' => 'present|array',
            'reminders.static.enabled' => 'boolean',
            'reminders.static.days_before' => 'array',
            'reminders.static.days_before.*' => 'integer|min:0',
            'reminders.static.message' => 'nullable|string|max:500',
        ]);
        // Save or update notification templates
        foreach ([
            'payment_confirmation', 'final_expiry_notification', 'mikrotik_status_alert',
            'reminders.hotspot', 'reminders.pppoe', 'reminders.static'
        ] as $type) {
            $templateData = data_get($data, str_replace('reminders.', 'reminder_', $type));
            if ($templateData) {
                TenantNotificationTemplate::updateOrCreate(
                    [
                        'tenant_id' => $tenantId,
                        'type' => $type,
                    ],
                    [
                        'channel' => 'email_sms_whatsapp',
                        'message_template' => $templateData['message'] ?? '',
                        'is_active' => $templateData['enabled'] ?? false,
                        'created_by' => auth()->id(),
                        'last_updated_by' => auth()->id(),
                    ]
                );
            }
        }
        cache()->forget("tenant_notification_templates_{$tenantId}");
        return redirect()->back()->with('success', 'Notification settings updated.');
    }
}
