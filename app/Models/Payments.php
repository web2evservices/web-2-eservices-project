<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payments extends Model
{
    protected $fillable = [
        'service_request_id',
        'amount',
        'currency',
        'payment_method',
        'status',
        'transaction_id',
    ];

    public function serviceRequest() {
        return $this->belongsTo(ServiceRequests::class, 'service_request_id');
    }
}
