<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Services extends Model
{

    protected $table = 'services';

    protected $fillable = [
        'office_id',
        'name',
        'category_id',
        'price',
        'duration',
        'required_documents',
    ];

    protected $casts = [
        'required_documents' => 'array',
    ];

    
       public function office() {
        return $this->belongsTo(Government_Offices::class, 'office_id');
    }

    public function category() {
        return $this->belongsTo(Service_Categories::class, 'category_id');
    }

    public function serviceRequests() {
        return $this->hasMany(ServiceRequests::class, 'service_id');
    }

    public function appointments() {
        return $this->hasMany(Appointments::class, 'service_id');
    }
}
