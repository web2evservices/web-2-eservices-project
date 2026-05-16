<?php

namespace App\Services;

use App\Services\Contracts\SmsServiceInterface;
use Illuminate\Support\Facades\Log;

/**
 * Twilio SMS Service
 * 
 * To use this service:
 * 1. Install Twilio: composer require twilio/sdk
 * 2. Add to .env:
 *    TWILIO_ACCOUNT_SID=your_account_sid
 *    TWILIO_AUTH_TOKEN=your_auth_token
 *    TWILIO_PHONE_NUMBER=your_twilio_number
 * 3. Change APP_SMS_SERVICE=twilio in .env
 * 4. Update config/services.php with Twilio config
 */
class TwilioSmsService implements SmsServiceInterface
{
    protected $client;

    public function __construct()
    {
        // Uncomment and use when Twilio is installed
        // $this->client = new \Twilio\Rest\Client(
        //     config('services.twilio.account_sid'),
        //     config('services.twilio.auth_token')
        // );
    }

    /**
     * Send SMS via Twilio
     *
     * @param string $phoneNumber
     * @param string $message
     * @return bool
     */
    public function send(string $phoneNumber, string $message): bool
    {
        try {
            // Uncomment when Twilio is installed
            // $this->client->messages->create(
            //     $phoneNumber,
            //     [
            //         'from' => config('services.twilio.phone_number'),
            //         'body' => $message,
            //     ]
            // );
            
            // For now, just log it
            Log::channel('sms')->info('Twilio SMS (Disabled)', [
                'phone' => $phoneNumber,
                'message' => $message,
            ]);
            return true;
        } catch (\Exception $e) {
            Log::error('Twilio SMS Failed: ' . $e->getMessage());
            return false;
        }
    }
}
