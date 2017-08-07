<?php
namespace App\Lib;

use Log;


/**
 * Description of Event
 *
 * @author liuxu
 */

class Request
{
    public static $arr = array(

        'user/send_verify_code' => array("API_LM",'user/send_verify_code','post'),
        'user/login' => array("API_LM",'user/login','post'),
        'user/get_user_info' => array("API_LM",'user/get_user_info','post'),
        'user/add_bank_card' => array("API_LM",'user/add_bank_card','post'),
        'user/bet' => array("API_LM",'user/bet','post'),//投注
        'user/delete_bank_card' => array("API_LM",'user/delete_bank_card','post'),
        'user/get_account_balance' => array("API_LM",'user/get_account_balance','post'),
        'user/get_bank_card_list' => array("API_LM",'user/get_bank_card_list','post'),
        'user/get_lottery_order' => array("API_LM",'user/get_lottery_order','post'),
        'get_lottery_order' => array("API_LM",'user/get_lottery_order','post'),
        'user/realname_authentication' => array("API_LM",'user/realname_authentication','post'),//实名认证
        'user/set_trade_password' => array("API_LM",'user/set_trade_password','post'),
        'send_verify_code' => array("API_LM",'user/send_verify_code','post'),
        'get_order_info' => array("API_LM",'merchant/get_lottery_order','post'),
        'get_user_flow' => array("API_LM",'user/get_user_flow','post'),
        'get_qiniu_token' => array("API_LM",'qiniu/get_qiniu_token','post'),
        'edit_user_info' => array("API_LM",'user/edit_user_info','post'),
        'user_apply_cash' => array("API_LM",'user/user_apply_cash','post'),
        //支付
        'pay/recharge' => array("API_LM",'pay/recharge','post'),
        //彩票
        'get_current_period' => array("API_LM",'lottery/get_current_period','post'),
        'get_dlt_example' => array("API_LM",'lottery/get_dlt_example','post'),
        'get_lottery_list' => array("API_LM",'lottery/get_lottery_list','post'),
        //专家
        'analyst_user_update' => array("API_CZD",'analyst/user/update','post'),
        'analyst_project_add' => array("API_CZD",'analyst/project/add','post'),
        'analyst_project_lists' => array("API_CZD",'analyst/project/lists','post'),
        'analyst_week_lists' => array("API_CZD",'analyst/week/lists','post'),
        'analyst_project_detail' => array("API_CZD",'analyst/project/detail','post'),
        'analyst_project_like' => array("API_CZD",'analyst/project/like','post'),
        'analyst_project_unlike' => array("API_CZD",'analyst/project/unlike','post'),
        'analyst_comment_add' => array("API_CZD",'analyst/comment/add','post'),
        'analyst_comment_like' => array("API_CZD",'analyst/comment/like','post'),
        'analyst_comment_unlike' => array("API_CZD",'analyst/comment/unlike','post'),
        'analyst_user_detail' => array("API_CZD",'analyst/user/detail','post'),
        'analyst_user_recommendProject' => array("API_CZD",'analyst/user/recommendProject','post'),

        'analyst_user_myProject' => array("API_CZD",'analyst/user/myProject','post'),
        'analyst_attention_add' => array("API_CZD",'analyst/attention/add','post'),
        'analyst_attention_cancel' => array("API_CZD",'analyst/attention/cancel','post'),
        'analyst_user_fansNum' => array("API_CZD",'analyst/user/fansNum','post'),
        'analyst_user_fansLists' => array("API_CZD",'analyst/user/fansLists','post'),
        'analyst_user_attentionLists' => array("API_CZD",'analyst/user/attentionLists','post'),
        'analyst_comment_lists' => array("API_CZD",'analyst/comment/lists','post'),
        'analyst_project_moreLists' => array("API_CZD",'analyst/project/moreLists','post'),

        //极速
        'caipiao_query' => array("API_JS",'caipiao/query','post'),
        'caipiao_history' => array("API_JS",'caipiao/history','post'),


    );

    public static function requset_all($key,$type,$params,$sign_key="SIGN_KEY"){
        $params['timestamp'] = date('Ymdhis');
        if(isset($params['merchant_id'])&&$params['merchant_id']=='22015'){
            $sign_key = "QIANYUN";
        }
        $params['sign'] = UtilityHelper::createSign_lm($params,$sign_key);

        if(isset(self::$arr[$key])){
            $api = self::$arr[$key];
            $url_ = $api[0];
            $uri = $api[1];
            $method = $api[2];
            switch ($type)
            {
                case 'json':
                    return self::request_json($method,$url_,$uri,$params);break;
                case 'form':
                    return self::request_form($method,$url_,$uri,$params);break;
                case 'query':
                    return self::request_query($method,$url_,$uri,$params);break;
                default:
                    return self::request_json($method,$url_,$uri,$params);break;
            }
        }

    }
    public static function request_json($method,$url_, $uri, $params = []){
        $url = env($url_).$uri;
        $options = [
            'connect_timeout' => 3,
            'json' => $params,
        ];
        return self::request($method,$url, $params,$options);
    }

    public static function request_form($method,$url_, $uri, $params = []){
        $url = env($url_).$uri;
        $options = [
            'connect_timeout' => 3,
            'form_params' => $params,
        ];
        return self::request($method,$url, $params,$options);
    }

    public static function request_query($method,$url_, $uri, $params = []){
        $url = env($url_).$uri;
        $options = [
            'connect_timeout' => 3,
//            'query' => $params,
        ];
        return self::request($method,$url, $params,$options);
    }

    public static function request($method,$url, $params = [],$options = [])
    {
        $client = new \GuzzleHttp\Client();
//        Log::info('BD request params: '.\json_encode($params));
        if($options == []){
            $options = [
                'connect_timeout' => 3,
                'json' => $params,
            ];
        }
        try {
            $res = $client->request(strtoupper($method), $url, $options);
        } catch (\Exception $e) {
            return UtilityHelper::showError(40000);
        }
        if ($res->getStatusCode() == 200) {
//            Log::info('BD response: '.$res->getBody());
            $data = \json_decode($res->getBody(), true);
            Log::info('outLog',['url'=>$url,'option'=>$options,'data'=>$data]);
//            dd($url, $options, $data);

            return $data;
        }
//        Log::warn('BD request error '.$url.' status code '.$res->getStatusCode().' body '.$res->getBody());
        return null;
    }



}
