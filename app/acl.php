<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class acl extends Model
{
    //
    protected $table = 'mqtt_acl';
    public $timestamps = false;
    public function scopeDeviceid($query, String $devid) {
        return $query->where('username','=',$devid);
    }
}
