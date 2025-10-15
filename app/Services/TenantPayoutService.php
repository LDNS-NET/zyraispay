<?php
namespace App\Services;

use App\Models\TenantSetting;
use IntaSend\IntaSendPHP\Payout;
use Illuminate\Support\Facades\Log;

class TenantPayoutService
{
    /**
     * Disburse funds to tenant using IntaSend Payout API
     * @param float $amount
     * @param int|string $tenantId
     * @param array $meta
     * @return array|null
     */
    public function disburse($amount, $tenantId, $meta = [])
    {
        $setting = TenantSetting::forTenant($tenantId, 'payment_gateway');
        if (!$setting || !isset($setting->settings['payout_method'])) {
            Log::error('No payout method configured for tenant', ['tenant_id' => $tenantId]);
            return null;
        }
        $method = $setting->settings['payout_method'];
        $payoutAmount = round($amount * (env('TENANT_PAYOUT_PERCENT', 0.99)), 2);
        $payout = new Payout();
        $payout->init([
            'token' => env('INTASEND_SECRET_KEY'),
            'publishable_key' => env('INTASEND_PUBLIC_KEY'),
            'test' => env('APP_ENV') !== 'production',
        ]);
        $recipient = null;
        if ($method === 'mpesa_phone' && !empty($setting->settings['phone_number'])) {
            $recipient = [
                'type' => 'M-PESA',
                'number' => $setting->settings['phone_number'],
            ];
        } elseif ($method === 'bank' && !empty($setting->settings['bank_account']) && !empty($setting->settings['bank_name'])) {
            $recipient = [
                'type' => 'BANK',
                'account' => $setting->settings['bank_account'],
                'bank' => $setting->settings['bank_name'],
            ];
        } // Add other payout methods as needed
        if (!$recipient) {
            Log::error('No valid payout recipient for tenant', ['tenant_id' => $tenantId]);
            return null;
        }
        try {
            $response = $payout->send($payoutAmount, $recipient, $meta);
            Log::info('IntaSend payout response', ['tenant_id' => $tenantId, 'response' => $response]);
            return $response;
        } catch (\Exception $e) {
            Log::error('IntaSend payout error', ['tenant_id' => $tenantId, 'error' => $e->getMessage()]);
            return null;
        }
    }
}
