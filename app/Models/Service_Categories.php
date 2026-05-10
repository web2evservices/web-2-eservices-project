<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service_Categories extends Model
{

    protected $table = 'service__categories'; // double underscore

    protected $fillable = ['name', 'description'];

    
    public function services() {
        return $this->hasMany(Services::class, 'category_id');
    }
}
