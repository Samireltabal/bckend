<?php

namespace App\Api\V1\Controllers;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Tymon\JWTAuth\JWTAuth;
use App\Http\Controllers\Controller;
use App\Api\V1\Requests\LoginRequest;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Auth;

class LoginController extends Controller
{
    /**
     * Log the user in
     *
     * @param LoginRequest $request
     * @param JWTAuth $JWTAuth
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request, JWTAuth $JWTAuth)
    {
        $credentials = $request->only(['email', 'password']);
        $credentials['active'] = 1;
        $credentials['deleted_at'] = null;
        if ( $request->remember ) {
            try {
                $token = Auth::guard()->setTTL(432000)->attempt($credentials);

                if(!$token) {
                    throw new AccessDeniedHttpException();
                }
    
            } catch (JWTException $e) {
                throw new HttpException(500);
            }
        } else {
            try {
                $token = Auth::guard()->attempt($credentials);
    
                if(!$token) {
                    throw new AccessDeniedHttpException();
                }
    
            } catch (JWTException $e) {
                throw new HttpException(500);
            }
        }
        

        return response()
            ->json([
                'status' => 'ok',
                'token' => $token,
                'user' => Auth::user(),
                'expires_in' => Auth::guard()->factory()->getTTL() * 60
            ]);
    }
}
