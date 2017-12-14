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
        $res = DB::table('cqssc2')->groupBy("num")->get();
        $numArr = array();
        foreach ($res as $k=>$v){
            $numArr[] = $v->num;
        }
        return UtilityHelper::renderJson($numArr, 0, '');
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

}