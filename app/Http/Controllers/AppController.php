<?php
namespace App\Http\Controllers;

use App\App_version;
use App\Lunbotu;
use App\Lib\Code;
use App\Lib\UtilityHelper;
use App\Lib\Request as REQ;
use App\Xiaoxi;
use Illuminate\Http\Request;

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

}