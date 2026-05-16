<?php

namespace App\Events;

use App\Models\ServiceRequests;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RequestStatusUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public ServiceRequests $request,
        public string $oldStatus,
        public string $newStatus
    ) {}
}
