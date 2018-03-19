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
        $redis = new Redis();
        $redis->connect('127.0.0.1', 6379, 1);
        $redis->incr($key);

    }

    public function getRedis(){
        $key = Input::get("key");
        $redis = new Redis();
        $redis->connect('127.0.0.1', 6379, 1);
        echo $redis->get($key);
    }

}
