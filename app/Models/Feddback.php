<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feddback extends Model
{
    public function serviceRequest() {
        return $this->belongsTo(ServiceRequests::class);
    }

    public function citizen() {
        return $this->belongsTo(Users::class, 'citizen_id');
    }
}
