<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointments extends Model
{
     public function office() {
        return $this->belongsTo(Government_Offices::class, 'office_id');
    }

    public function citizen() {
        return $this->belongsTo(Users::class, 'citizen_id');
    }

    public function service() {
        return $this->belongsTo(Services::class, 'service_id');
    }

    public function serviceRequest() {
        return $this->hasOne(ServiceRequests::class, 'appointment_id');
    }
}
