<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class mqtt_user extends Model
{
    //
    protected $table = 'mqtt_user';
    public $timestamps = false;
    public function scopeDeviceid($query, String $devid) {
        return $query->where('username',$devid);
    }
}
