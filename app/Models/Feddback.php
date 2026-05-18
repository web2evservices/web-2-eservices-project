<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\NotificationService;

class Feddback extends Model
{
    protected $table = 'feedback';

    protected $fillable = [
        'service_request_id',
        'citizen_id',
        'rating',
        'comment',
        'response',
    ];

    public function serviceRequest() {
        return $this->belongsTo(ServiceRequests::class);
    }

    public function citizen()
    {
        return $this->belongsTo(Users::class, 'citizen_id');
    }
}
