<?php

namespace App\Services;

use App\Services\Contracts\SmsServiceInterface;
use Illuminate\Support\Facades\Log;

class LogSmsService implements SmsServiceInterface
{
    /**
     * Send SMS by logging to file (for testing purposes)
     *
     * @param string $phoneNumber
     * @param string $message
     * @return bool
     */
    public function send(string $phoneNumber, string $message): bool
    {
        try {
            Log::channel('sms')->info('SMS Message Sent', [
                'phone' => $phoneNumber,
                'message' => $message,
                'timestamp' => now(),
            ]);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to log SMS: ' . $e->getMessage());
            return false;
        }
    }
}
