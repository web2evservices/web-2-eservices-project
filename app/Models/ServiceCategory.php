<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceCategory extends Model
{

    protected $table = 'service_categories';

    protected $fillable = ['name', 'description'];

    
    public function services() {
        return $this->hasMany(Services::class, 'category_id');
    }
}
