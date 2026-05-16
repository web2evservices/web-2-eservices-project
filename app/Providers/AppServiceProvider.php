<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Contracts\SmsServiceInterface;
use App\Services\LogSmsService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind SMS service to the container
        // Use LogSmsService by default (logs to file)
        // To use Twilio, change to: TwilioSmsService::class
        $this->app->bind(
            SmsServiceInterface::class,
            LogSmsService::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
