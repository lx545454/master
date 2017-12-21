<?php
namespace App\Http\Controllers;

use App\App_version;
use App\Lib\Logs;
use App\Lunbotu;
use App\Lib\Code;
use App\Lib\UtilityHelper;
use App\Lib\Request as REQ;
use App\Xiaoxi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Log;
use Mockery\CountValidator\Exception;

class AppController extends Controller
{

    public function get_version(Request $request)
    {
        $m_type = $request->input('m_type','');
        $merchant_id = $request->input('merchant_id','');

        $query = App_version::query();
        if ($m_type) $query = $query->where('m_type', $m_type);
        if ($merchant_id) $query = $query->where('merchant_id', $merchant_id);
        $app = $query->first();
        $data['data'] = $app;

        return UtilityHelper::renderJson($data, 0, '');
    }

    public function get_cqssc(Request $request)
    {
        $sub_data['caipiaoid'] = "73";
        $sub_data['appkey'] = env('JS_APPKEY');
        $sub_data['num'] = "100";
        $res = REQ::requset_all('caipiao_history','form',$sub_data);
        if(isset($res['status']) && $res['status'] == "0"){
            return UtilityHelper::renderJson($res, 0, '');
        }
    }

    public function get_cqssc2(Request $request)
    {
        $sub_data['caipiaoid'] = "73";
        $sub_data['appkey'] = env('JS_APPKEY');
        $sub_data['num'] = $request['num'] ?? 1;
        $sub_data['start'] = $request['start'] ?? 0;
        $all = array();
        $alls = array();
        while (true){
            $res = REQ::requset_all('caipiao_history','form',$sub_data);
            echo $sub_data['start'];
            if(isset($res['status']) && isset($res['result']['list'])){

                foreach ($res['result']['list'] as $k=>$v){
                    $v['number'] = str_replace(' ','',$v['number']);
                    $res = DB::table("cqssc3")->insert([
                        'qici'=>$v['issueno'],
                        'num'=>$v['number'],
                        'opendate'=>$v['opendate'],
                    ]);

                    if(!in_array($v,$all)){
                        $all[] = $v;
                    }

                    $alls[] = $v;
                }
                $sub_data['start'] +=$request['num'];
            }else{
                break;
            }

        }

//        foreach ($all as $k=>$v){
//            $count = DB::table('cqssc3')->where("num",'=',$v['number'])->count();
//            DB::table('cqssc3')->where("num",'=',$v['number'])->update([
//                'cf' => $count
//            ]);
//        }
        return UtilityHelper::renderJson($res, 0, '一共有：'.count($all));

    }


    public function get_cqssc3(Request $request)
    {
        $sub_data['caipiaoid'] = "73";
        $sub_data['appkey'] = env('JS_APPKEY');
        $sub_data['num'] = "20";
        $sub_data['start'] = $request['start'] ?? 0;
        $all = array();
        $res = REQ::requset_all('caipiao_history','form',$sub_data);
        if(isset($res['status']) && $res['status'] == "0" && isset($res['result']['list'])){
            foreach ($res['result']['list'] as $k=>$v){
                $v['number'] = str_replace(' ','',$v['number']);
                try{
                    $res = DB::table("cqssc3")->insert([
                        'qici'=>$v['issueno'],
                        'num'=>$v['number'],
                        'opendate'=>$v['opendate'],
                    ]);
                }catch (Exception $e){
                    Logs::debug('cqssc','add_cqssc_list=======>'.$e->getMessage());
                }


                if(!in_array($v,$all)){
                    $all[] = $v;
                }
            }
        }

//        foreach ($all as $k=>$v){
//            $count = DB::table('cqssc3')->where("num",'=',$v['number'])->count();
//            DB::table('cqssc3')->where("num",'=',$v['number'])->update([
//                'cf' => $count
//            ]);
//        }
        return UtilityHelper::renderJson($res, 0, '一共有：'.count($all));

    }

    public function get_cqssc_history(Request $request)
    {
        $res = DB::table('cqssc3')->groupBy("num")->get();
        $numArr = array();
        foreach ($res as $k=>$v){
            $numArr[] = $v->num;
        }
        return UtilityHelper::renderJson($numArr, 0, '');
    }

    public function get_ssc_history(Request $request)
    {
        $qici = $request['qici'] ?? "0";
        $num = $request['num'] ?? 1;
        $xxxx = $request['xxxx'] ?? ">";

        $res = DB::table('cqssc3')->where('qici',$xxxx,$qici)->orderBy('qici','desc')->limit($num)->get();
        $data = array();
        $data['data'] = $res;

        $numArr = array();
        foreach ($res as $k=>$v){
            $numArr[] = $v->num;
        }
        $data['numArr'] = $numArr;

        return UtilityHelper::renderJson($data, 0, '');
    }

