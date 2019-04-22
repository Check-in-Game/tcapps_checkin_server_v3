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
Route::post('/login', 'APIUser@login');
// Logout
Route::get('/logout', 'APIUser@logout')
      ->middleware('apicheck.auth');

// Password
Route::post('/user/security/password', 'APIUser@security_change_password')
      ->middleware('apicheck.auth');

// Purchase
Route::get('/purchase/{gid}', 'APIUser@purchase')
      ->middleware('apicheck.auth');

// Wear Badge
  // wear
Route::post('/user/badge/wear', 'APIUser@badge_wear')
      ->middleware('apicheck.auth');
  // take off
Route::post('/user/badge/takeoff', 'APIUser@badge_takeoff')
      ->middleware('apicheck.auth');

// =======ADMIN=======
// Conpensate
Route::post('/admin/conpensate', 'APIAdmin@conpensate')
      ->middleware('apicheck.auth')
      ->middleware('apicheck.admin.auth:conpensate_add');
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
Route::post('/admin/goods/add', 'APIAdmin@goods_add')
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

// Badges
  // search
Route::get('/admin/badges/search/{bid}', 'APIAdmin@badges_search')
      ->middleware('apicheck.auth')
      ->middleware('apicheck.admin.auth:badges_search');
  // add
Route::post('/admin/badges/add', 'APIAdmin@badges_add')
      ->middleware('apicheck.auth')
      ->middleware('apicheck.admin.auth:badges_add');
  // update
Route::post('/admin/badges/update', 'APIAdmin@badges_update')
      ->middleware('apicheck.auth')
      ->middleware('apicheck.admin.auth:badges_update');
  // delete
Route::post('/admin/badges/delete', 'APIAdmin@badges_delete')
      ->middleware('apicheck.auth')
      ->middleware('apicheck.admin.auth:badges_delete');

// Effects
  // search
Route::get('/admin/effects/search/{bid}', 'APIAdmin@effects_search')
      ->middleware('apicheck.auth')
      ->middleware('apicheck.admin.auth:effects_search');
  // add
Route::post('/admin/effects/add', 'APIAdmin@effects_add')
      ->middleware('apicheck.auth')
      ->middleware('apicheck.admin.auth:effects_add');
  // update
Route::post('/admin/effects/update', 'APIAdmin@effects_update')
      ->middleware('apicheck.auth')
      ->middleware('apicheck.admin.auth:effects_update');
  // delete
Route::post('/admin/effects/delete', 'APIAdmin@effects_delete')
      ->middleware('apicheck.auth')
      ->middleware('apicheck.admin.auth:effects_delete');
