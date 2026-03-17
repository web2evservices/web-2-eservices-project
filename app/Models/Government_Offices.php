<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Government_Offices extends Model
{
    public function municipality() {
        return $this->belongsTo(Users::class, 'municipality_id');
    }

    public function services() {
        return $this->hasMany(Services::class, 'office_id');
    }

    public function appointments() {
        return $this->hasMany(Appointments::class, 'office_id');
    }
}
