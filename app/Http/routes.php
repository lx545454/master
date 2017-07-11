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
    $app->post('bet','UserController@bet');
    $app->post('delete_bank_card','UserController@delete_bank_card');
    $app->post('get_account_balance','UserController@get_account_balance');
    $app->post('get_bank_card_list','UserController@get_bank_card_list');
    $app->post('get_lottery_order','UserController@get_lottery_order');
    $app->post('realname_authentication','UserController@realname_authentication');
    $app->post('set_trade_password','UserController@set_trade_password');

});
