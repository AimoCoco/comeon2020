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
Route::get('/test', 'IndexController@index');

Route::get('/5e1ac823555215b0', 'FunctionController@index');
Route::get('/switch', 'FunctionController@switch');
Route::post('/function/setparam', 'FunctionController@setParam');
Route::get('/flush', 'FunctionController@flush');
