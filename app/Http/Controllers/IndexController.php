<?php
namespace App\Http\Controllers;

use App\Advice;
use App\Lunbotu;
use App\Lib\Code;
use App\Lib\UtilityHelper;
use App\Lib\Request as REQ;
use App\Xiaoxi;
use Illuminate\Http\Request;
use App\Lib\Caipiao;
use Cache;
class IndexController extends Controller
{

    public function lunbotu(Request $request)
    {
//        $id = $request->input('id');
//        $type = $request->input('type','');
//        $name = $request->input('name');
        $data = array(
            'lunbo'=>array(),
            'gonggao'=>array(),
            'caipiao'=>array(),
        );
        $pageSize = $request->input('pageSize', 10);
        $page = $request->input('page', 1);
        $skip = (abs((int)$page)-1)*$pageSize;
echo "a";
        $query = Lunbotu::query();
//        if ($id) $query = $query->where('id', '=', $id);
//        if ($type != '') $query = $query->where('type', '=', $type);
//        if ($name) $query = $query->where('name', 'like', $name.'%');

//        $count = $query->count();
        $lunbo = $query->skip($skip)->take($pageSize)->get()->toArray();
        echo "b";
        $query_xiaoxi = Xiaoxi::query();echo "c";
        $xiaoxi = $query_xiaoxi->get()->toArray();echo "d";
        $data['lunbo'] = $lunbo;
        $data['gonggao'] = $xiaoxi;
        $data['caipiao'] = $this->get_caipiao_arr();
        print_r($data);die;
        return UtilityHelper::renderJson($data, 0, '');
    }

    public function advice_add(Request $request)
    {
        $sub_data['content'] = $request->input('content');
        $sub_data['userid']= $request->input('userid');
        if (in_array('', $sub_data)) {
            return UtilityHelper::showError(Code::HTTP_REQUEST_PARAM_ERROR);
        }

        $sub = Advice::query()->insertGetId($sub_data);
        return UtilityHelper::renderJson($sub);

    }

    public function get_caipiao_list(Request $request)
    {
        $subArr = $this->get_caipiao_arr();
        $url = str_replace('/api/v1/','',$request->server()['REDIRECT_URL']);
        $_url = substr($url,0,6);
        if($_url == "jsonp_"){
            $callback = $request->input('callback');
            return  $callback."(".\GuzzleHttp\json_encode(['data' => $subArr]).")";
        }
        return UtilityHelper::renderJson(['data' => $subArr]);
    }

    public function get_caipiao_arr(){
        if (app('cache')->has('caipiao_list')) {
            return app('cache')->get('caipiao_list');
        }
        $sub_data['caipiaoids'] = "11 12 14 16";
        $sub_data['appkey'] = env('JS_APPKEY');
        $cpArr = explode(' ',$sub_data['caipiaoids']);
        $subArr = [];
        foreach ($cpArr as $k=>$v){
            $param = [
                'appkey'=>$sub_data['appkey'],
                'caipiaoid' => $v,
            ];
            $res = REQ::requset_all('caipiao_query','form',$param);
            if(isset($res['status']) && $res['status'] == "0"){
                $res['result']['lottery_code'] = Caipiao::getLotteryCode($res['result']['caipiaoid']);
                $subArr[] = $res['result'];
            }
        }
        app('cache')->put('caipiao_list',$subArr,60);
        return $subArr;
    }

}