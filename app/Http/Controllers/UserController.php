<?php
namespace App\Http\Controllers;

use App\User;
use App\Lib\Code;
use App\Lib\UtilityHelper;
use App\Lib\Request as REQ;
use Illuminate\Http\Request;

class UserController extends Controller
{

    public function sendVerifyCode(Request $request)
    {
        $sub_data['mobile'] = $request->input('mobile', '13817715315');
//        $sub_data['verify_code'] = $request->input('sverify_code', '888888');
        $sub_data['sign'] = UtilityHelper::createSign($sub_data);
//        if (in_array('', $sub_data)) {
//            return UtilityHelper::showError(Code::HTTP_REQUEST_PARAM_ERROR);
//        }

        $sub = REQ::requset_all('user/send_verify_code','json',$sub_data);
        print_r($sub);die;
        return UtilityHelper::renderJson($sub);
    }
    public function login(Request $request)
    {
        $sub_data['mobile'] = $request->input('mobile', '13817715315');
        $sub_data['merchant_id'] = $request->input('merchant_id', '');
        $sub_data['verify_code'] = $request->input('verify_code', '888888');

        $sub = REQ::requset_all('user/login','form',$sub_data);
        return $sub;
    }

    public function add_bank_card(Request $request)
    {
        $sub_data['user_token'] = $request->input('user_token', '');
        $sub_data['merchant_id'] = $request->input('merchant_id', '');
        $sub_data['card_no'] = $request->input('card_no', '');
        $sub = REQ::requset_all('user/'.__FUNCTION__,'form',$sub_data);
        return $sub;
    }

    public function bet(Request $request)
    {
        $sub_data['user_token'] = $request->input('user_token', '');
        $sub_data['merchant_id'] = $request->input('merchant_id', '');
        $sub_data['bet_data'] = $request->input('bet_data', '');
        $sub = REQ::requset_all('user/'.__FUNCTION__,'form',$sub_data);
        return $sub;
    }

    public function delete_bank_card(Request $request)
    {
        $sub_data['user_token'] = $request->input('user_token', '');
        $sub_data['merchant_id'] = $request->input('merchant_id', '');
        $sub_data['card_id'] = $request->input('card_id', '');
        $sub = REQ::requset_all('user/'.__FUNCTION__,'form',$sub_data);
        return $sub;
    }

    public function get_account_balance(Request $request)
    {
        $sub_data['user_token'] = $request->input('user_token', '');
        $sub_data['merchant_id'] = $request->input('merchant_id', '');
        $sub = REQ::requset_all('user/'.__FUNCTION__,'form',$sub_data);
        return $sub;
    }

    public function get_bank_card_list(Request $request)
    {
        $sub_data['user_token'] = $request->input('user_token', '');
        $sub_data['merchant_id'] = $request->input('merchant_id', '');
        $sub = REQ::requset_all('user/'.__FUNCTION__,'form',$sub_data);
        return $sub;
    }

    public function get_lottery_order(Request $request)
    {
        $sub_data['user_token'] = $request->input('user_token', '');
        $sub_data['merchant_id'] = $request->input('merchant_id', '');
        $sub = REQ::requset_all('user/'.__FUNCTION__,'form',$sub_data);
        return $sub;
    }

    public function realname_authentication(Request $request)
    {
        $sub_data['user_token'] = $request->input('user_token', '');
        $sub_data['merchant_id'] = $request->input('merchant_id', '');
        $sub_data['realname'] = $request->input('realname', '');
        $sub_data['idcard'] = $request->input('idcard', '');
        $sub = REQ::requset_all('user/'.__FUNCTION__,'form',$sub_data);
        return $sub;
    }

    public function set_trade_password(Request $request)
    {
        $sub_data['user_token'] = $request->input('user_token', '');
        $sub_data['merchant_id'] = $request->input('merchant_id', '');
        $sub_data['verify_code'] = $request->input('verify_code', '');
        $sub_data['password'] = $request->input('password', '');
        $sub = REQ::requset_all('user/'.__FUNCTION__,'form',$sub_data);
        return $sub;
    }
    public function userInfo(Request $request)
    {
        $sub_data['user_token'] = $request->input('user_token', '');
        $sub_data['merchant_id'] = $request->input('merchant_id', '');
        $sub = REQ::requset_all('user/get_user_info','form',$sub_data);
        //添加余额
        $money = REQ::requset_all('user/get_account_balance','form',$sub_data);
        if(isset($money['error_code'])&&!$money['error_code']){
            $data_m = $money['data'];
        }
        $sub['data'] = $sub['data']+$data_m;
        return $sub;
    }

}