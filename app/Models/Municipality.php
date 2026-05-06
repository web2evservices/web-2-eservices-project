<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Municipality extends Model
{
    protected $fillable = ['name','city'];

    public function offices()
    {
        return $this->hasMany(Office::class);
    }
}
