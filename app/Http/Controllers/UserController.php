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
        $sub_data['verify_code'] = $request->input('verify_code', '888888');
        $sub_data['merchant_id'] = $request->input('merchant_id', '38206');
        $sub_data['sign'] = UtilityHelper::createSign($sub_data);
//        if (in_array('', $sub_data)) {
//            return UtilityHelper::showError(Code::HTTP_REQUEST_PARAM_ERROR);
//        }

        $sub = REQ::requset_all('user/login','form',$sub_data);
        return $sub;
        return UtilityHelper::renderJson($sub);
    }

    public function userInfo(Request $request)
    {
        $sub_data['user_token'] = $request->input('user_token', '');
//        $sub_data['merchant_id'] = $request->input('merchant_id', '38206');
//        $sub_data['sign'] = UtilityHelper::createSign($sub_data);
//        if (in_array('', $sub_data)) {
//            return UtilityHelper::showError(Code::HTTP_REQUEST_PARAM_ERROR);
//        }

        $sub = REQ::requset_all('user/get_user_info','form',$sub_data);
        return $sub;
    }

    public function updateUser(Request $request)
    {
        $id = $request->input('id');
        if ($id == 0) {
            return UtilityHelper::showError(Code::HTTP_REQUEST_PARAM_ERROR);
        }
        $sub = User::find($id);
        $f_id = $request->input('f_id');
        $u_id = $request->input('u_id');
        $form_content = $request->input('form_content');
        $is_agree = (int)$request->input('is_agree');

        if ($f_id) $sub->f_id = $f_id;
        if ($u_id) $sub->u_id = $u_id;
        if ($form_content) $sub->form_content = $form_content;
        $sub->is_agree = $is_agree;

        if ($is_agree === 1 || $is_agree === 0) {
            $sub->processing_time = date('Y-m-d H:i:s');
        }
        $sub->save();

        return UtilityHelper::renderJson($sub);
    }

    public function deleteUser($id)
    {
        $sub = User::find($id);
        $sub->delete();
        return UtilityHelper::renderJson($sub);
    }

    public function index(Request $request)
    {
        $id = $request->input('id');
        $f_id = $request->input('f_id');
        $u_id = $request->input('u_id');
        $is_agree = $request->input('is_agree', '');

        $pageSize = $request->input('pageSize', 10);
        $page = $request->input('page', 1);
        $skip = (abs((int)$page)-1)*$pageSize;

        $query = User::query();
        if ($id) {
            $query = $query->where('id', '=', $id);
        }
        if ($f_id) {
            $query = $query->where('f_id', '=', $f_id);
        }
        if ($u_id) {
            $query = $query->where('u_id', '=', $u_id);
        }
        if ($is_agree != '') {
            $query = $query->where('is_agree', '=', $is_agree);
        }
        $count = $query->count();
        $form = $query->skip($skip)->take($pageSize)->orderBy('id', 'desc')->get()->toArray();

        return UtilityHelper::renderJson($form, 0, '', $count);
    }
}