    public function get_history_2x_list(Request $request){
        $data = array();
        $request['count'] = $request['count'] ??  30;
        $request['startDate'] = $request['startDate'] ??  date("Y-m-d");
        $request['endDate'] = $request['endDate'] ?? date("Y-m-d",strtotime("+1 day"));
        $res = DB::table('cqssc3')->where('opendate','>',$request['startDate'])->where('opendate','<',$request['endDate'])->orderBy('qici')->get()->toArray();
        $data['dui'] = 0;
        if($res){
            foreach ($res as $k=>$v){
                $request['qici'] = $v->qici;
                $one = self::get_history_2x_one($request);
                $data[] = $one;
                if($one['zai'] == 1){
                    $data['dui']++;
                }

            }
        }
        return UtilityHelper::renderJson($data, 0, '');


    }

    public function get_history_2x(Request $request){
        $data = self::get_history_2x_one($request);
        return UtilityHelper::renderJson($data, 0, '');
    }


    public function get_history_2x_one(Request $request)
    {
        $count = $request['count'] ?? 30;
        $qici = $request['qici'] ?? false;

        if($qici){
            $data = DB::table('cqssc3')->where('qici','=',$qici)->first();
            if(!$data) return UtilityHelper::renderJson([], 0, "该期次不存在");
            $num = $data->num;
        }else{
            $res = REQ::requset_all("alicp_query",'auth',['caipiaoid'=>'73']);
            $xiabiao = strpos($res,'{');
            $res = \GuzzleHttp\json_decode(substr($res,$xiabiao),true);
            $data = $res['result'] ?? false;
            if(!$data) return UtilityHelper::renderJson([], 0, "阿里云接口失效");
            $num = str_replace(' ','',$data['number']);
            $qici = $data['issueno'];
        }


        $str1 = substr($num,0,3);
        $str2 = substr($num,2,3);

        $res1 = DB::table('cqssc3')->where('num','like',$str1."%")->where('qici','<',$qici)->orderBy('qici','desc')->first();
        $res2 = DB::table('cqssc3')->where('num','like',"%".$str2)->where('qici','<',$qici)->orderBy('qici','desc')->first();



        $qici1 = $res1->qici;
        $qici2 = $res2->qici;

        $res_1 = DB::table('cqssc3')->where('qici','>',$qici1)->orderBy('qici')->limit($count)->get()->toArray();
        $res_2 = DB::table('cqssc3')->where('qici','>',$qici2)->orderBy('qici')->limit($count)->get()->toArray();

        $arr1 = array();
        foreach ($res_1 as $k=>$v){
            for ($i=0;$i<4;$i++){
                $str = substr($v->num,$i,2);
                if(!in_array($str,$arr1)){
                    $arr1[] = $str;
                }
            }
        }

        $arr2 = array();
        foreach ($res_2 as $k=>$v){
            for ($i=0;$i<4;$i++){
                $str = substr($v->num,$i,2);
                if(!in_array($str,$arr2)){
                    $arr2[] = $str;
                }
            }
        }
//        print_r($res1);print_r($res_1);die;
        $output = array();
        $output['arr1'] = $arr1;
        $output['arr2'] = $arr2;
        $output['qici'] = $qici;
        $output['num'] = $num;

        //下一期
        $res3 = DB::table('cqssc3')->where('qici','>',$qici)->orderBy('qici')->first();
        if($res3){
//            $qici3 = $res3->qici;
            $str3_1 = substr($res3->num,0,2);
            $str3_2 = substr($res3->num,1,2);
            $str3_3 = substr($res3->num,2,2);
            $str3_4 = substr($res3->num,3,2);
            if(in_array($str3_1,$arr1) && in_array($str3_2,$arr1)){
                $output['zai1'] = 1;
            }else{
                $output['zai1'] = 2;
            }
            if(in_array($str3_3,$arr2) && in_array($str3_4,$arr2)){
                $output['zai2'] = 1;
            }else{
                $output['zai2'] = 2;
            }
//            if(in_array($str3_1,$arr1) && in_array($str3_2,$arr1)){
//                $output['zai1'] = 1;
//            }else{
//                $output['zai1'] = 2;
//            }
        }else{
            $output['zai'] = 0;
            $output['zai1'] = 0;
            $output['zai2'] = 0;
        }

        return $output;
    }

    public function get_history_3x(Request $request){
        $data = self::get_history_3x_one($request);
        return UtilityHelper::renderJson($data, 0, '');
    }

    public function get_history_3x_3x(Request $request){
        $data = self::get_history_3x_3x_one($request);
        return UtilityHelper::renderJson($data, 0, '');
    }

    public function get_demo(Request $request)
    {
        $updown = $request['updown'] ?? false;
        $count = $request['count'] ?? 1;
        $dui = $request['dui'] ?? false;
        $startNum = $request['startNum'] ?? false;
        $endNum = $request['endNum'] ?? false;
        $qicis = $request['qicis'] ?? false;

        $qiciArr = explode(',',$qicis);
        $output = array();
        foreach ($qiciArr as $k=>$v){
            $data = DB::table('cqssc3')->where('qici','=',$v)->first();
            if(!$data){
                return UtilityHelper::renderJson([], 0, $v,'无效');
            }

            $sql="select * from t_cqssc3 where 1=1 ";

            if($startNum && $endNum){
                $str = substr($data->num,($startNum-1),$endNum);
                if($dui){
                    $sql .= " and SUBSTR(num,$startNum,$endNum)=$str ";
                }else{
                    $sql .= " and num like '%$str%' ";

                }
            }
            if($updown){
                $sql .= " and qici<'$v' order by qici desc limit $count ";
            }else{
                $sql .= " and qici>'$v' order by qici limit $count ";
            }
            $res = DB::select($sql);
            if($res){
                $output[$v] = $res;
            }
        }

        return UtilityHelper::renderJson($output, 0, '');

    }

