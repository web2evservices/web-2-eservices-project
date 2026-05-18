<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Notifications extends Model
{
    protected $guarded = [];

     public function user() {
        return $this->belongsTo(Users::class);
    }
}
