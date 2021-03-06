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

        'user/send_verify_code' => array("API_LM",'user/send_verify_code','POST'),
        'user/login' => array("API_LM",'user/login','POST'),
        'user/get_user_info' => array("API_LM",'user/get_user_info','POST'),
        'user/add_bank_card' => array("API_LM",'user/add_bank_card','POST'),
        'user/bet' => array("API_LM",'user/bet','POST'),//投注
        'user/delete_bank_card' => array("API_LM",'user/delete_bank_card','POST'),
        'user/get_account_balance' => array("API_LM",'user/get_account_balance','POST'),
        'user/get_bank_card_list' => array("API_LM",'user/get_bank_card_list','POST'),
        'user/get_lottery_order' => array("API_LM",'user/get_lottery_order','POST'),
        'get_lottery_order' => array("API_LM",'user/get_lottery_order','POST'),
        'user/realname_authentication' => array("API_LM",'user/realname_authentication','POST'),//实名认证
        'user/set_trade_password' => array("API_LM",'user/set_trade_password','POST'),
        'send_verify_code' => array("API_LM",'user/send_verify_code','POST'),
        'get_order_info' => array("API_LM",'merchant/get_lottery_order','POST'),
        'get_user_flow' => array("API_LM",'user/get_user_flow','POST'),
        'get_qiniu_token' => array("API_LM",'qiniu/get_qiniu_token','POST'),
        'edit_user_info' => array("API_LM",'user/edit_user_info','POST'),
        'user_apply_cash' => array("API_LM",'user/user_apply_cash','POST'),
        'get_merchant' => array("API_LM",'merchant/get_merchant','POST'),
        //支付
        'pay/recharge' => array("API_LM",'pay/recharge','POST'),
        //彩票
        'get_current_period' => array("API_LM",'lottery/get_current_period','POST'),
        'get_dlt_example' => array("API_LM",'lottery/get_dlt_example','POST'),
        'get_lottery_list' => array("API_LM",'lottery/get_lottery_list','POST'),
        //专家
        'analyst_user_update' => array("API_CZD",'analyst/user/update','POST'),
        'analyst_project_add' => array("API_CZD",'analyst/project/add','POST'),
        'analyst_project_lists' => array("API_CZD",'analyst/project/lists','POST'),
        'analyst_week_lists' => array("API_CZD",'analyst/week/lists','POST'),
        'analyst_project_detail' => array("API_CZD",'analyst/project/detail','POST'),
        'analyst_project_like' => array("API_CZD",'analyst/project/like','POST'),
        'analyst_project_unlike' => array("API_CZD",'analyst/project/unlike','POST'),
        'analyst_comment_add' => array("API_CZD",'analyst/comment/add','POST'),
        'analyst_comment_like' => array("API_CZD",'analyst/comment/like','POST'),
        'analyst_comment_unlike' => array("API_CZD",'analyst/comment/unlike','POST'),
        'analyst_user_detail' => array("API_CZD",'analyst/user/detail','POST'),
        'analyst_user_recommendProject' => array("API_CZD",'analyst/user/recommendProject','POST'),

        'analyst_user_myProject' => array("API_CZD",'analyst/user/myProject','POST'),
        'analyst_attention_add' => array("API_CZD",'analyst/attention/add','POST'),
        'analyst_attention_cancel' => array("API_CZD",'analyst/attention/cancel','POST'),
        'analyst_user_fansNum' => array("API_CZD",'analyst/user/fansNum','POST'),
        'analyst_user_fansLists' => array("API_CZD",'analyst/user/fansLists','POST'),
        'analyst_user_attentionLists' => array("API_CZD",'analyst/user/attentionLists','POST'),
        'analyst_comment_lists' => array("API_CZD",'analyst/comment/lists','POST'),
        'analyst_project_moreLists' => array("API_CZD",'analyst/project/moreLists','POST'),

        //极速
        'caipiao_query' => array("API_JS",'caipiao/query','GET'),
        'caipiao_history' => array("API_JS",'caipiao/history','GET'),

        //竞猜
        'jingcai' => array("API_JC",'test/test.go',"POST"),

        'alicp_query' => array("API_ALI",'caipiao/query','GET'),
        'alicp_history' => array("API_ALI",'caipiao/history','GET'),

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
                case 'js':
                    return self::request_js($method,$url_,$uri,$params);break;
                case 'auth':
                    return self::request_auth($method,$url_,$uri,$params);break;
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
        $url.="?".http_build_query($params);
        echo $url;
        $options = [
            'connect_timeout' => 3,
            'query' => $params,
        ];
        return self::request($method,$url, $params,$options);
    }

    public static function request_auth($method,$url_, $uri, $params = []){
        $url = env($url_).$uri;
        $appcode = env('APPCODE');
        $headers = array();
        array_push($headers, "Authorization:APPCODE " . $appcode);
        $querys = http_build_query($params);
        $bodys = "";
        $url = $url . "?" . $querys;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        if (1 == strpos("$".$url, "https://"))
        {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        return curl_exec($curl);

    }

    public static function request_js($method,$url_, $uri, $params = []){
        $url = env($url_).$uri;
        $querys = http_build_query($params);
        $url = $url.'?'.$querys;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        if (1 == strpos("$".$url, "https://"))
        {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        $res = curl_exec($curl);
        $xiabiao = strpos($res,'{');
        $res = \GuzzleHttp\json_decode(substr($res,$xiabiao),true);
        return $res;

    }

    public static function request_xml($body,$cmd){
        $url = env("API_JC");
        $message_id = date('YmdHis').mt_rand(100000, 999999); //请求流水号必须保证唯一

        $xml = '<?xml version="1.0" encoding="UTF-8"?>
            <request>
            <head sid="'.$cmd.'" agent="4007" messageid="'.$message_id.'" timestamp="'.date('Y-m-d H:i:s').'" memo="" />
            <body>
            '.$body.'
            </body>
            </request>';

        $sign = md5($xml.'123456');
        $data = 'xml='.$xml.'&sign='.$sign;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST,true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

        if (!empty($options)){
            curl_setopt_array($ch, $options);
        }
        $data = curl_exec($ch);
        curl_close($ch);
        return json_decode(json_encode((array) simplexml_load_string($data)), true);
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
//            print_r($e->getMessage());die;
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
