<?php

namespace App\Http\Middleware;

use Closure;
use Auth;
use App\Device;
class devOwner
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $deviceID = $request->device_id;
        $device = Device::Deviceid($deviceID)->first();
        if (Auth::user()->id == $device->user_id) {
            return $next($request);
        }
        else{
            return response()->json("{\"Message\": \"Unauthorised Action\"}",401);
        }
    }
}