    public function get_3x_check_create(Request $request)
    {
        $count1 = $request['count1'] ?? 15;
        $count2 = $request['count2'] ?? 15;
        $count3 = $request['count3'] ?? 15;
        $count_c = $request['count_c'] ?? 10;
        $count_mohu = $request['count_mohu'] ?? 3;
        $qici = $request['qici'] ?? false;

        if($qici){
            $data = DB::table('cqssc3')->where('qici','=',$qici)->first();
            if(!$data) return UtilityHelper::renderJson([], 0, "该期次不存在");
            $num = $data->num;
        }else{
            $res = REQ::requset_all("alicp_query",'auth',['caipiaoid'=>'73']);
            $xiabiao = strpos($res,'{');
            $res = \GuzzleHttp\json_decode(substr($res,$xiabiao),true);
            $data = $res['result'] ?? false;
            if(!$data) return UtilityHelper::renderJson([], 0, "阿里云接口失效");
            $num = str_replace(' ','',$data['number']);
            $qici = $data['issueno'];
        }
        Logs::debug('3x3x',$qici);

        $str1 = substr($num,0,3);
        $str2 = substr($num,1,3);
        $str3 = substr($num,2,3);

        //取最近一次对位号码
        $res_d1 = DB::table('cqssc3')->where('num','like',$str2.'%')->where('qici','<',$qici)->orderBy('qici','desc')->first();
        $res_d2 = DB::select("select * from t_cqssc3 where SUBSTR(num,2,3)=".$str2." and qici<'".$qici."' order by qici desc limit 1");
        $res_d3 = DB::table('cqssc3')->where('num','like',"%".$str3)->where('qici','<',$qici)->orderBy('qici','desc')->first();
        //取对位下期开奖结果
        $res_d1_n = DB::table('cqssc3')->where('qici','>',$res_d1->qici)->orderBy('qici')->first();
        $res_d2_n = DB::table('cqssc3')->where('qici','>',$res_d2->qici)->orderBy('qici')->first();
        $res_d3_n = DB::table('cqssc3')->where('qici','>',$res_d3->qici)->orderBy('qici')->first();
        $res_d1_n_str1 = substr($res_d1_n->num,0,3);
        $res_d1_n_str2 = substr($res_d1_n->num,1,3);
        $res_d1_n_str3 = substr($res_d1_n->num,2,3);
        $res_d2_n_str1 = substr($res_d2_n->num,0,3);
        $res_d2_n_str2 = substr($res_d2_n->num,1,3);
        $res_d2_n_str3 = substr($res_d2_n->num,2,3);
        $res_d3_n_str1 = substr($res_d3_n->num,0,3);
        $res_d3_n_str2 = substr($res_d3_n->num,1,3);
        $res_d3_n_str3 = substr($res_d3_n->num,2,3);
        //对位期前三期 无需对位
        $res_d1_res1 = DB::table('cqssc3')->where('num','like',"%".$str1."%")->where('qici','>',$res_d1->qici)->orderBy('qici','desc')->limit($count_mohu)->get()->toArray();
        $res_d1_res2 = DB::table('cqssc3')->where('num','like',"%".$str2."%")->where('qici','>',$res_d2->qici)->orderBy('qici','desc')->limit($count_mohu)->get()->toArray();
        $res_d1_res3 = DB::table('cqssc3')->where('num','like',"%".$str3."%")->where('qici','>',$res_d1->qici)->orderBy('qici','desc')->limit($count_mohu)->get()->toArray();

        $res_d1_res1_arr1 = array();
        foreach ($res_d1_res1 as $k3=>$v3){
            $qici_d = $v3->qici;
            $res_1 = DB::table('cqssc3')->where('qici','>',$qici_d)->orderBy('qici')->limit($count_c)->get()->toArray();
            $res_d1_res1_arr1 = array();
            foreach ($res_1 as $k=>$v){
                for ($i=0;$i<5;$i++){
                    for ($j=0;$j<5;$j++){
                        if($i==$j){
                            continue;
                        }else{
                            $str_a = $v->num[$i].$v->num[$j];
                            if(!in_array($str_a,$res_d1_res1_arr1)){
                                $res_d1_res1_arr1 = $str_a;
                            }
                        }

                    }
                }
            }
        }

        $res_d1_res1_arr2 = array();
        foreach ($res_d1_res2 as $k3=>$v3){
            $qici_d = $v3->qici;
            $res_1 = DB::table('cqssc3')->where('qici','>',$qici_d)->orderBy('qici')->limit($count_c)->get()->toArray();
            $res_d1_res1_arr2 = array();
            foreach ($res_1 as $k=>$v){
                for ($i=0;$i<5;$i++){
                    for ($j=0;$j<5;$j++){
                        if($i==$j){
                            continue;
                        }else{
                            $str_a = $v->num[$i].$v->num[$j];
                            if(!in_array($str_a,$res_d1_res1_arr2)){
                                $res_d1_res1_arr2 = $str_a;
                            }
                        }

                    }
                }
            }
        }

        $res_d1_res1_arr3 = array();
        foreach ($res_d1_res3 as $k3=>$v3){
            $qici_d = $v3->qici;
            $res_1 = DB::table('cqssc3')->where('qici','>',$qici_d)->orderBy('qici')->limit($count_c)->get()->toArray();
            $res_d1_res1_arr3 = array();
            foreach ($res_1 as $k=>$v){
                for ($i=0;$i<5;$i++){
                    for ($j=0;$j<5;$j++){
                        if($i==$j){
                            continue;
                        }else{
                            $str_a = $v->num[$i].$v->num[$j];
                            if(!in_array($str_a,$res_d1_res1_arr3)){
                                $res_d1_res1_arr3 = $str_a;
                            }
                        }

                    }
                }
            }
        }

        $res1 = DB::table('cqssc3')->where('num','like',"%".$str1."%")->where('qici','<',$qici)->orderBy('qici','desc')->limit(3)->get()->toArray();
        $res2 = DB::table('cqssc3')->where('num','like',"%".$str2."%")->where('qici','<',$qici)->orderBy('qici','desc')->limit(3)->get()->toArray();
        $res3 = DB::table('cqssc3')->where('num','like',"%".$str3."%")->where('qici','<',$qici)->orderBy('qici','desc')->limit(3)->get()->toArray();


        $arr1 = array();
        foreach ($res1 as $k3=>$v3){
            $qici1 = $v3->qici;
            $res_1 = DB::table('cqssc3')->where('qici','>',$qici1)->orderBy('qici')->limit($count1)->get()->toArray();
            $arr1[$k3]['a'] = array();
            $arr1[$k3]['b'] = array();
            foreach ($res_1 as $k=>$v){
                for ($i=0;$i<5;$i++){
                    for ($j=0;$j<5;$j++){
                        if($i>=$j){
                            continue;
                        }else{
                            $str_a = $v->num[$i].$v->num[$j];
                            $str_b = $v->num[$j].$v->num[$i];
                            if(!in_array($str_a,$arr1[$k3]['a'])){
                                $arr1[$k3]['a'][] = $str_a;
                                $arr1[$k3]['b'][] = $str_b;
                            }
                        }

                    }
                }
            }
            sort($arr1[$k3]['a']);
            sort($arr1[$k3]['b']);
        }

        $arr2 = array();
        foreach ($res2 as $k3=>$v3){
            $qici1 = $v3->qici;
            $res_1 = DB::table('cqssc3')->where('qici','>',$qici1)->orderBy('qici')->limit($count1)->get()->toArray();
            $arr2[$k3]['a'] = array();
            $arr2[$k3]['b'] = array();
            foreach ($res_1 as $k=>$v){
                for ($i=0;$i<5;$i++){
                    for ($j=0;$j<5;$j++){
                        if($i>=$j){
                            continue;
                        }else{
                            $str_a = $v->num[$i].$v->num[$j];
                            $str_b = $v->num[$j].$v->num[$i];
                            if(!in_array($str_a,$arr2[$k3]['a'])){
                                $arr2[$k3]['a'][] = $str_a;
                                $arr2[$k3]['b'][] = $str_b;
                            }
                        }

                    }
                }
            }
            sort($arr2[$k3]['a']);
            sort($arr2[$k3]['b']);
        }

        $arr3 = array();
        foreach ($res3 as $k3=>$v3){
            $qici1 = $v3->qici;
            $res_1 = DB::table('cqssc3')->where('qici','>',$qici1)->orderBy('qici')->limit($count1)->get()->toArray();
            $arr3[$k3]['a'] = array();
            $arr3[$k3]['b'] = array();
            foreach ($res_1 as $k=>$v){
                for ($i=0;$i<5;$i++){
                    for ($j=0;$j<5;$j++){
                        if($i>=$j){
                            continue;
                        }else{
                            $str_a = $v->num[$i].$v->num[$j];
                            $str_b = $v->num[$j].$v->num[$i];
                            if(!in_array($str_a,$arr3[$k3]['a'])){
                                $arr3[$k3]['a'][] = $str_a;
                                $arr3[$k3]['b'][] = $str_b;
                            }
                        }

                    }
                }
            }
            sort($arr3[$k3]['a']);
            sort($arr3[$k3]['b']);
        }






//        print_r($res1);print_r($res_1);die;
        $output = array();

        $output['arr1'] = $arr1;
        $output['arr2'] = $arr2;
        $output['arr3'] = $arr3;



        $output['qici'] = $qici;
        $output['num'] = $num;

        //下一期
        $res_n = DB::table('cqssc3')->where('qici','>',$qici)->orderBy('qici')->first();
        if($res_n){
            $str_n_1 = substr($res_n->num,0,2);
            $str_n_2 = substr($res_n->num,1,2);
            $str_n_3 = substr($res_n->num,2,2);
            $str_n_4 = substr($res_n->num,3,2);
            $wb = $res_n->num[0].$res_n->num[2];
            $qs = $res_n->num[1].$res_n->num[3];
            $bg = $res_n->num[2].$res_n->num[4];
            foreach ($output['arr1'] as $k=>&$v){
                if(in_array($str_n_1,$v['a']) && in_array($str_n_2,$v['a']) && in_array($wb,$v['a'])){
                    $v['zai'] = 1;
                }else{
                    $v['zai'] = 2;
                }
            }

            foreach ($output['arr2'] as $k=>&$v){
                if(in_array($str_n_2,$v['a']) && in_array($str_n_3,$v['a']) && in_array($qs,$v['a'])){
                    $v['zai'] = 1;
                }else{
                    $v['zai'] = 2;
                }
            }

            foreach ($output['arr3'] as $k=>&$v){
                if(in_array($str_n_3,$v['a']) && in_array($str_n_4,$v['a']) && in_array($bg,$v['a'])){
                    $v['zai'] = 1;
                }else{
                    $v['zai'] = 2;
                }
            }

        }
        Logs::debug('3x3xend',$output['num']);

        return $output;
    }

