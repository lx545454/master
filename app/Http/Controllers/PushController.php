<?php

namespace App\Http\Controllers;
use App\Lib\PushServer;
use Illuminate\Http\Request;

class PushController extends Controller
{
    private $service;
    public function __construct()
    {
        $this->service = PushServer::getInstance();
    }

    public function push(Request $request){
        $platform = $request->input('platform', 'ALL');
        $target= $request->input('target', 'ALL');
        $targetValue= $request->input('targetValue', 'ALL');
        $title= $request->input('title', 'title');
        $content= $request->input('content', 'content');
        $pushtype= $request->input('pushtype', 'NOTICE');
        $this->service->push2app($platform,$target,$targetValue,$title,$content,$pushtype);
    }

    //
}
