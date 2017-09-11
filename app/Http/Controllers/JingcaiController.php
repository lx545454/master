<?php
namespace App\Http\Controllers;

use App\User;
use App\Lib\Code;
use App\Lib\UtilityHelper;
use App\Lib\Request as REQ;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;

class JingcaiController extends Controller
{

    public function duizhen(){
        $body = Input::get('body','<query />');
        $cmd = Input::get('cmd',"30001");
        $res = REQ::request_xml($body,$cmd);
        if($res['result']['@attributes']['desc'] ?? ""){
            $data =  $res['body']['rows']['row'];
        }
        return UtilityHelper::renderJson(['data'=>$data]);
    }

    public function jczq(){
        $data = [];
        $lastone = DB::table('jczq')->orderBy('created_at','desc')->first();
        //判断缓存是否有效
        if (app('cache')->has('jczq_lasttime')) {
            $lasttime = app('cache')->get('jczq_lasttime');
            if($lastone->created_at==$lasttime){
                $data = app('cache')->get('jczq_lastdata');
            }
        }

        if(!$data){
            $zq = DB::table('jczq')->where('created_at',$lastone->created_at)->get();
            $zq = json_decode(json_encode($zq), true);
            $dateArr = [];
            $data = [];
            if($zq){
                $count = count($zq);
                app('cache')->put('jczq_lastcount',$count,60*12);
                foreach ($zq as $k=>&$v){
                    if($v['content']){
                        $v['content'] =preg_replace("/\s/","",$v['content']);
                        $qian = array('半全场单关胜胜','胜','平','负',',,');
                        $hou = array('',',',',',',',',');
                        $v['bqc'] = str_replace($qian,$hou,$v['content']);
                        $v['cbf'] = "";
                        $v['jqs'] = "";
                        $v['hh'] = "";
                        $v['gh'] = "";
                        for ($i=1;$i<=31;$i++){
                            $v['cbf'].=$v['bf'.$i].',';
                        }
                        $v['cbf'] = substr($v['cbf'],0,-1);
                        for ($i=32;$i<=39;$i++){
                            $v['jqs'].=$v['bf'.$i].',';
                        }
                        $v['jqs'] = substr($v['jqs'],0,-1);
                        for ($i=1;$i<=5;$i++){
                            $v['hh'].=$v['h'.$i].',';
                        }
                        $v['hh'] = substr($v['hh'],0,-1);
                        for ($i=1;$i<=5;$i++){
                            $v['gh'].=$v['v'.$i].',';
                        }
                        $v['gh'] = substr($v['gh'],0,-1);

                        $v['spf'] = $v['bet3'].','.$v['bet1'].','.$v['bet0'];
                        $v['rspf'] = $v['rang3'].','.$v['rang1'].','.$v['rang0'];
                    }
                    if(!in_array($v['date'],$dateArr)){
                        $dateArr[] = $v['date'];
                    }
                    if($k==0){
                        $data[$v['date']]['week'] = substr($v['name'],0,-3);
                        $data[$v['date']]['date'] = $v['date'];
                    }
                    $data[$v['date']]['list'][] = $v;

                    //缓存最后时间
                    if($k=$count-1){
                        app('cache')->put('jczq_lasttime',$v['created_at'],60*12);
                    }
                }
                $ARR = [];
                foreach ($dateArr as $k=>$v){
                    $ARR[] = $data[$v];
                }
                $data = $ARR;
                app('cache')->put('jczq_lastdata',$data,60*12);
            }
        }



        return UtilityHelper::renderJson(['data'=>$data]);
    }

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