    public function get_history_3x_3x_one(Request $request)
    {
        $count1 = $request['count1'] ?? 15;
        $count2 = $request['count2'] ?? 15;
        $count3 = $request['count3'] ?? 15;
        $qici = $request['qici'] ?? false;

        if($qici){
            $data = DB::table('cqssc3')->where('qici','=',$qici)->first();
            if(!$data) return UtilityHelper::renderJson([], 0, "该期次不存在");
            $num = $data->num;
        }else{
            $res = REQ::requset_all("alicp_query",'auth',['caipiaoid'=>'73']);
            $xiabiao = strpos($res,'{');
            $res = \GuzzleHttp\json_decode(substr($res,$xiabiao),true);
            $data = $res['result'] ?? false;
            if(!$data) return UtilityHelper::renderJson([], 0, "阿里云接口失效");
            $num = str_replace(' ','',$data['number']);
            $qici = $data['issueno'];
        }
        Logs::debug('3x3x',$qici);

        $str1 = substr($num,0,3);
        $str2 = substr($num,1,3);
        $str3 = substr($num,2,3);

        $res1 = DB::table('cqssc3')->where('num','like',"%".$str1."%")->where('qici','<',$qici)->orderBy('qici','desc')->limit(3)->get()->toArray();
        $res2 = DB::table('cqssc3')->where('num','like',"%".$str2."%")->where('qici','<',$qici)->orderBy('qici','desc')->limit(3)->get()->toArray();
        $res3 = DB::table('cqssc3')->where('num','like',"%".$str3."%")->where('qici','<',$qici)->orderBy('qici','desc')->limit(3)->get()->toArray();
//        $res2 = DB::table('cqssc3')->where('num','like',"%".$str2.'%')->where('qici','<',$qici)->orderBy('qici','desc')->first();
//        $res2 = DB::select("select * from t_cqssc3 where SUBSTR(num,2,3)=".$str2." and qici<'".$qici."' order by qici desc limit 3");
//        $res3 = DB::table('cqssc3')->where('num','like',"%".$str3)->where('qici','<',$qici)->orderBy('qici','desc')->limit(3)->get()->toArray();

        $arr1 = array();
        foreach ($res1 as $k3=>$v3){
            $qici1 = $v3->qici;
            $res_1 = DB::table('cqssc3')->where('qici','>',$qici1)->orderBy('qici')->limit($count1)->get()->toArray();
            $arr1[$k3]['a'] = array();
            $arr1[$k3]['b'] = array();
            foreach ($res_1 as $k=>$v){
                for ($i=0;$i<5;$i++){
                    for ($j=0;$j<5;$j++){
                        if($i>=$j){
                            continue;
                        }else{
                            $str_a = $v->num[$i].$v->num[$j];
                            $str_b = $v->num[$j].$v->num[$i];
                            if(!in_array($str_a,$arr1[$k3]['a'])){
                                $arr1[$k3]['a'][] = $str_a;
                                $arr1[$k3]['b'][] = $str_b;
                            }
                        }

                    }
                }
            }
            sort($arr1[$k3]['a']);
            sort($arr1[$k3]['b']);
        }

        $arr2 = array();
        foreach ($res2 as $k3=>$v3){
            $qici1 = $v3->qici;
            $res_1 = DB::table('cqssc3')->where('qici','>',$qici1)->orderBy('qici')->limit($count2)->get()->toArray();
            $arr2[$k3]['a'] = array();
            $arr2[$k3]['b'] = array();
            foreach ($res_1 as $k=>$v){
                for ($i=0;$i<5;$i++){
                    for ($j=0;$j<5;$j++){
                        if($i>=$j){
                            continue;
                        }else{
                            $str_a = $v->num[$i].$v->num[$j];
                            $str_b = $v->num[$j].$v->num[$i];
                            if(!in_array($str_a,$arr2[$k3]['a'])){
                                $arr2[$k3]['a'][] = $str_a;
                                $arr2[$k3]['b'][] = $str_b;
                            }
                        }

                    }
                }
            }
            sort($arr2[$k3]['a']);
            sort($arr2[$k3]['b']);
        }

        $arr3 = array();
        foreach ($res3 as $k3=>$v3){
            $qici1 = $v3->qici;
            $res_1 = DB::table('cqssc3')->where('qici','>',$qici1)->orderBy('qici')->limit($count3)->get()->toArray();
            $arr3[$k3]['a'] = array();
            $arr3[$k3]['b'] = array();
            foreach ($res_1 as $k=>$v){
                for ($i=0;$i<5;$i++){
                    for ($j=0;$j<5;$j++){
                        if($i>=$j){
                            continue;
                        }else{
                            $str_a = $v->num[$i].$v->num[$j];
                            $str_b = $v->num[$j].$v->num[$i];
                            if(!in_array($str_a,$arr3[$k3]['a'])){
                                $arr3[$k3]['a'][] = $str_a;
                                $arr3[$k3]['b'][] = $str_b;
                            }
                        }

                    }
                }
            }
            sort($arr3[$k3]['a']);
            sort($arr3[$k3]['b']);
        }






//        print_r($res1);print_r($res_1);die;
        $output = array();

        $output['arr1'] = $arr1;
        $output['arr2'] = $arr2;
        $output['arr3'] = $arr3;



        $output['qici'] = $qici;
        $output['num'] = $num;

        //下一期
        $res_n = DB::table('cqssc3')->where('qici','>',$qici)->orderBy('qici')->first();
        if($res_n){
            $str_n_1 = substr($res_n->num,0,2);
            $str_n_2 = substr($res_n->num,1,2);
            $str_n_3 = substr($res_n->num,2,2);
            $str_n_4 = substr($res_n->num,3,2);
            $wb = $res_n->num[0].$res_n->num[2];
            $qs = $res_n->num[1].$res_n->num[3];
            $bg = $res_n->num[2].$res_n->num[4];
            foreach ($output['arr1'] as $k=>&$v){
                if(in_array($str_n_1,$v['a']) && in_array($str_n_2,$v['a']) && in_array($wb,$v['a'])){
                    $v['zai'] = 1;
                }else{
                    $v['zai'] = 2;
                }
            }

            foreach ($output['arr2'] as $k=>&$v){
                if(in_array($str_n_2,$v['a']) && in_array($str_n_3,$v['a']) && in_array($qs,$v['a'])){
                    $v['zai'] = 1;
                }else{
                    $v['zai'] = 2;
                }
            }

            foreach ($output['arr3'] as $k=>&$v){
                if(in_array($str_n_3,$v['a']) && in_array($str_n_4,$v['a']) && in_array($bg,$v['a'])){
                    $v['zai'] = 1;
                }else{
                    $v['zai'] = 2;
                }
            }

        }
        Logs::debug('3x3xend',$output['num']);

        return $output;
    }

