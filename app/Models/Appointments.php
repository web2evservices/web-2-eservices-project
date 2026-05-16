<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointments extends Model
{
    protected $fillable = [
        'office_id',
        'service_id',
        'citizen_id',
        'citizen_name',
        'citizen_email',
        'citizen_phone',
        'date',
        'time_slot',
        'status',
        'notes',
    ];
    protected $casts = [
    'date' => 'datetime',
];
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
