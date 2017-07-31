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
    $app->post('add_bank_card','UserController@add_bank_card');
    $app->post('bet','PayController@bet');
    $app->post('delete_bank_card','UserController@delete_bank_card');
    $app->post('get_account_balance','UserController@get_account_balance');
    $app->post('get_bank_card_list','UserController@get_bank_card_list');
    $app->post('get_lottery_order','UserController@get_lottery_order');
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

    $app->post('get_version','AppController@get_version');
    $app->post('advice_add','IndexController@advice_add');

    $app->get('h5_analyst_attention_add','ZhuanjiaController@h5_analyst_attention_add');
    $app->get('analyst_project_like','FactoryController@super_h5');
    $app->get('analyst_user_detail_userinfo','ZhuanjiaController@analyst_user_detail_userinfo');
    $app->get('analyst_project_detail_userinfo','ZhuanjiaController@analyst_project_detail_userinfo');
    $app->get('get_lottery_order','FactoryController@super_h5');
    $app->get('get_order_info','FactoryController@super_h5');
    $app->get('get_user_flow','FactoryController@super_h5');

    $app->post('caipiao_query','FactoryController@super_js');
    $app->post('get_caipiao_list','IndexController@get_caipiao_list');

});
