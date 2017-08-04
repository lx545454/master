<?php
namespace App\Http\Controllers;

use App\User;
use App\Lib\Code;
use App\Lib\UtilityHelper;
use App\Lib\Request as REQ;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Cache;

class JobController extends Controller
{

    public function super(Request $request)
    {
        $sub_data = Input::get();
        $url = str_replace('/api/v1/','',$request->server()['REDIRECT_URL']);
        $sub = REQ::requset_all($url,'form',$sub_data);
        return $sub;
    }

    public function super_h5(Request $request)
    {
        $sub_data = Input::get();
        $url = str_replace('/api/v1/','',$request->server()['REDIRECT_URL']);
        $sub = REQ::requset_all($url,'form',$sub_data);

        $callback = Input::get('callback');
        return  $callback."(".\GuzzleHttp\json_encode($sub).")";
    }

    public function super_js(Request $request)
    {
        $sub_data = Input::get();
        $page = Input::get("page","0");
        $num = Input::get("num","10");
        if($page){
            if($page > 0){
                $sub_data['start'] = ($page-1)*$num;
            }
        }
        $sub_data['appkey'] = env('JS_APPKEY');
        $url = str_replace('/api/v1/','',$request->server()['REDIRECT_URL']);
        $url = str_replace("jsonp_","",$url);
        $sub = REQ::requset_all($url,'form',$sub_data);
        if(isset($sub['status']) && $sub['status'] == "0"){
            $sub = $sub['result'];
        }
        $url = str_replace('/api/v1/','',$request->server()['REDIRECT_URL']);
        $_url = substr($url,0,6);
        if($_url == "jsonp_"){
            $callback = $request->input('callback');
            return  $callback."(".\GuzzleHttp\json_encode($sub).")";
        }
        return UtilityHelper::renderJson($sub);
    }

}