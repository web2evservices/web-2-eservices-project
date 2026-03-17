<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payments extends Model
{
    public function serviceRequest() {
        return $this->belongsTo(ServiceRequests::class, 'service_request_id');
    }
}
