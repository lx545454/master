<?php
namespace App\Http\Controllers;

use App\Submit;
use Illuminate\Http\Request;
use App\Lib\Code;
use App\Lib\UtilityHelper;

class SubmitController extends Controller
{
    public function createSubmit(Request $request)
    {
        $sub_data['f_id'] = $request->input('f_id', '');
        $sub_data['u_id'] = $request->input('u_id', '');
        if (in_array('', $sub_data)) {
            return UtilityHelper::showError(Code::HTTP_REQUEST_PARAM_ERROR);
        }

        $sub_data['form_content'] = $request->input('form_content');
        $sub_data['is_agree'] = $request->input('is_agree', -1);

        $sub = Submit::query()->insertGetId($sub_data);
        return UtilityHelper::renderJson($sub);
    }

    public function updateSubmit(Request $request)
    {
        $id = $request->input('id');
        if ($id == 0) {
            return UtilityHelper::showError(Code::HTTP_REQUEST_PARAM_ERROR);
        }
        $sub = Submit::find($id);
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

    public function deleteSubmit($id)
    {
        $sub = Submit::find($id);
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

        $query = Submit::query();
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