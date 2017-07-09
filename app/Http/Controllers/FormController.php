<?php
namespace App\Http\Controllers;

use App\Form;
use Illuminate\Http\Request;
use App\Lib\Code;
use App\Lib\UtilityHelper;

class FormController extends Controller
{
    public function createForm(Request $request)
    {
        $form_data['name'] = trim($request->input('name', ''));
        $form_data['html_content'] = trim($request->input('html_content', ''));
        if (in_array('', $form_data)) {
            return UtilityHelper::showError(Code::HTTP_REQUEST_PARAM_ERROR);
        }

        $form_data['agree_content'] = $request->input('agree_content');
        $form_data['refuse_content'] = $request->input('refuse_content');

        $form = Form::query()->insertGetId($form_data);
        return UtilityHelper::renderJson($form);
    }

    public function updateForm(Request $request)
    {
        $id = $request->input('id');
        if ($id == 0) {
            return UtilityHelper::showError(Code::HTTP_REQUEST_PARAM_ERROR);
        }
        $form = Form::find($id);
        $name = $request->input('name');
        $html_content = $request->input('html_content');
        $agree_content = $request->input('agree_content');
        $refuse_content = $request->input('refuse_content');
        $type = $request->input('type');

        if ($name) $form->name = $name;
        if ($html_content) $form->html_content = $html_content;
        if ($agree_content) $form->agree_content = $agree_content;
        if ($refuse_content) $form->refuse_content = $refuse_content;
        if ($type) $form->type = $type;
        $form->save();

        return UtilityHelper::renderJson($form);
    }

    public function deleteForm(Request $request)
    {
        $id = $request->input('id', 0);
        if ($id == 0) {
            return UtilityHelper::showError(Code::HTTP_REQUEST_PARAM_ERROR);
        }
        $form = Form::find($id);
        $form->delete();
        return UtilityHelper::renderJson($form);
    }

    public function index(Request $request)
    {
        $id = $request->input('id');
        $type = $request->input('type','');
        $name = $request->input('name');

        $pageSize = $request->input('pageSize', 10);
        $page = $request->input('page', 1);
        $skip = (abs((int)$page)-1)*$pageSize;

        $query = Form::query();
        if ($id) $query = $query->where('id', '=', $id);
        if ($type != '') $query = $query->where('type', '=', $type);
        if ($name) $query = $query->where('name', 'like', $name.'%');

        $count = $query->count();
        $form = $query->skip($skip)->take($pageSize)->orderBy('id', 'desc')->get()->toArray();

        return UtilityHelper::renderJson($form, 0, '', $count);
    }
}