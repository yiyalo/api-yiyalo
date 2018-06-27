<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;


class Car extends Model
{
    protected $table = "car";

    protected $fillable = [
        'user_id', 'title', 'details', 'cyear', 'price', 'discount', 'location'
    ];

    public function user(){
        return $this->belongsTo('App\User');
    }
}
