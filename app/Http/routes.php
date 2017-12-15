<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$app->get('/', function () use ($app) {
    return $app->version();
});
$app->group(['prefix' => 'api/v1'], function($app)
{
    $app->post('sendVerifyCode','UserController@sendVerifyCode');
    $app->post('login','UserController@login');
    $app->post('user-info','UserController@userInfo');
    $app->get('user-info','UserController@userInfo');
    $app->post('add_bank_card','UserController@add_bank_card');
    $app->post('bet','PayController@bet');
    $app->post('delete_bank_card','UserController@delete_bank_card');
    $app->post('get_account_balance','UserController@get_account_balance');
    $app->post('get_bank_card_list','UserController@get_bank_card_list');
    $app->post('get_lottery_order','FactoryController@super');
    $app->get('get_lottery_order','FactoryController@super_h5');
    $app->post('realname_authentication','UserController@realname_authentication');
    $app->post('set_trade_password','UserController@set_trade_password');


    $app->post('recharge','PayController@recharge');

    $app->post('lunbotu','IndexController@lunbotu');
    $app->post('push2app','PushController@push');
    $app->post('get_current_period','FactoryController@super');
    $app->post('get_dlt_example','FactoryController@super');
    $app->post('get_lottery_list','FactoryController@super');
    $app->post('edit_user_info','FactoryController@super');

    $app->post('analyst_user_update','FactoryController@super');
    $app->post('analyst_project_add','FactoryController@super');
    $app->post('analyst_project_lists','FactoryController@super');
    $app->post('analyst_week_lists','FactoryController@super');
    $app->post('analyst_project_detail','FactoryController@super');
    $app->post('analyst_project_like','FactoryController@super');
    $app->post('analyst_project_unlike','FactoryController@super');
    $app->post('analyst_comment_add','FactoryController@super');
    $app->post('analyst_comment_like','FactoryController@super');
    $app->post('analyst_comment_unlike','FactoryController@super');

    $app->post('analyst_user_detail','FactoryController@super');
    $app->post('analyst_user_recommendProject','FactoryController@super');
    $app->post('analyst_user_myProject','FactoryController@super');
    $app->post('analyst_attention_add','FactoryController@super');
    $app->post('analyst_attention_cancel','FactoryController@super');
    $app->post('analyst_user_fansNum','FactoryController@super');
    $app->post('analyst_user_fansLists','FactoryController@super');
    $app->post('analyst_user_attentionLists','FactoryController@super');
    $app->post('analyst_comment_lists','FactoryController@super');
    $app->post('analyst_project_moreLists','FactoryController@super');
    $app->post('send_verify_code','FactoryController@super');
    $app->post('get_order_info','FactoryController@super');
    $app->post('get_user_flow','FactoryController@super');
    $app->post('get_qiniu_token','FactoryController@super');
    $app->post('user_apply_cash','FactoryController@super');

    $app->post('get_version','AppController@get_version');
    $app->post('advice_add','IndexController@advice_add');

    $app->get('h5_analyst_attention_add','ZhuanjiaController@h5_analyst_attention_add');
    $app->get('analyst_attention_cancel','FactoryController@super_h5');
    $app->get('analyst_project_like','FactoryController@super_h5');
    $app->get('analyst_user_detail_userinfo','ZhuanjiaController@analyst_user_detail_userinfo');
    $app->get('analyst_project_detail_userinfo','ZhuanjiaController@analyst_project_detail_userinfo');

    $app->get('get_lottery_order','FactoryController@super_h5');
    $app->get('get_order_info','FactoryController@super_h5');
    $app->get('get_user_flow','FactoryController@super_h5');
    $app->get('get_merchant','FactoryController@super_h5');
    $app->post('get_merchant','FactoryController@super');

    $app->post('caipiao_query','FactoryController@super_js');
    $app->post('caipiao_history','FactoryController@super_js');
    $app->post('get_caipiao_list','IndexController@get_caipiao_list');

    $app->get('jsonp_get_caipiao_list','IndexController@get_caipiao_list');
    $app->get('jsonp_caipiao_query','FactoryController@super_js');
    $app->get('jsonp_caipiao_history','FactoryController@super_js');

    $app->post('ssc_getnum','FactoryController@link');
    $app->post('ssc_addqici','FactoryController@link');
    $app->post('ssc_update','FactoryController@link');
    $app->post('ssc_goupdate','FactoryController@link');
    $app->post('ssc_get_qici','FactoryController@link');
    $app->post('ssc_get_qicis','FactoryController@link');
    $app->post('ssc_get_qici_detail','FactoryController@link');

    $app->post('jc_duizhen','JingcaiController@duizhen');
    $app->post('jc_all','JingcaiController@duizhen');
    $app->get('jc_duizhen','JingcaiController@duizhen');

    $app->post('jc_jczq','JingcaiController@jczq');
    $app->post('jc_sfc','JingcaiController@sfc');
    $app->get('jc_sfc','JingcaiController@sfc');
    $app->get('jc_dc','JingcaiController@dc');
    $app->post('jc_dc','JingcaiController@dc');

    $app->post('get_cqssc','AppController@get_cqssc');
    $app->post('get_cqssc_history','AppController@get_cqssc_history');
    $app->get('get_cqssc_history','AppController@get_cqssc_history');
    $app->post('get_cqssc2','AppController@get_cqssc2');
    $app->post('get_cqssc3','AppController@get_cqssc3');
    $app->post('set_cf','AppController@set_cf');
    $app->post('get_cqssc_num','AppController@get_cqssc_num');

    $app->get('add_cqssc','AppController@add_cqssc');
    $app->get('add_cqsscs','AppController@add_cqsscs');
    $app->post('get_one','AppController@get_one');
    $app->post('get_ssc_history','AppController@get_ssc_history');
    $app->post('get_history_2x','AppController@get_history_2x');
    $app->post('get_history_3x','AppController@get_history_3x');
    $app->post('get_history_2x_list','AppController@get_history_2x_list');
    $app->post('get_history_3x_list','AppController@get_history_3x_list');

});
