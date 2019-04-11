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

// 首页
Route::redirect('/', '/home');
Route::redirect('/index', '/home');
Route::get('/home', 'PublicController@index');
// 签到器
Route::get('/webCheckin', 'PublicController@webCheckin');
// 登录
Route::get('/login', 'PublicController@login');
// 错误提示
Route::get('/alert/{error}/{content}', 'PublicController@alert');
// 注册
Route::match(['get', 'post'], '/register', 'PublicController@register');
// 用户中心
Route::get('/user', 'UserController@user')->middleware('check.auth');
Route::get('/shop', 'UserController@shop')->middleware('check.auth');
Route::get('/user/security/password', 'UserController@security_change_password')->middleware('check.auth');
Route::get('/user/history/checkin', 'UserController@history_checkin')->middleware('check.auth');
Route::get('/user/bill', 'UserController@bill')->middleware('check.auth');
Route::get('/user/activity', 'UserController@activity')->middleware('check.auth');
// 管理中心
Route::get('/admin', 'AdminController@index')->middleware('check.auth')->middleware('check.admin.auth');
Route::get('/admin/compensate', 'AdminController@compensate')->middleware('check.auth')->middleware('check.admin.auth');
Route::get('/admin/activity', 'AdminController@activity')->middleware('check.auth')->middleware('check.admin.auth');
Route::get('/admin/goods', 'AdminController@goods')->middleware('check.auth')->middleware('check.admin.auth');
Route::get('/admin/optimize', 'AdminController@optimize')->middleware('check.auth')->middleware('check.admin.auth');
