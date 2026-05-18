<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Notifications;
use App\Services\NotificationService;

class Messages extends Model
{
    protected $guarded = [];

   public function sender() {
        return $this->belongsTo(Users::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(Users::class, 'receiver_id');
    }

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequests::class, 'service_request_id');
    }
}