    public function get_history_3x_one(Request $request)
    {
        $count = $request['count'] ?? 15;
        $qici = $request['qici'] ?? false;

        if($qici){
            $data = DB::table('cqssc3')->where('qici','=',$qici)->first();
            if(!$data) return UtilityHelper::renderJson([], 0, "该期次不存在");
            $num = $data->num;
        }else{
            $res = REQ::requset_all("alicp_query",'auth',['caipiaoid'=>'73']);
            $xiabiao = strpos($res,'{');
            $res = \GuzzleHttp\json_decode(substr($res,$xiabiao),true);
            $data = $res['result'] ?? false;
            if(!$data) return UtilityHelper::renderJson([], 0, "阿里云接口失效");
            $num = str_replace(' ','',$data['number']);
            $qici = $data['issueno'];
        }
        Logs::debug('3x',$qici);

        $str1 = substr($num,0,3);
        $str2 = substr($num,1,3);
        $str3 = substr($num,2,3);

        $res1 = DB::table('cqssc3')->where('num','like',$str1."%")->where('qici','<',$qici)->orderBy('qici','desc')->first();
//        $res2 = DB::table('cqssc3')->where('num','like',"%".$str2.'%')->where('qici','<',$qici)->orderBy('qici','desc')->first();
        $res2 = DB::select("select * from t_cqssc3 where SUBSTR(num,2,3)=".$str2." and qici<'".$qici."' order by qici desc limit 1");
        $res3 = DB::table('cqssc3')->where('num','like',"%".$str3)->where('qici','<',$qici)->orderBy('qici','desc')->first();


        $qici1 = $res1->qici;
        $qici2 = $res2[0]->qici;
        $qici3 = $res3->qici;

        $res_1 = DB::table('cqssc3')->where('qici','>',$qici1)->orderBy('qici')->limit($count)->get()->toArray();
        $res_2 = DB::table('cqssc3')->where('qici','>',$qici2)->orderBy('qici')->limit($count)->get()->toArray();
        $res_3 = DB::table('cqssc3')->where('qici','>',$qici3)->orderBy('qici')->limit($count)->get()->toArray();

        $arr1 = array();
        foreach ($res_1 as $k=>$v){
            for ($i=0;$i<5;$i++){
                for ($j=0;$j<5;$j++){
                    if($i==$j){
                        continue;
                    }else{
                        $str = $v->num[$i].$v->num[$j];
                        if(!in_array($str,$arr1)){
                            $arr1[] = $str;
                        }
                    }

                }
            }
        }

        $arr2 = array();
        foreach ($res_2 as $k=>$v){
            for ($i=0;$i<5;$i++){
                for ($j=0;$j<5;$j++){
                    if($i==$j){
                        continue;
                    }else{
                        $str = $v->num[$i].$v->num[$j];
                        if(!in_array($str,$arr2)){
                            $arr2[] = $str;
                        }
                    }

                }
            }
        }

        $arr3 = array();
        foreach ($res_3 as $k=>$v){
            for ($i=0;$i<5;$i++){
                for ($j=0;$j<5;$j++){
                    if($i==$j){
                        continue;
                    }else{
                        $str = $v->num[$i].$v->num[$j];
                        if(!in_array($str,$arr3)){
                            $arr3[] = $str;
                        }
                    }

                }
            }
        }

        $arr1_7 = array();
        foreach ($res_1 as $k=>$v){
            $arr1_7[] = substr($v->num,0,2);
            $arr1_7[] = substr($v->num,1,2);
            $arr1_7[] = substr($v->num,2,2);
            $arr1_7[] = substr($v->num,3,2);
            $arr1_7[] = $v->num[0].$v->num[2];
            $arr1_7[] = $v->num[1].$v->num[3];
            $arr1_7[] = $v->num[2].$v->num[4];
        }

        $arr2_7 = array();
        foreach ($res_2 as $k=>$v){
            $arr2_7[] = substr($v->num,0,2);
            $arr2_7[] = substr($v->num,1,2);
            $arr2_7[] = substr($v->num,2,2);
            $arr2_7[] = substr($v->num,3,2);
            $arr2_7[] = $v->num[0].$v->num[2];
            $arr2_7[] = $v->num[1].$v->num[3];
            $arr2_7[] = $v->num[2].$v->num[4];
        }

        $arr3_7 = array();
        foreach ($res_3 as $k=>$v){
            $arr3_7[] = substr($v->num,0,2);
            $arr3_7[] = substr($v->num,1,2);
            $arr3_7[] = substr($v->num,2,2);
            $arr3_7[] = substr($v->num,3,2);
            $arr3_7[] = $v->num[0].$v->num[2];
            $arr3_7[] = $v->num[1].$v->num[3];
            $arr3_7[] = $v->num[2].$v->num[4];
        }


//        print_r($res1);print_r($res_1);die;
        $output = array();

        $output['arr1'] = $arr1;
        $output['arr2'] = $arr2;
        $output['arr3'] = $arr3;
        $output['arrAll'] = array_intersect($arr1,$arr2,$arr3);
        sort($output['arr1']);
        sort($output['arr2']);
        sort($output['arr3']);
        sort($output['arrAll']);

        $output['arr1_7'] = array_unique($arr1_7);
        $output['arr2_7'] = array_unique($arr2_7);
        $output['arr3_7'] = array_unique($arr3_7);
        $output['arrAll_7'] = array_intersect($arr1_7,$arr2_7,$arr3_7);
        sort($output['arr1_7']);
        sort($output['arr2_7']);
        sort($output['arr3_7']);
        sort($output['arrAll_7']);

        $output['qici'] = $qici;
        $output['num'] = $num;

        //下一期
        $res_n = DB::table('cqssc3')->where('qici','>',$qici)->orderBy('qici')->first();
        if($res_n){
            $str_n_1 = substr($res_n->num,0,2);
            $str_n_2 = substr($res_n->num,1,2);
            $str_n_3 = substr($res_n->num,2,2);
            $str_n_4 = substr($res_n->num,3,2);
            $wb = $res_n->num[0].$res_n->num[2];
            $qs = $res_n->num[1].$res_n->num[3];
            $bg = $res_n->num[2].$res_n->num[4];
            if(in_array($str_n_1,$output['arrAll']) && in_array($str_n_2,$output['arrAll']) &&in_array($str_n_3,$output['arrAll']) &&in_array($str_n_4,$output['arrAll']) ){
                $output['zai'] = 1;
            }else{
                $output['zai'] = 2;
            }
            if(in_array($str_n_1,$output['arr1']) && in_array($str_n_2,$output['arr1']) && in_array($wb,$output['arr1'])){
                $output['zai1'] = 1;
            }else{
                $output['zai1'] = 2;
            }
            if(in_array($str_n_2,$output['arr2']) && in_array($str_n_3,$output['arr2']) && in_array($qs,$output['arr2'])){
                $output['zai2'] = 1;
            }else{
                $output['zai2'] = 2;
            }
            if(in_array($str_n_3,$output['arr3']) && in_array($str_n_4,$output['arr3']) && in_array($bg,$output['arr3'])){
                $output['zai3'] = 1;
            }else{
                $output['zai3'] = 2;
            }

            if(in_array($str_n_1,$output['arrAll_7']) && in_array($str_n_2,$output['arrAll_7']) &&in_array($str_n_3,$output['arrAll_7']) &&in_array($str_n_4,$output['arrAll_7']) ){
                $output['zai_7'] = 1;
            }else{
                $output['zai_7'] = 2;
            }
            if(in_array($str_n_1,$output['arr1_7']) && in_array($str_n_2,$output['arr1_7']) && in_array($wb,$output['arr1_7'])){
                $output['zai1_7'] = 1;
            }else{
                $output['zai1_7'] = 2;
            }
            if(in_array($str_n_2,$output['arr2_7']) && in_array($str_n_3,$output['arr2_7']) && in_array($qs,$output['arr2_7'])){
                $output['zai2_7'] = 1;
            }else{
                $output['zai2_7'] = 2;
            }
            if(in_array($str_n_3,$output['arr3_7']) && in_array($str_n_4,$output['arr3_7']) && in_array($bg,$output['arr3_7'])){
                $output['zai3_7'] = 1;
            }else{
                $output['zai3_7'] = 2;
            }
        }else{
            $output['zai'] = 0;
            $output['zai1'] = 0;
            $output['zai2'] = 0;
            $output['zai3'] = 0;

            $output['zai_7'] = 0;
            $output['zai1_7'] = 0;
            $output['zai2_7'] = 0;
            $output['zai3_7'] = 0;
        }
        Logs::debug('3xend',$output['num']);

        return $output;
    }


