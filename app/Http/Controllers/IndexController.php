<?php
namespace App\Http\Controllers;

use App\Lunbotu;
use App\Lib\Code;
use App\Lib\UtilityHelper;
use App\Lib\Request as REQ;
use App\Xiaoxi;
use Illuminate\Http\Request;

class IndexController extends Controller
{

    public function lunbotu(Request $request)
    {
//        $id = $request->input('id');
//        $type = $request->input('type','');
//        $name = $request->input('name');
        $data = array(
            'lunbo'=>array(),
            'gonggao'=>array()
        );
        $pageSize = $request->input('pageSize', 10);
        $page = $request->input('page', 1);
        $skip = (abs((int)$page)-1)*$pageSize;

        $query = Lunbotu::query();
//        if ($id) $query = $query->where('id', '=', $id);
//        if ($type != '') $query = $query->where('type', '=', $type);
//        if ($name) $query = $query->where('name', 'like', $name.'%');

//        $count = $query->count();
        $lunbo = $query->skip($skip)->take($pageSize)->get()->toArray();

        $query_xiaoxi = Xiaoxi::query();
        $xiaoxi = $query_xiaoxi->get()->toArray();
        $data['lunbo'] = $lunbo;
        $data['gonggao'] = $xiaoxi;

        return UtilityHelper::renderJson($data, 0, '');
    }

}