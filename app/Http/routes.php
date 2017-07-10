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
    $app->post('form','FormController@createForm');
    $app->post('form/update','FormController@updateForm');
    $app->post('form/delete','FormController@deleteForm');
    $app->get('form','FormController@index');

    //SubmitController
    $app->post('submit','SubmitController@createSubmit');
    $app->post('submit/update','SubmitController@updateSubmit');
//    $app->delete('submit/{id}','SubmitController@deleteSubmit');
    $app->get('submit','SubmitController@index');

    $app->post('sendVerifyCode','UserController@sendVerifyCode');
    $app->post('login','UserController@login');


});
