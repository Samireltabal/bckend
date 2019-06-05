<?php

use Dingo\Api\Routing\Router;

/** @var Router $api */
$api = app(Router::class);

$api->version('v1', function (Router $api) {
    $api->group(['prefix' => 'auth'], function(Router $api) {
        $api->post('signup', 'App\\Api\\V1\\Controllers\\SignUpController@signUp');
        $api->post('login', 'App\\Api\\V1\\Controllers\\LoginController@login');
        $api->get('signup/activate/{token}', 'App\\Api\\V1\\Controllers\\SignUpController@signupActivate');
        $api->post('recovery', 'App\\Api\\V1\\Controllers\\ForgotPasswordController@sendResetEmail');
        $api->post('reset', 'App\\Api\\V1\\Controllers\\ResetPasswordController@resetPassword');

        $api->post('logout', 'App\\Api\\V1\\Controllers\\LogoutController@logout');
        $api->post('refresh', 'App\\Api\\V1\\Controllers\\RefreshController@refresh');
        $api->get('me', 'App\\Api\\V1\\Controllers\\UserController@me');
    });    
    $api->post('pair', 'App\\Http\\Controllers\\DeviceController@pair');
    $api->post('test', 'App\\Http\\Controllers\\DeviceController@tester');
    $api->get('myDevices', 'App\\Http\\Controllers\\DeviceController@getMyDevices')->middleware('jwt.auth');
    $api->delete('removeDevice', 'App\\Http\\Controllers\\DeviceController@removeDevice')->middleware('jwt.auth','devOwner');
    $api->post('addShare', 'App\\Http\\Controllers\\DeviceController@addShare')->middleware('jwt.auth','devOwner');
    $api->post('removeShare', 'App\\Http\\Controllers\\DeviceController@removeShare')->middleware('jwt.auth','devOwner');
    $api->get('deviceLogin','App\\Http\\Controllers\\DeviceController@devLogin')->middleware('devAuth');
    $api->group(['middleware' => 'jwt.auth'], function(Router $api) {
        $api->get('protected', function() {
            return response()->json([
                'message' => 'Access to protected resources granted! You are seeing this text as you provided the token correctly.'
            ]);
        });

        $api->get('refresh', [
            'middleware' => 'jwt.refresh',
            function() {
                return response()->json([
                    'message' => 'By accessing this endpoint, you can refresh your access token at each request. Check out this response headers!'
                ]);
            }
        ]);
    });

    $api->get('hello', function() {
        return response()->json([
            'message' => 'This is a simple example of item returned by your APIs. Everyone can see it.'
        ]);
    });
});
