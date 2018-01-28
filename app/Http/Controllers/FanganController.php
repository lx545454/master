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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
class FanganController extends Controller
{

    public function get()
    {
        $post = array_filter(Input::all());
        $query = DB::table("fangan");
        if(isset($post['id'])){
            $res = $query->where('id',$post['id'])->get()->toArray();
            if($res){
                return UtilityHelper::renderJson($res, 0, '');
            }
        }
        return UtilityHelper::renderJson([], 0, '');

    }

    public function getList()
    {
        $post = array_filter(Input::all());
        $query = DB::table("fangan");
        try{
            $res = $query->skip($post['pageSize']*($post['page']-1))->take($post['pageSize'])->orderBy('id','desc')->get()->toArray();
        }catch (Exception $e){
            return UtilityHelper::renderJson(['error'=>$e->getMessage()], 1, 'error');
        }
        return UtilityHelper::renderJson($res, 0, '');

    }
    public function save()
    {
        $post = array_filter(Input::all());
        if(isset($post['id'])){
            $id = $post['id'];
            unset($post['id']);
            $res = DB::table('fangan')->where('id',$id)->update($post);
        }else{
            $res = DB::table('fangan')->insert($post);
        }
        if($res){
            return UtilityHelper::renderJson(['id'=>$res], 0, '');
        }else{
            return UtilityHelper::renderJson(['error'=>$res], 0, '失败');
        }


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