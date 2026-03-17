<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service_Categories extends Model
{
    public function services() {
        return $this->hasMany(Services::class, 'category_id');
    }
}
