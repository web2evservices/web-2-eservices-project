<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Office extends Model
{
    protected $fillable = [
        'municipality_id',
        'name',
        'email',
        'phone',
        'address',
        'working_hours',
        'contact_info',
        'latitude',
        'longitude',
        'is_active'
    ];

    public function municipality()
    {
        return $this->belongsTo(Municipality::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
