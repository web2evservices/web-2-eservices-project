<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Documents extends Model
{
    protected $fillable = [
        'service_request_id',
        'document_type',
        'file_path',
    ];

    public function serviceRequest() {
        return $this->belongsTo(ServiceRequests::class, 'service_request_id');
    }
}
