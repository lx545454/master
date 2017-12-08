<?php
namespace App\Http\Controllers;

use App\App_version;
use App\Lunbotu;
use App\Lib\Code;
use App\Lib\UtilityHelper;
use App\Lib\Request as REQ;
use App\Xiaoxi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $sub_data['num'] = "20";
        $sub_data['start'] = 0;
        $all = array();
        $alls = array();
        while (true){
            $res = REQ::requset_all('caipiao_history','form',$sub_data);
            if(isset($res['status']) && $res['status'] == "0" && isset($res['result']['list'])){
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
                $sub_data['start'] +=20;
            }else{
                break;
            }

        }


        foreach ($all as $k=>$v){
            $count = DB::table('cqssc3')->where("num",'=',$v['number'])->count();
            DB::table('cqssc3')->where("num",'=',$v['number'])->update([
                'cf' => $count
            ]);
        }
        return UtilityHelper::renderJson($res, 0, '一共有：'.count($all));

    }

    public function get_cqssc3(Request $request)
    {
        $sub_data['caipiaoid'] = "73";
        $sub_data['appkey'] = env('JS_APPKEY');
        $sub_data['num'] = "20";
        $sub_data['start'] = 0;
        $all = array();
        $res = REQ::requset_all('caipiao_history','form',$sub_data);
        if(isset($res['status']) && $res['status'] == "0" && isset($res['result']['list'])){
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
            }
        }

        foreach ($all as $k=>$v){
            $count = DB::table('cqssc3')->where("num",'=',$v['number'])->count();
            DB::table('cqssc3')->where("num",'=',$v['number'])->update([
                'cf' => $count
            ]);
        }
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
}