<?php
namespace App\Http\Controllers;

use App\User;
use App\Lib\Code;
use App\Lib\UtilityHelper;
use App\Lib\Request as REQ;
use Illuminate\Http\Request;

class PayController extends Controller
{

    public function recharge(Request $request)
    {
        $sub_data['user_token'] = $request->input('user_token', '');
        $sub_data['amount'] = $request->input('amount', '');
        $sub = REQ::requset_all('pay/'.__FUNCTION__,'form',$sub_data);
        return $sub;
    }


}