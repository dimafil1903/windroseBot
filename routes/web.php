<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


    Route::get('unset', 'TelegramController@unset');
    Route::get('set', 'TelegramController@set');
    Route::post('hook', 'TelegramController@hook');
    Route::get('info', 'TelegramController@info');
    Route::get('check', 'SendMessage@check');
//    Route::post("viberhook","ViberBotController@index");
//    Route::get('setviber', 'ViberBotController@set');
//    Route::get('unsetviber', 'TelegramController@unset');
//    Route::post('hookviber', 'TelegramController@hook');
Route::group(['prefix' => 'admin'], function () {
    Voyager::routes();
    Route::group(['prefix' => 'telegram'], function () {
        Route::get('index', 'SendMessage@index');
        Route::post('send', 'SendMessage@send');
    });

    Route::group([   'as'     => 'telegram-config.','prefix' => 'telegram-config'], function ()   {

        Route::get('/', ['uses' =>'TelegramSettingsController@index',        'as' => 'index']);
        Route::post('/', ['uses' => 'TelegramSettingsController@store',        'as' => 'store']);
        Route::put('/{id}', ['uses' => 'TelegramSettingsController@update',       'as' => 'update']);
        Route::delete('{id}', ['uses' => 'TelegramSettingsController@delete',       'as' => 'delete']);
        Route::get('{id}/move_up', ['uses' => 'TelegramSettingsController@move_up',      'as' => 'move_up']);
        Route::get('{id}/move_down', ['uses' => 'TelegramSettingsController@move_down',    'as' => 'move_down']);
        Route::put('{id}/delete_value', ['uses' => 'TelegramSettingsController@delete_value', 'as' => 'delete_value']);
    });

});
