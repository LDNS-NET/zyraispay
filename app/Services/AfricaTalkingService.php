<?php

namespace App\Services;

use AfricasTalking\SDK\AfricasTalking;
use Exception;

class AfricaTalkingService
{
    protected $sms;
    protected $application;

    public function __construct()
    {
        $username = config('services.africastalking.username');
        $apiKey = config('services.africastalking.api_key');

        $at = new AfricasTalking($username, $apiKey);

        $this->sms = $at->sms();
        $this->application = $at->application();
    }

    /**
     * Send SMS to recipients with proper error handling.
     *
     * @param array $recipients
     * @param string $message
     * @return array
     */
    public function sendSMS(array $recipients, string $message): array
    {
        try {
            $result = $this->sms->send([
                'to' => $recipients,
                'message' => $message,
            ]);

            return [
                'status' => 'sent',
                'provider_response' => $result,
            ];
        } catch (Exception $e) {
            return [
                'status' => 'failed',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get Africa's Talking account balance using the Application API.
     *
     * @return array
     */
    public function getBalance(): array
    {
        try {
            $response = $this->application->fetchApplicationData();

            return [
                'status' => 'success',
                'balance' => $response['UserData']['balance'] ?? 'Unknown',
                'raw' => $response,
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }
}
