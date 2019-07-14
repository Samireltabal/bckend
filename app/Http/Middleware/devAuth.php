<?php

namespace App\Http\Middleware;

use Closure;
use App\Device;
class devAuth
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
        if ($request->header('auth')) {
            $token = $request->header('auth');
            $device = Device::token($token)->first();
            if($device)
            {
                return $next($request);
            }else{
                return response()->json('{"message": "invalid key" , "code": 401 }',401); 
            }            
        }else {
            return response()->json('{"message": "no key provided" , "code": 401 }',401);
        }
                
        // 
    }
}
