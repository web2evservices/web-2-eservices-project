<?php

namespace App\Events;

use App\Models\Feddback;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FeedbackReceived
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Feddback $feedback
    ) {}
}