    public function get_cqssc_num(Request $request)
    {
        $pageSize = $request->input('pageSize', 10);
        $page = $request->input('page', 1);
        $skip = $request->input('skip', 1);
//        $skip = (abs((int)$page)-1)*$pageSize;
        $cf = $request->input('cf',10);

        $res = DB::table('cqssc3')->skip($skip)->take($pageSize)->groupBy("num")->orderBy('id')->having('cf','<',$cf)->get();
        $nums = "";
        foreach ($res as $k=>$v){
            $nums.=" ".$v->num;
        }
        $data['numArr'] = $res;
        $data['nums'] =trim($nums);
        return UtilityHelper::renderJson($data, 0, '');
    }

    public function set_cf(Request $request)
    {
        $res = DB::table('cqssc3')->groupBy("num")->get();
        $numArr = array();
        foreach ($res as $k=>$v){
            $count = DB::table('cqssc3')->where("num",'=',$v->num)->count();
            DB::table('cqssc3')->where("num",'=',$v->num)->update([
                'cf' => $count
            ]);
        }
        return UtilityHelper::renderJson($numArr, 0, 'cf设置成功');
    }

    public function add_cqssc(Request $request)
    {
        $res = REQ::requset_all("alicp_query",'auth',['caipiaoid'=>'73']);
        $xiabiao = strpos($res,'{');
        $res = \GuzzleHttp\json_decode(substr($res,$xiabiao),true);
        $data = $res['result'] ?? false;
        if(!$data) return UtilityHelper::renderJson([], 0, "阿里云接口失效");
        $data['number'] = str_replace(' ','',$data['number']);

        try{
            $res = DB::table("cqssc3")->insert([
                'qici'=>$data['issueno'],
                'num'=>$data['number'],
                'opendate'=>$data['opendate'],
            ]);
        }catch (\Exception $e){
            Logs::debug('cqssc','add_cqssc=======>'.$e->getMessage());
            return UtilityHelper::renderJson([], 0, $e->getMessage());

        }

        return UtilityHelper::renderJson([], 0, '成功');
    }

