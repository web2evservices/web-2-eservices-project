<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestHistories extends Model
{
    protected $fillable = [
        'service_request_id',
        'old_status',
        'new_status',
        'changed_by'
    ];

    public function serviceRequest(){
        return $this->belongsTo(ServiceRequests::class,'service_request_id');
    }
}
