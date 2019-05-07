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
Route::get('/home', 'PublicController@index')                                     ->middleware('check.auth', 'notice:1');
// 签到器
Route::get('/webCheckin', 'PublicController@webCheckin')                          ->middleware('check.auth', 'notice:3');
// 登录
Route::get('/login', 'PublicController@login')                                    ->middleware('notice:4');
// 错误提示
Route::get('/alert/{error}/{content}', 'PublicController@alert');
// 鸣谢
Route::get('/credit', 'PublicController@credit');
// 注册
Route::match(['get', 'post'], '/register', 'PublicController@register')           ->middleware('notice:2');
// 用户中心
Route::get('/user', 'UserController@user')                                        ->middleware('check.auth', 'notice:5');
Route::get('/shop', 'UserController@shop')                                        ->middleware('check.auth', 'notice:6');
Route::get('/user/security/password', 'UserController@security_change_password')  ->middleware('check.auth', 'notice:7');
Route::get('/user/history/checkin', 'UserController@history_checkin')             ->middleware('check.auth', 'notice:8');
Route::get('/user/bill', 'UserController@bill')                                   ->middleware('check.auth', 'notice:9');
Route::get('/user/activity', 'UserController@activity')                           ->middleware('check.auth', 'notice:10');
Route::get('/user/badges', 'UserController@badges')                               ->middleware('check.auth', 'notice:25');
Route::get('/user/security/username', 'UserController@username_modify')           ->middleware('check.auth', 'notice:26');

// 管理中心
Route::get('/admin', 'AdminController@index')                           ->middleware('check.auth', 'check.admin.auth', 'notice:11');
Route::get('/admin/compensate', 'AdminController@compensate')           ->middleware('check.auth', 'check.admin.auth', 'notice:12');
Route::get('/admin/activity', 'AdminController@activity')               ->middleware('check.auth', 'check.admin.auth', 'notice:20');
Route::get('/admin/activity_manage', 'AdminController@activity_manage') ->middleware('check.auth', 'check.admin.auth', 'notice:13');
Route::get('/admin/goods', 'AdminController@goods')                     ->middleware('check.auth', 'check.admin.auth', 'notice:27');
Route::get('/admin/goods_manage', 'AdminController@goods_manage')       ->middleware('check.auth', 'check.admin.auth', 'notice:14');
Route::get('/admin/optimize', 'AdminController@optimize')               ->middleware('check.auth', 'check.admin.auth', 'notice:15');
Route::get('/admin/notices', 'AdminController@notices')                 ->middleware('check.auth', 'check.admin.auth', 'notice:16');
Route::get('/admin/notices/manage', 'AdminController@notices_manage')   ->middleware('check.auth', 'check.admin.auth', 'notice:17');
Route::get('/admin/users', 'AdminController@users_list')                ->middleware('check.auth', 'check.admin.auth', 'notice:18');
Route::get('/admin/users/manage', 'AdminController@users_manage')       ->middleware('check.auth', 'check.admin.auth', 'notice:19');
Route::get('/admin/manage', 'AdminController@admins_manage')            ->middleware('check.auth', 'check.admin.auth', 'notice:20');
Route::get('/admin/badges', 'AdminController@badges')                   ->middleware('check.auth', 'check.admin.auth', 'notice:21');
Route::get('/admin/badges/manage', 'AdminController@badges_manage')     ->middleware('check.auth', 'check.admin.auth', 'notice:22');
Route::get('/admin/effects', 'AdminController@effects')                 ->middleware('check.auth', 'check.admin.auth', 'notice:23');
Route::get('/admin/effects/manage', 'AdminController@effects_manage')   ->middleware('check.auth', 'check.admin.auth', 'notice:24');
