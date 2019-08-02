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

// 错误提示
Route::get('/alert/{error}/{content}', 'PublicController@alert');

// 首页
Route::redirect('/', '/home');
Route::redirect('/index', '/home');
Route::get('/home', 'PublicController@index')                                     ->middleware('check.auth:info', 'notice:1');
// 登录
Route::get('/login', 'PublicController@login')                                    ->middleware('check.auth:info', 'notice:4');
Route::get('/login_old', 'PublicController@login_old')                            ->middleware('check.auth:info', 'notice:4');
// 注册
Route::match(['get', 'post'], '/register', 'PublicController@register')           ->middleware('check.auth:info', 'notice:2');
// 排行榜
// Route::get('/leaderboard', 'PublicController@leaderboard')                        ->middleware('check.auth:info', 'notice:28');

Route::get('/user/verify_email/{uid}/{code}', 'UserController@verify_email')      ->middleware('notice:19');

// 用户中心
Route::get('/user', 'UserController@user')                                        ->middleware('check.auth', 'notice:5');
Route::get('/shop', 'UserController@shop')                                        ->middleware('check.auth', 'notice:6');
Route::get('/user/security/password', 'UserController@security_change_password')  ->middleware('check.auth', 'notice:7');
Route::get('/user/security/email', 'UserController@security_email')               ->middleware('check.auth', 'notice:16');
Route::get('/user/recycle', 'UserController@recycle')                             ->middleware('check.auth', 'notice:8');
Route::get('/user/blend', 'UserController@blend')                                 ->middleware('check.auth', 'notice:9');
Route::get('/user/worker', 'UserController@worker')                               ->middleware('check.auth', 'notice:10');
Route::get('/user/worker_upgrade', 'UserController@worker_upgrade')               ->middleware('check.auth', 'notice:13');
Route::get('/user/gifts/reedem', 'UserController@gifts_reedem')                   ->middleware('check.auth', 'notice:14');
// Route::get('/user/badges', 'UserController@badges')                               ->middleware('check.auth', 'notice:25');
Route::get('/user/security/username', 'UserController@username_modify')           ->middleware('check.auth', 'notice:26');
Route::get('/user/resources', 'UserController@user_resources')                    ->middleware('check.auth', 'notice:27');
Route::get('/user/market', 'UserController@market')                               ->middleware('check.auth', 'notice:16');
Route::get('/user/market/sale', 'UserController@market_sale')                     ->middleware('check.auth', 'notice:17');
Route::get('/user/market/manage', 'UserController@market_manage')                 ->middleware('check.auth', 'notice:18');

// 基金会
Route::get('/foundation/recurit', 'FoundationController@recruit')                 ->middleware('check.auth', 'notice:15');


// 管理中心
Route::get('/admin', 'AdminController@index')                                     ->middleware('check.auth', 'check.admin.auth', 'notice:11');
Route::get('/admin/update', 'AdminController@update')                             ->middleware('check.auth', 'check.admin.auth', 'notice:12');
// Route::get('/admin/compensate', 'AdminController@compensate')           ->middleware('check.auth', 'check.admin.auth', 'notice:12');
// Route::get('/admin/activity', 'AdminController@activity')               ->middleware('check.auth', 'check.admin.auth', 'notice:20');
// Route::get('/admin/activity_manage', 'AdminController@activity_manage') ->middleware('check.auth', 'check.admin.auth', 'notice:13');
// Route::get('/admin/goods', 'AdminController@goods')                     ->middleware('check.auth', 'check.admin.auth', 'notice:27');
// Route::get('/admin/goods_manage', 'AdminController@goods_manage')       ->middleware('check.auth', 'check.admin.auth', 'notice:14');
// Route::get('/admin/optimize', 'AdminController@optimize')               ->middleware('check.auth', 'check.admin.auth', 'notice:15');
// Route::get('/admin/notices', 'AdminController@notices')                 ->middleware('check.auth', 'check.admin.auth', 'notice:16');
// Route::get('/admin/notices/manage', 'AdminController@notices_manage')   ->middleware('check.auth', 'check.admin.auth', 'notice:17');
// Route::get('/admin/users', 'AdminController@users_list')                ->middleware('check.auth', 'check.admin.auth', 'notice:18');
// Route::get('/admin/users/manage', 'AdminController@users_manage')       ->middleware('check.auth', 'check.admin.auth', 'notice:19');
// Route::get('/admin/manage', 'AdminController@admins_manage')            ->middleware('check.auth', 'check.admin.auth', 'notice:20');
// Route::get('/admin/badges', 'AdminController@badges')                   ->middleware('check.auth', 'check.admin.auth', 'notice:21');
// Route::get('/admin/badges/manage', 'AdminController@badges_manage')     ->middleware('check.auth', 'check.admin.auth', 'notice:22');
// Route::get('/admin/effects', 'AdminController@effects')                 ->middleware('check.auth', 'check.admin.auth', 'notice:23');
// Route::get('/admin/effects/manage', 'AdminController@effects_manage')   ->middleware('check.auth', 'check.admin.auth', 'notice:24');
