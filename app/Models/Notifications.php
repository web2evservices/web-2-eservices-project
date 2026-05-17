<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notifications extends Model
{
    protected $guarded = [];

     public function user() {
        return $this->belongsTo(Users::class);
    }
}
