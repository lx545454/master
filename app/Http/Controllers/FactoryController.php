<?php
namespace App\Http\Controllers;

use App\User;
use App\Lib\Code;
use App\Lib\UtilityHelper;
use App\Lib\Request as REQ;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

class FactoryController extends Controller
{

    public function super(Request $request)
    {
        $sub_data = Input::get();
        $url = str_replace('/api/v1/','',$request->server()['REDIRECT_URL']);
        $sub = REQ::requset_all($url,'form',$sub_data);
        return $sub;
    }


}