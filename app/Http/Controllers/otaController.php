<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;



class otaController extends Controller
{
    //
    public function index(Request $request) {
        $message = [
            "message" => "success",
            "key" => $request->header('auth'),
            "version" => $request->header('version'),
            "Agent" => $request->header('user-agent')

        ];
        if ($message["Agent"] != "ESP8266HTTPClient") {
            return response()->json('{"message": "Bad Agent" , "code": 412 }',412);
        }
        if ($message["version"] >= env("FW_VERSION") || $message["version"] == null) {
            return response()->json('{ "message": "No Update Available" , "code": 412 }',412);
        }
        return response()->json('{"message":"firmware update available" , "code": 200 }',200);
    }
}
