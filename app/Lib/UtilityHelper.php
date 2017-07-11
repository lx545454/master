<?php
namespace App\Lib;
use App\Lib\Code;

class UtilityHelper {
    public static function createSign($data = array()) {
        ksort($data);			//先排序
        $params = urldecode(http_build_query($data));
        $secretKey = env("SIGN_KEY");
//        $params .= '&sign=' . $secretKey;
        $sign = md5($params.$secretKey);
        return $sign;
    }

    /**
     * 根据情况，将公共参数顺序定死，后面接上浮动的参数再进行加密
     * @param unknown $data
     */
    public static function createSignSpecial($public_arr, $data = array()) {
        $public_str = urldecode(http_build_query($public_arr));
        //将公共参数剔除后排序
        foreach ($public_arr as $k=>$v) {
            unset($data[$k]);
        }
        ksort($data);
        if (!empty($data)) {
            $params = $public_str . '&' . urldecode(http_build_query($data));
        } else {
            $params = $public_str;
        }
        $secretKey = env("APP_KEY");
        $params .= '&sign=' . $secretKey;
        $sign = md5($params);
        return $sign;
    }

    public static function renderJson($data = array(), $errcode = 0, $errmsg = '', $total = 0) {
        if (!is_array($data)) {
            $data = array($data);
        }
//        $data = json_encode($data);
        $json_arr['error_code'] = $errcode;
        $json_arr['error_msg'] = $errmsg;
//        $json_arr['total'] = $total;
        if($total > 0){
            $json_arr['data'] = array('total'=>$total,'data'=>$data);
        }else{
            $json_arr['data'] = $data;
        }

//        $json_arr = '{"errcode":'.$errcode.',"errmsg":"'.$errmsg.'","total":'.$total.',"data":'.$data.'}';
        return response()->json($json_arr);
    }

    public static function showError($code) {
//        header("Content-type: application/json");
        $errmsg = Code::getErrorMsg($code);
//        $data = json_encode(array());
//        die('{"errcode":'.$code.',"errmsg":"'.$errmsg.'","total":'.$total.',"data":'.$data.'}');
        $json_arr['errcode'] = $code;
        $json_arr['errmsg'] = $errmsg;
        $json_arr['total'] = 0;
        $json_arr['data'] = array();
        return response()->json($json_arr);
    }

    public static function objToArray($object) {
        $object =  json_decode( json_encode( $object),true);
        return $object;
    }

    /**
     * curl封装
     * @param unknown $url
     * @param string $launch
     * @param string $contentType
     * @param unknown $postData
     * @return mixed
     */
    public static function curlProtect($url, $launch = 'post',
                                       $contentType = 'text/html', $postData = array()) {
        $result = "";
        try {
            $header = array("Content-Type:" . $contentType . ";charset=utf-8");
            if (!empty($_SERVER['HTTP_USER_AGENT'])) {		//是否有user_agent信息
                $user_agent = $_SERVER['HTTP_USER_AGENT'];
            }
            $cur = curl_init();
            curl_setopt($cur, CURLOPT_URL, $url);
            curl_setopt($cur, CURLOPT_HEADER, 0);
// 			curl_setopt($cur, CURLOPT_HTTPHEADER, $header);
            curl_setopt($cur, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($cur, CURLOPT_TIMEOUT, 30);
            //https
            curl_setopt($cur, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($cur, CURLOPT_SSL_VERIFYHOST, FALSE);
            if (isset($user_agent)) {
                curl_setopt($cur, CURLOPT_USERAGENT, $user_agent);
            }
            curl_setopt($cur, CURLOPT_ENCODING, 'gzip');
            if (is_array($postData)) {
                if ($postData && count($postData) > 0) {
                    $params = http_build_query($postData);
                    if ($launch=='get') {		//发送方式选择
                        curl_setopt($cur, CURLOPT_HTTPGET, $params);
                    } else {
                        curl_setopt($cur, CURLOPT_POST, true);
                        curl_setopt($cur, CURLOPT_POSTFIELDS, $params);
                    }
                }
            } else {
                if (!empty($postData)) {
                    $params = $postData;
                    if ($launch=='post') {
                        curl_setopt($cur, CURLOPT_POST, true);
                        curl_setopt($cur, CURLOPT_POSTFIELDS, $params);
                    }
                }
            }
            $result = curl_exec($cur);
            curl_close($cur);
        } catch (Exception $e) {

        }
        return $result;
    }

    public static function encodeJson($arr, $parentKey = '') {
        $ret = array();
        foreach ($arr as $key => $value) {
            if (is_array($value)) {
                if (is_int($key)) {
                    $ret[] = self::encodeJson($value, $key);
                } else {
                    $ret[] = '"' . $key . '":' . self::encodeJson($value, $key);
                }
            } else {
                $value  = str_replace('"', '\"', $value);
                $ret [] = '"' . $key . '":"' . $value . '"';
            }
        }
        if (substr($parentKey, -6) !== 'button') {
            $retStr = '{' . join(',', $ret) . '}';
        } else {
            $retStr = '[' . join(',', $ret) . ']';
        }

        return $retStr;
    }

    /**
     * js数组转化为字符串
     * @param unknown $jsArray
     */
    public static function _setRequireJs($jsArray = array()) {
        $_addJs = "";
        foreach ($jsArray as $v) {
            if (empty($_addJs)) {
                $_addJs = '"'.$v.'"';
            } else {
                $_addJs .= ',' . '"'.$v.'"';
            }
        }
        return $_addJs;
    }

    public static function compress_html ($string) {
        $string = str_replace("\r\n", '', $string); //清除换行符
        $string = str_replace("\n", '', $string); //清除换行符
        $string = str_replace("\t", '', $string); //清除制表符
        $pattern = array (
            "/> *([^ ]*) *</", //去掉注释标记
            "/[\s]+/",
            "/<!--[\\w\\W\r\\n]*?-->/",
            "/\" /",
            "/ \"/",
            "'/\*[^*]*\*/'"
        );
        $replace = array (
            ">\\1<",
            " ",
            "",
            "\"",
            "\"",
            ""
        );
        return preg_replace($pattern, $replace, $string);
    }
}