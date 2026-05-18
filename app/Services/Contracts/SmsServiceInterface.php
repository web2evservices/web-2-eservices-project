<?php

namespace App\Services\Contracts;

interface SmsServiceInterface
{
    /**
     * Send an SMS message
     *
     * @param string $phoneNumber
     * @param string $message
     * @return bool
     */
    public function send(string $phoneNumber, string $message): bool;
}
