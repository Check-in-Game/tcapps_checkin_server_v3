<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// =======USER=======
// Check in
Route::get('/getToken/{username}/{b64password}', 'APICheckIn@get_token');
Route::get('/checkIn/{username}/{token}', 'APICheckIn@check_in');

// Login
Route::get('/login/{username}/{b64password}', 'APIUser@login');
// Logout
Route::get('/logout', 'APIUser@logout')
      ->middleware('apicheck.auth');

// Password
Route::post('/user/security/password', 'APIUser@security_change_password')
      ->middleware('apicheck.auth');

// Purchase
Route::get('/purchase/{gid}', 'APIUser@purchase')
      ->middleware('apicheck.auth');

// =======ADMIN=======
// Conpensate
Route::get('/admin/conpensate/{uid}/{count}', 'APIAdmin@conpensate')
      ->middleware('apicheck.auth')
      ->middleware('apicheck.admin.auth:site_manage');
// Activity
  // search
Route::get('/admin/activity/search/{aid}', 'APIAdmin@activity_search')
      ->middleware('apicheck.auth')
      ->middleware('apicheck.admin.auth:activity_search');
  // add
Route::post('/admin/activity/add', 'APIAdmin@activity_add')
      ->middleware('apicheck.auth')
      ->middleware('apicheck.admin.auth:activity_add');
  // update
Route::post('/admin/activity/update', 'APIAdmin@activity_update')
      ->middleware('apicheck.auth')
      ->middleware('apicheck.admin.auth:activity_update');
  // delete
Route::post('/admin/activity/delete', 'APIAdmin@activity_delete')
      ->middleware('apicheck.auth')
      ->middleware('apicheck.admin.auth:activity_delete');
// Goods
  // add
Route::get('/admin/goods/add/{name}/{cost}/{starttime}/{endtime}/{tid}/{sid}/{rebuy}/{all_count}/{description}/{image}', 'APIAdmin@goods_add')
      ->middleware('apicheck.auth')
      ->middleware('apicheck.admin.auth:goods_add');

// Optmize
Route::get('/admin/optimize/{project}', 'APIAdminOptmize@optmize')
      ->middleware('apicheck.auth')
      ->middleware('apicheck.admin.auth:site_optmize');

// Notices
  // search
Route::get('/admin/notices/search/{nid}', 'APIAdmin@notices_search')
      ->middleware('apicheck.auth')
      ->middleware('apicheck.admin.auth:notices_search');
  // add
Route::post('/admin/notices/add', 'APIAdmin@notices_add')
      ->middleware('apicheck.auth')
      ->middleware('apicheck.admin.auth:notices_add');
  // update
Route::post('/admin/notices/update', 'APIAdmin@notices_update')
      ->middleware('apicheck.auth')
      ->middleware('apicheck.admin.auth:notices_update');
  // delete
Route::post('/admin/notices/delete', 'APIAdmin@notices_delete')
      ->middleware('apicheck.auth')
      ->middleware('apicheck.admin.auth:notices_delete');

// Users
  // search
Route::get('/admin/users/search/{uid}', 'APIAdmin@users_search')
      ->middleware('apicheck.auth')
      ->middleware('apicheck.admin.auth:user_search');
  // update
Route::post('/admin/users/update', 'APIAdmin@users_update')
      ->middleware('apicheck.auth')
      ->middleware('apicheck.admin.auth:user_manage');

// Admins 管理权限管理
  // add
Route::post('/admin/rights/add', 'APIAdmin@admins_rights_add')
      ->middleware('apicheck.auth')
      ->middleware('apicheck.admin.auth:site_owner');
