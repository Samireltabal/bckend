<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Device;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Auth;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Response;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class DeviceController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    /*
    *    Request to pair ( POST REQUEST SENT BY DEVICE BY User Request With User ID Attached )
    *    IF Device Is already registered in the server it will return 401 unauthorised message
    */
    public function pair(Request $request)
    {        
        $mac = $request->device_mac;
        $device_ID = str_replace(":", "", $mac);
        $current = Device::deviceid($device_ID)->first();
        if ( $current ) {
            return response()->json("{\"message\": \"Device $device_ID is already registered\"}",401);
        }
        $device = new Device;
        $device->device_mac = $request->device_mac;
        $device->internal_ip = $request->internal_ip;
        $device->device_id = $device_ID;        
        $device->type = $request->type;
        $device->user_id = $request->user_id;
        $device->token = str_random(60);
        $device->version = $request->version;        
        $device->channel = "upv" . $device_ID;
        $device->external_ip = $request->ip();
        $options = array('option1' => 'val1','option2' => 'val2' );
        $device->options = json_encode($options);
        if( $device->save() ){
            if($this->createMqttUser($device_ID,$device->token)) {
                return $device;
            }else{
                return response("{\"message\": \"device is not completly registered\" }",401); 
            }
        }else {
            return response("{\"message\": \"something gone wrong\" }",401); 
        }             
    }
    /*
    *   User Action ( Get User Devices and devices Shared By Other Users )
    *   GET REQUEST ( Auth Required )
    */
    public function getMyDevices(Request $request) {
        $devices = Auth::guard()->user()->devices;
        $arr = [];
        $arr['devices'] = [];
        foreach ($devices as $device) {
            $shareble = $device->shares;
            $arr['devices'][] = $device;                        
        }
        $arr['shares'] = Auth::guard()->user()->shares;
        return response()->json($arr);
    }        
    /*
    *   METHOD : DELETE
    *   PARAMETERS : DEVICE ID 
    *   MiddleWares : Device Owner , Auth 
    */
    public function removeDevice(Request $request) {
        $device = Device::deviceid($request->device_id)->first();
        $device->shares()->detach();
        $device->delete();
        $this->deleteMqttUser($request->device_id);
        return response()->json('success',200);
    }
    /*
    *   METHOD : TEST
    *   PARAMETERS : TEST
    *   MiddleWares : TEST 
    */
    public function tester(Request $request) {
        $mac = $request->device_mac;
        $device_ID = str_replace(":", "", $mac);
        $device = Device::Deviceid($device_ID)->first()->get();
        $resp = array('device_id'=> $device_ID);
        return $this->createMqttUser($device_ID,$mac);
        return response()->json($device,200);
    }
    /*
    *   User Function To Update Device Options 
    *
    */
    public function updateOptions(Request $request) {
        $options = $request->options;
        $device_id = $request->device_id;
        $device = Device::deviceid($device_id)->first();
        $optionsJson = json_encode($options);
        // return $optionsJson;
        // $string = json_decode($options, true);
        $device->options = $optionsJson;
        if ( $device->save() ) {
            return response()->json("{\"message\":\"successfully updated device data\"}",200);
        }else{
            return response()->json("{\"message\":\"Couldn`t save data\"}",400);
        }
    }
    /*
    *   METHOD : GET
    *   Headers : Auth Token
    *   MiddleWares : active device , active token
    *   return : Current device Data 
    */
    public function devLogin(Request $request) {
        $token = $request->header('auth');
        $device = Device::token($token)->first();
        $response = array(
            "device_id"=> $device->device_id,
            "channel"=> $device->channel,
            "options"=> json_decode($device->options),
            "last_activity" => $device->updated_at->timestamp  
        );
        $device->touch();
        return response()->json($response,200);
    }
    /*
    *   METHOD : POST
    *   PARAMETERS : EMAIL ADDRESS , DEVICE ID  
    *   MiddleWares : Device Owner , Auth 
    */
    public function addShare(Request $request) {
        $email = $request->email;
        $device_id = $request->device_id;
        $user = User::email($email)->first();
        $device = Device::deviceid($device_id)->first();
        if( $user->shares()->attach($device->id) ) {
            return response()->json('success',200);
        }else{
            return response()->json('failure',400);
        }
        $data = array('user'=>$user, 'device'=> $device);
        return response()->json($data,200);
    }
    /*
    *   METHOD : POST
    *   PARAMETERS : EMAIL ADDRESS , DEVICE ID  
    *   MiddleWares : Device Owner , Auth 
    */
    public function removeShare(Request $request) {
        $email = $request->email;
        $device_id = $request->device_id;
        $user = User::email($email)->first();
        $device = Device::deviceid($device_id)->first();
        if( $user->shares()->detach($device->id) ) {
            return response()->json('success',200);
        }else{
            return response()->json('failure',400);
        }
        $data = array('user'=>$user, 'device'=> $device);
        return response()->json($data,200);
    }
    /*
        ++ Private Functions 
    */
    // Private Function to get shares of specifiec device     
    private function getShares($deviceid) {
        $devices = new Device;
        $device = $devices::find($deviceid);
        return $device->shares;
    }
    // Private Function to Create USER IN MQTT SERVER 
    private function createMqttUser($id,$password) {
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
            RequestOptions::JSON => ['clientid' => $id , 'password' => $password]
        ]);
        if ( $res->getStatusCode() == 200) {
            return true;
        }else {
            return false;
        }
    }
    // PRIVATE FUNCTION TO DELETE MQTT USER FROM MQTT SERVER 
    private function deleteMqttUser($id) {
        $appID = env("MQTT_APP_ID");
        $appPass = env('MQTT_APP_PASS');        
        $client = new Client([
            // Base URI is used with relative requests
            'base_uri' => 'http://127.0.0.1:8080/api/v3/',
            // You can set any number of default request options.
            'timeout'  => 4.0,
            'auth' => [$appID, $appPass]
        ]);
        $res = $client->request('DELETE', "auth_clientid/$id");
        if ( $res->getStatusCode() == 200) {
            return true;
        }else {
            return false;
        }
    }
}


