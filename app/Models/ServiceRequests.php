<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceRequests extends Model
{
    protected $fillable = [
        'citizen_id',
        'service_id',
        'status',
        'qr_code',
        'appointment_id',
    ];

    public function citizen() {
        return $this->belongsTo(Users::class, 'citizen_id');
    }

    public function service() {
        return $this->belongsTo(Services::class, 'service_id');
    }

    public function appointment() {
        return $this->belongsTo(Appointments::class, 'appointment_id');
    }

    public function payment() {
        return $this->hasOne(Payments::class);
    }

    public function documents() {
        return $this->hasMany(Documents::class, 'service_request_id');
    }

    public function requestHistories() {
        return $this->hasMany(RequestHistories::class, 'service_request_id');
    }

    public function feedbacks() {
        return $this->hasMany(Feddback::class, 'service_request_id');
    }

    public function messages() {
        return $this->hasMany(Messages::class);
    }
}