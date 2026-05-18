<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Government_Offices extends Model
{
    protected $table = 'government_offices';

    protected $fillable = [
        'name',
        'address',
        'municipality_id',
        'working_hours',
        'contact_info',
        'latitude',
        'longitude',
        'user_id',      // the office_user who manages this office
        'status',
    ];

    public function services()
    {
        return $this->hasMany(Services::class, 'office_id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointments::class, 'office_id');
    }

    public function municipality()
    {
        return $this->belongsTo(Municipality::class, 'municipality_id');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function user()
    {
    return $this->belongsTo(User::class, 'user_id');
    }
}