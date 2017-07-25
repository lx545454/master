<?php
namespace App\Http\Controllers;

use App\User;
use App\Lib\Code;
use App\Lib\UtilityHelper;
use App\Lib\Request as REQ;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

class ZhuanjiaController extends Controller
{

    public function analyst_user_detail_userinfo(Request $request)
    {
        $sub_data = Input::get();
        $sub = array('data'=>array());
        //添加方案
        $fangan = REQ::requset_all('analyst_user_recommendProject','form',$sub_data);

        if(!Input::get('type')){
            $sub = REQ::requset_all('analyst_user_detail','form',$sub_data);
        }

        if(isset($sub['data']['word_data'])){
            $fangan['data']['fangan'] = $fangan['data']['word_data'];
            $fangan['data']['word_data'] = $sub['data']['word_data'];
        }else{
            $fangan['data']['fangan'] = $fangan['data']['word_data'];
            unset($fangan['data']['word_data']);
        }
        $callback = Input::get('callback');
        return  $callback."(".\GuzzleHttp\json_encode($fangan).")";
    }

    public function analyst_project_detail_userinfo(Request $request)
    {
        $sub_data = Input::get();
        $sub = REQ::requset_all('analyst_user_detail','form',$sub_data);
        //添加方案
        $fangan = REQ::requset_all('analyst_project_detail','form',$sub_data);
        if(isset($fangan['data']['word_data'])){
            $sub['data']['fangan'] = $fangan['data']['word_data'];
        }else{
            $sub['data']['fangan'] = array();
        }
        $callback = Input::get('callback');
        return  $callback."(".\GuzzleHttp\json_encode($sub).")";
    }

    public function h5_analyst_attention_add(Request $request)
    {
        $sub_data = Input::get();
        $sub = REQ::requset_all('analyst_attention_add','form',$sub_data);

        $callback = Input::get('callback');
        return  $callback."(".\GuzzleHttp\json_encode($sub).")";
    }

}