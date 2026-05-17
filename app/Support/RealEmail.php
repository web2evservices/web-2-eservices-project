<?php

namespace App\Support;

class RealEmail
{
    /** Domains commonly used for fake or placeholder addresses. */
    private const BLOCKED_DOMAINS = [
        'example.com',
        'example.org',
        'example.net',
        'test.com',
        'test.test',
        'invalid',
        'localhost',
    ];

    public static function isReal(?string $email): bool
    {
        if ($email === null || $email === '') {
            return false;
        }

        $email = strtolower(trim($email));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $domain = substr(strrchr($email, '@'), 1);

        if ($domain === false || $domain === '') {
            return false;
        }

        if (in_array($domain, self::BLOCKED_DOMAINS, true)) {
            return false;
        }

        if (str_ends_with($domain, '.test') || str_ends_with($domain, '.invalid') || str_ends_with($domain, '.local')) {
            return false;
        }

        if (config('mail.validate_mx', true) && ! self::domainAcceptsMail($domain)) {
            return false;
        }

        return true;
    }

    private static function domainAcceptsMail(string $domain): bool
    {
        if (! function_exists('checkdnsrr')) {
            return true;
        }

        return checkdnsrr($domain, 'MX') || checkdnsrr($domain, 'A');
    }
}
