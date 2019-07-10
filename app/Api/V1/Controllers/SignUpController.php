<?php

namespace App\Api\V1\Controllers;

use Config;
use App\User;
use Tymon\JWTAuth\JWTAuth;
use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use App\Api\V1\Requests\SignUpRequest;
use App\Notifications\SignupActivate;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SignUpController extends Controller
{
    public function signUp(SignUpRequest $request, JWTAuth $JWTAuth)
    {
        $user = new User([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'activation_token' => str_random(60),
            'role_id' => 2
        ]);
        if(!$user->save()) {
            throw new HttpException(500);
        }
        $user->notify(new SignupActivate($user));
        if(!Config::get('boilerplate.sign_up.release_token')) {
            return response()->json([
                'status' => 'ok'
            ], 201);
        }

        $token = $JWTAuth->fromUser($user);
        return response()->json([
            'status' => 'ok',
            'token' => $token
        ], 201);
    }
    public function signupActivate($token)
    {
        $user = User::where('activation_token', $token)->first();

        if (!$user) {
            return response()->json([
                'message' => 'This activation token is invalid.'
            ], 404);
        }
        $this->createMqttUser($user->email);
        $user->active = true;
        $user->activation_token = '';
        $user->save();

        return $user;
    }
    private function createMqttUser($id) {
        $appID = env("MQTT_APP_ID");
        $appPass = env('MQTT_APP_PASS');        
        $client = new Client([
            // Base URI is used with relative requests
            'base_uri' => 'http://127.0.0.1:8080/api/v3/',
            // You can set any number of default request options.
            'timeout'  => 4.0,
            'auth' => [$appID, $appPass]
        ]);
        $res = $client->request('POST', 'auth_clientid', [
            RequestOptions::JSON => ['clientid' => $id , 'password' => '12345678']
        ]);
        if ( $res->getStatusCode() == 200) {
            return true;
        }else {
            return false;
        }
    }
}
