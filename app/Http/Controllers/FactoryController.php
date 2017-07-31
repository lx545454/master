<?php
namespace App\Http\Controllers;

use App\User;
use App\Lib\Code;
use App\Lib\UtilityHelper;
use App\Lib\Request as REQ;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

class FactoryController extends Controller
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
        $sub_data['appkey'] = env('JS_APPKEY');
        $url = str_replace('/api/v1/','',$request->server()['REDIRECT_URL']);
        $sub = REQ::requset_all($url,'form',$sub_data);
        if(isset($sub['status']) && $sub['status'] == "0"){
            $sub = $sub['result'];
        }
        return UtilityHelper::renderJson($sub);
    }

}