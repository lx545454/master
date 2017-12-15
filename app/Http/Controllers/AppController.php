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
            $str3_2 = substr($res3->num,3,2);
            if(in_array($str3_1,$arr1) && in_array($str3_2,$arr2)){
                $output['zai'] = 1;
            }else{
                $output['zai'] = 2;
            }
        }else{
            $output['zai'] = 0;
        }

        return $output;
    }

    public function get_history_3x(Request $request){
        $data = self::get_history_3x_one($request);
        return UtilityHelper::renderJson($data, 0, '');
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

//        print_r($res1);print_r($res_1);die;
        $output = array();

        $output['arr1'] = $arr1;
        $output['arr2'] = $arr2;
        $output['arr3'] = $arr3;
        $output['arrAll'] = $arr1+$arr2+$arr3;
        sort($output['arr1']);
        sort($output['arr2']);
        sort($output['arr3']);
        sort($output['arrAll']);
        $output['qici'] = $qici;
        $output['num'] = $num;

        //下一期
        $res_n = DB::table('cqssc3')->where('qici','>',$qici)->orderBy('qici')->first();
        if($res_n){
            $str_n_1 = substr($res_n->num,0,2);
            $str_n_2 = substr($res_n->num,1,2);
            $str_n_3 = substr($res_n->num,2,2);
            $str_n_4 = substr($res_n->num,3,2);
            if(in_array($str_n_1,$output['arrAll']) && in_array($str_n_2,$output['arrAll']) &&in_array($str_n_3,$output['arrAll']) &&in_array($str_n_4,$output['arrAll']) ){
                $output['zai'] = 1;
            }else{
                $output['zai'] = 2;
            }
        }else{
            $output['zai'] = 0;
        }

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