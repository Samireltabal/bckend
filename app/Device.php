<?php

namespace App;
use App\User;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    //
    protected $fillable = ['version','type'];
    protected $hidden = [        
    ];
    public function user() {
        return $this->belongsTo('App\User');
    }
    public function scopeDeviceid($query, String $devid) {
        return $query->where('device_id',$devid);
    }
    public function scopeToken($query, String $devid) {
        return $query->where('token','=',$devid);
    }
    public function shares() {
        return $this->belongsToMany('App\User','user_device','device_id','user_id');
    }
}
