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
        'user/realname_authentication' => array("API_LM",'user/realname_authentication','post'),//实名认证
        'user/set_trade_password' => array("API_LM",'user/set_trade_password','post'),
        //支付
        'pay/recharge' => array("API_LM",'pay/recharge','post'),
        //彩票
        'get_current_period' => array("API_LM",'lottery/get_current_period','post'),
        'get_dlt_example' => array("API_LM",'lottery/get_dlt_example','post'),
        'get_lottery_list' => array("API_LM",'lottery/get_lottery_list','post'),

    );

    public static function requset_all($key,$type,$params,$sign_key="SIGN_KEY"){
        $params['timestamp'] = date('Ymdhis');
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
            'query' => $params,
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
            return false;
        }
        if ($res->getStatusCode() == 200) {
//            Log::info('BD response: '.$res->getBody());
            $data = \json_decode($res->getBody(), true);
//            dd($url, $options, $data);
            return $data;
        }
//        Log::warn('BD request error '.$url.' status code '.$res->getStatusCode().' body '.$res->getBody());
        return null;
    }



}
