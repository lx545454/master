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

//                        $harr = explode(':',$v['hvhistory']);
//                        $v['hvh'] = $harr[1];
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
                    if($k==($count-1)){
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

    public function sfc(){
        $data = [];
        $lastone = DB::table('sfc')->orderBy('created_at','desc')->first();
        //判断缓存是否有效
        if (app('cache')->has('sfc_lasttime')) {
                $lasttime = app('cache')->get('sfc_lasttime');
            if($lastone->created_at==$lasttime){
                $data = app('cache')->get('sfc_lastdata');
            }
        }

        if(!$data){
            $zq = DB::table('sfc')->where('created_at',$lastone->created_at)->get();
            $zq = json_decode(json_encode($zq), true);
            $dateArr = [];
            $data = [];
            if($zq){
                $count = count($zq);
                foreach ($zq as $k=>&$v){
                    for ($i=1;$i<=5;$i++){
                        $v['hh'].=$v['h'.$i].',';
                    }
                    $v['hh'] = substr($v['hh'],0,-1);
                    for ($i=1;$i<=5;$i++){
                        $v['gh'].=$v['v'.$i].',';
                    }
                    $v['gh'] = substr($v['gh'],0,-1);

                    $data[] = $v;

                    //缓存最后时间
                    if($k==($count-1)){
                        app('cache')->put('sfc_lasttime',$v['created_at'],60*12);
                    }
                }
                app('cache')->put('sfc_lastdata',$data,60*12);
            }
        }



        return UtilityHelper::renderJson(['data'=>$data]);
    }

    public function dc_spf(){
        $data = [];
        $lastone = DB::table('dg_sf')->orderBy('created_at','desc')->first();
        //判断缓存是否有效
        if (app('cache')->has('dg_sf_lasttime')) {
                $lasttime = app('cache')->get('dg_sf_lasttime');
            if($lastone->created_at==$lasttime){
                $data = app('cache')->get('dg_sf_lastdata');
            }
        }

        if(!$data){
            $zq = DB::table('dg_sf')->where('created_at',$lastone->created_at)->get();
            $zq = json_decode(json_encode($zq), true);
            $dateArr = [];
            $data = [];
            if($zq){
                $count = count($zq);
                foreach ($zq as $k=>&$v){
                    for ($i=1;$i<=5;$i++){
                        $v['hh'].=$v['h'.$i].',';
                    }
                    $v['hh'] = substr($v['hh'],0,-1);
                    for ($i=1;$i<=5;$i++){
                        $v['gh'].=$v['v'.$i].',';
                    }
                    $v['gh'] = substr($v['gh'],0,-1);

                    if(!in_array($v['matchcode'],$dateArr)){
                        $dateArr[] = $v['matchcode'];

                        $dateStr =  str_replace(['年','月','日',' '],['-','-','',''],$v['matchcode']);
                        $week = date('w',strtotime($dateStr));
                        $week = str_replace(['0','1','2','3','4','5','6'],['一','二','三','四','五','六','日'],$week);
                        $data[$v['matchcode']]['week'] = '周'.$week;
                        $data[$v['matchcode']]['date'] = $v['matchcode'];
                    }
                    $data[$v['matchcode']]['list'][] = $v;

                    //缓存最后时间
                    if($k==($count-1)){
                        app('cache')->put('dg_sf_lasttime',$v['created_at'],60*12);
                    }
                }
                $ARR = [];
                foreach ($dateArr as $k=>$v){
                    $ARR[] = $data[$v];
                }
                $data = $ARR;

                app('cache')->put('dg_sf_lastdata',$data,60*12);
            }
        }



        return $data;
    }

    public function dc_bf(){
        $data = [];
        $lastone = DB::table('dg_bf')->orderBy('created_at','desc')->first();
        //判断缓存是否有效
        if (app('cache')->has('dg_bf_lasttime')) {
            $lasttime = app('cache')->get('dg_bf_lasttime');
            if($lastone->created_at==$lasttime){
                $data = app('cache')->get('dg_bf_lastdata');
            }
        }

        if(!$data){
            $zq = DB::table('dg_bf')->where('created_at',$lastone->created_at)->get();
            $zq = json_decode(json_encode($zq), true);
            $dateArr = [];
            $data = [];
            if($zq){
                $count = count($zq);
                foreach ($zq as $k=>&$v){
                    for ($i=1;$i<=5;$i++){
                        $v['hh'].=$v['h'.$i].',';
                    }
                    $v['hh'] = substr($v['hh'],0,-1);
                    for ($i=1;$i<=5;$i++){
                        $v['gh'].=$v['v'.$i].',';
                    }
                    $v['gh'] = substr($v['gh'],0,-1);

                    $v['cbf'] = "";
                    for ($i=0;$i<=24;$i++){
                        $v['cbf'].=$v['bf'.($i+10)].',';
                    }
                    $v['cbf'] = substr($v['cbf'],0,-1);

                    if(!in_array($v['matchcode'],$dateArr)){
                        $dateArr[] = $v['matchcode'];

                        $dateStr =  str_replace(['年','月','日',' '],['-','-','',''],$v['matchcode']);
                        $week = date('w',strtotime($dateStr));
                        $week = str_replace(['0','1','2','3','4','5','6'],['一','二','三','四','五','六','日'],$week);
                        $data[$v['matchcode']]['week'] = '周'.$week;
                        $data[$v['matchcode']]['date'] = $v['matchcode'];
                    }
                    $data[$v['matchcode']]['list'][] = $v;

                    //缓存最后时间
                    if($k==($count-1)){
                        app('cache')->put('dg_bf_lasttime',$v['created_at'],60*12);
                    }
                }

                $ARR = [];
                foreach ($dateArr as $k=>$v){
                    $ARR[] = $data[$v];
                }
                $data = $ARR;

                app('cache')->put('dg_bf_lastdata',$data,60*12);
            }
        }



        return $data;
    }

    public function dc_sxds(){
        $data = [];
        $lastone = DB::table('dg_sxds')->orderBy('created_at','desc')->first();
        //判断缓存是否有效
//        if (app('cache')->has('dg_sxds_lasttime')) {
//            $lasttime = app('cache')->get('dg_sxds_lasttime');
//            if($lastone->created_at==$lasttime){
//                $data = app('cache')->get('dg_sxds_lastdata');
//            }
//        }

        if(!$data){
            $zq = DB::table('dg_sxds')->where('created_at',$lastone->created_at)->get();
            $zq = json_decode(json_encode($zq), true);
            $dateArr = [];
            $data = [];
            if($zq){
                $count = count($zq);
                foreach ($zq as $k=>&$v){
                    for ($i=1;$i<=5;$i++){
                        $v['hh'].=$v['h'.$i].',';
                    }
                    $v['hh'] = substr($v['hh'],0,-1);
                    for ($i=1;$i<=5;$i++){
                        $v['gh'].=$v['v'.$i].',';
                    }
                    $v['gh'] = substr($v['gh'],0,-1);

                    $v['sxds'] = $v['s1'].','.$v['s2'].','.$v['x1'].','.$v['x2'];
                    if(!in_array($v['matchcode'],$dateArr)){
                        $dateArr[] = $v['matchcode'];

                        $dateStr =  str_replace(['年','月','日',' '],['-','-','',''],$v['matchcode']);
                        $week = date('w',strtotime($dateStr));
                        $week = str_replace(['0','1','2','3','4','5','6'],['一','二','三','四','五','六','日'],$week);
                        $data[$v['matchcode']]['week'] = '周'.$week;
                        $data[$v['matchcode']]['date'] = $v['matchcode'];

                    }
                    $data[$v['matchcode']]['list'][] = $v;

                    //缓存最后时间
                    if($k==($count-1)){
                        app('cache')->put('dg_sxds_lasttime',$v['created_at'],60*12);
                    }
                }
                $ARR = [];
                foreach ($dateArr as $k=>$v){
                    $ARR[] = $data[$v];
                }
                $data = $ARR;
                app('cache')->put('dg_sxds_lastdata',$data,60*12);
            }
        }



        return $data;
    }

    public function dc_zong(){
        $data = [];
        $lastone = DB::table('dg_zong')->orderBy('created_at','desc')->first();
        //判断缓存是否有效
        if (app('cache')->has('dg_zong_lasttime')) {
            $lasttime = app('cache')->get('dg_zong_lasttime');
            if($lastone->created_at==$lasttime){
                $data = app('cache')->get('dg_zong_lastdata');
            }
        }

        if(!$data){
            $zq = DB::table('dg_zong')->where('created_at',$lastone->created_at)->get();
            $zq = json_decode(json_encode($zq), true);
            $dateArr = [];
            $data = [];
            if($zq){
                $count = count($zq);
                foreach ($zq as $k=>&$v){
                    for ($i=1;$i<=5;$i++){
                        $v['hh'].=$v['h'.$i].',';
                    }
                    $v['hh'] = substr($v['hh'],0,-1);
                    for ($i=1;$i<=5;$i++){
                        $v['gh'].=$v['v'.$i].',';
                    }
                    $v['gh'] = substr($v['gh'],0,-1);
                    $v['jqs'] = "";
                    for ($i=1;$i<=8;$i++){
                        $v['jqs'].=$v['bf'.$i].',';
                    }
                    $v['jqs'] = substr($v['jqs'],0,-1);

                    if(!in_array($v['matchcode'],$dateArr)){
                        $dateArr[] = $v['matchcode'];

                        $dateStr =  str_replace(['年','月','日',' '],['-','-','',''],$v['matchcode']);
                        $week = date('w',strtotime($dateStr));
                        $week = str_replace(['0','1','2','3','4','5','6'],['一','二','三','四','五','六','日'],$week);
                        $data[$v['matchcode']]['week'] = '周'.$week;
                        $data[$v['matchcode']]['date'] = $v['matchcode'];
                    }
                    $data[$v['matchcode']]['list'][] = $v;

                    //缓存最后时间
                    if($k==($count-1)){
                        app('cache')->put('dg_zong_lasttime',$v['created_at'],60*12);
                    }
                }

                $ARR = [];
                foreach ($dateArr as $k=>$v){
                    $ARR[] = $data[$v];
                }
                $data = $ARR;

                app('cache')->put('dg_zong_lastdata',$data,60*12);
            }
        }



        return $data;
    }
    public function dc_ban(){
        $data = [];
        $lastone = DB::table('dg_ban')->orderBy('created_at','desc')->first();
        //判断缓存是否有效
        if (app('cache')->has('dg_ban_lasttime')) {
            $lasttime = app('cache')->get('dg_ban_lasttime');
            if($lastone->created_at==$lasttime){
                $data = app('cache')->get('dg_ban_lastdata');
            }
        }

        if(!$data){
            $zq = DB::table('dg_ban')->where('created_at',$lastone->created_at)->get();
            $zq = json_decode(json_encode($zq), true);
            $dateArr = [];
            $data = [];
            if($zq){
                $count = count($zq);
                foreach ($zq as $k=>&$v){
                    for ($i=1;$i<=5;$i++){
                        $v['hh'].=$v['h'.$i].',';
                    }
                    $v['hh'] = substr($v['hh'],0,-1);
                    for ($i=1;$i<=5;$i++){
                        $v['gh'].=$v['v'.$i].',';
                    }
                    $v['gh'] = substr($v['gh'],0,-1);

                    $v['bqc'] = "";
                    for ($i=1;$i<=9;$i++){
                        $v['bqc'].=$v['bf'.$i].',';
                    }
                    $v['bqc'] = substr($v['bqc'],0,-1);

                    if(!in_array($v['matchcode'],$dateArr)){
                        $dateArr[] = $v['matchcode'];

                        $dateStr =  str_replace(['年','月','日',' '],['-','-','',''],$v['matchcode']);
                        $week = date('w',strtotime($dateStr));
                        $week = str_replace(['0','1','2','3','4','5','6'],['一','二','三','四','五','六','日'],$week);
                        $data[$v['matchcode']]['week'] = '周'.$week;
                        $data[$v['matchcode']]['date'] = $v['matchcode'];
                    }
                    $data[$v['matchcode']]['list'][] = $v;

                    //缓存最后时间
                    if($k==($count-1)){
                        app('cache')->put('dg_ban_lasttime',$v['created_at'],60*12);
                    }
                }

                $ARR = [];
                foreach ($dateArr as $k=>$v){
                    $ARR[] = $data[$v];
                }
                $data = $ARR;

                app('cache')->put('dg_ban_lastdata',$data,60*12);
            }
        }



        return $data;
    }
    public function dc(){
        $dc_sf = $this->dc_spf();
        $dc_sxds = $this->dc_sxds();
        $dc_zong = $this->dc_zong();
        $dc_bf = $this->dc_bf();
        $dc_ban = $this->dc_ban();

        $data = [
            'sf'=>$dc_sf,
            'sxds'=>$dc_sxds,
            'zong'=>$dc_zong,
            'bf'=>$dc_bf,
            'ban'=>$dc_ban,
        ];

        return UtilityHelper::renderJson($data);
    }
}