    public function add_cqsscs(Request $request)
    {
        $qicis = $request['qicis'] ?? false;
        if(!$qicis){
            return UtilityHelper::renderJson([], 0, "必须传入期次");
        }
        $qiciArr = explode(',',$qicis);
        foreach ($qiciArr as $k=>$v){
            $data = self::get_one_res(['qici'=>$v]);
            $data['number'] = str_replace(' ','',$data['number']);

            try{
                $res = DB::table("cqssc3")->insert([
                    'qici'=>$data['issueno'],
                    'num'=>$data['number'],
                    'opendate'=>$data['opendate'],
                ]);
            }catch (\Exception $e){
                Logs::debug('cqssc','add_cqssc=======>'.$e->getMessage());
                return UtilityHelper::renderJson([], 0, $e->getMessage());

            }
        }

        return UtilityHelper::renderJson([], 0, '成功');
    }

    public function get_one(Request $request){
        $data = self::get_one_res($request);
        return UtilityHelper::renderJson($data, 0, '成功');
    }

    public function get_one_res($request = array()){
        $qici = $request['qici'] ?? false;
        if($qici){
            $res = REQ::requset_all("alicp_query",'auth',['caipiaoid'=>'73','issueno'=>$qici]);
        }else{
            $res = REQ::requset_all("alicp_query",'auth',['caipiaoid'=>'73']);
        }
        $xiabiao = strpos($res,'{');
        $res = \GuzzleHttp\json_decode(substr($res,$xiabiao),true);
        $data = $res['result'] ?? false;
        if(!$data) return UtilityHelper::renderJson([], 0, "阿里云接口失效");
        return $data;
    }

}