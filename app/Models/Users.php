<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Users extends Model
{
     public function offices() {
        return $this->hasMany(Government_Offices::class, 'municipality_id');
    }

    public function serviceRequests() {
        return $this->hasMany(ServiceRequests::class, 'citizen_id');
    }

    public function appointments() {
        return $this->hasMany(Appointments::class, 'citizen_id');
    }

    public function feedbacks() {
        return $this->hasMany(Feddback::class, 'citizen_id');
    }

    public function sentMessages() {
        return $this->hasMany(Messages::class, 'sender_id');
    }

    public function receivedMessages() {
        return $this->hasMany(Messages::class, 'receiver_id');
    }

    public function notifications() {
        return $this->hasMany(Notifications::class);
    }

    public function office() {
        return $this->belongsTo(Office::class);
    }
}
