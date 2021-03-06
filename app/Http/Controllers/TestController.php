<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rules\In;
use Laravel\Lumen\Routing\Controller as BaseController;

class TestController extends BaseController
{
    public function setRedis()
    {
        $key = Input::get("key");
        $v = app('cache')->get($key);
        app('cache')->put($key,$v+1,600);

    }

    public function getRedis(){
        $key = Input::get("key");
        echo app('cache')->get($key);
    }

    public function resetRedis(){
        $key = Input::get("key");
        app('cache')->put($key,1,600);
    }

    public function curl(){
     $ch = curl_init();
     curl_setopt($ch, CURLOPT_URL, "http://a.123zzz.xyz/api/v1/getRedis?key=test");
     curl_setopt($ch, CURLOPT_HEADER, false);
     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
     curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, TRUE);
     $result=curl_exec($ch);
     print_r($result);
     curl_close($ch);
    }

}
