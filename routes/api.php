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
// Login
Route::post('/login', 'APIUser@login');
// Logout
Route::get('/logout', 'APIUser@logout')
      ->middleware('apicheck.auth');

// Check in v2
Route::post('/user/checkin/clean', 'APICheckIn@clean')
      ->middleware('apicheck.auth');

// Security
Route::post('/user/security/password', 'APIUser@security_change_password')
      ->middleware('apicheck.auth');
Route::post('/user/security/username', 'APIUser@security_change_username')
      ->middleware('apicheck.auth');

// Purchase
Route::post('/purchase', 'APIUser@purchase')
      ->middleware('apicheck.auth');

// Blend
Route::post('/blend', 'APIUser@blend')
      ->middleware('apicheck.auth');

// Recycle
Route::post('/recycle', 'APIUser@recycle')
      ->middleware('apicheck.auth');

// Worker
  // redeem worker
Route::get('/worker/redeem', 'APIUser@worker_redeem')
      ->middleware('apicheck.auth');
  // get worker list
Route::post('/worker', 'APIUser@worker')
      ->middleware('apicheck.auth');
  // get specific worker list
Route::post('/worker/assign_query', 'APIUser@worker_assign_query')
      ->middleware('apicheck.auth');
  // assign worker to a field
Route::post('/worker/assign', 'APIUser@worker_assign')
      ->middleware('apicheck.auth');
  // withdraw worker from a field
Route::post('/worker/withdraw', 'APIUser@worker_withdraw')
      ->middleware('apicheck.auth');
  // query anticipation of harvest
Route::post('/worker/harvest_query', 'APIUser@worker_harvest_query')
      ->middleware('apicheck.auth');
  // harvest
Route::post('/worker/harvest', 'APIUser@worker_harvest')
      ->middleware('apicheck.auth');
  // query worker upgrade demands
Route::post('/worker/upgrade_query', 'APIUser@worker_upgrade_query')
      ->middleware('apicheck.auth');
  // worker upgrade
Route::post('/worker/upgrade', 'APIUser@worker_upgrade')
      ->middleware('apicheck.auth');

// =======ADMIN=======
// 数据迁移
  // 积分迁移
Route::get('/admin/migrate/points', 'APIAdmin@migrate_points')
      ->middleware('apicheck.auth')
      ->middleware('apicheck.admin.auth:site_owner');
  // 勋章迁移
Route::get('/admin/migrate/badges', 'APIAdmin@migrate_badges')
      ->middleware('apicheck.auth')
      ->middleware('apicheck.admin.auth:site_owner');
// // Conpensate
// Route::post('/admin/conpensate', 'APIAdmin@conpensate')
//       ->middleware('apicheck.auth')
//       ->middleware('apicheck.admin.auth:conpensate_add');
//
// // Activity
//   // search
// Route::get('/admin/activity/search/{aid}', 'APIAdmin@activity_search')
//       ->middleware('apicheck.auth')
//       ->middleware('apicheck.admin.auth:activity_search');
//   // add
// Route::post('/admin/activity/add', 'APIAdmin@activity_add')
//       ->middleware('apicheck.auth')
//       ->middleware('apicheck.admin.auth:activity_add');
//   // update
// Route::post('/admin/activity/update', 'APIAdmin@activity_update')
//       ->middleware('apicheck.auth')
//       ->middleware('apicheck.admin.auth:activity_update');
//   // delete
// Route::post('/admin/activity/delete', 'APIAdmin@activity_delete')
//       ->middleware('apicheck.auth')
//       ->middleware('apicheck.admin.auth:activity_delete');
//
// // Goods
//   // search
// Route::get('/admin/goods/search/{gid}', 'APIAdmin@goods_search')
//       ->middleware('apicheck.auth')
//       ->middleware('apicheck.admin.auth:goods_search');
//   // add
// Route::post('/admin/goods/add', 'APIAdmin@goods_add')
//       ->middleware('apicheck.auth')
//       ->middleware('apicheck.admin.auth:goods_add');
//   // update
// Route::post('/admin/goods/update', 'APIAdmin@goods_update')
//       ->middleware('apicheck.auth')
//       ->middleware('apicheck.admin.auth:goods_update');
//   // delete
// Route::post('/admin/goods/delete', 'APIAdmin@goods_delete')
//       ->middleware('apicheck.auth')
//       ->middleware('apicheck.admin.auth:goods_delete');
//
// // Optmize
// Route::get('/admin/optimize/{project}', 'APIAdminOptmize@optmize')
//       ->middleware('apicheck.auth')
//       ->middleware('apicheck.admin.auth:site_optmize');
//
// // Notices
//   // search
// Route::get('/admin/notices/search/{nid}', 'APIAdmin@notices_search')
//       ->middleware('apicheck.auth')
//       ->middleware('apicheck.admin.auth:notices_search');
//   // add
// Route::post('/admin/notices/add', 'APIAdmin@notices_add')
//       ->middleware('apicheck.auth')
//       ->middleware('apicheck.admin.auth:notices_add');
//   // update
// Route::post('/admin/notices/update', 'APIAdmin@notices_update')
//       ->middleware('apicheck.auth')
//       ->middleware('apicheck.admin.auth:notices_update');
//   // delete
// Route::post('/admin/notices/delete', 'APIAdmin@notices_delete')
//       ->middleware('apicheck.auth')
//       ->middleware('apicheck.admin.auth:notices_delete');
//
// // Users
//   // search
// Route::get('/admin/users/search/{uid}', 'APIAdmin@users_search')
//       ->middleware('apicheck.auth')
//       ->middleware('apicheck.admin.auth:user_search');
// Route::get('/admin/users/search_username/{username}', 'APIAdmin@users_search_username')
//       ->middleware('apicheck.auth')
//       ->middleware('apicheck.admin.auth:user_search');
// Route::get('/admin/users/points/{uid}', 'APIAdmin@users_points_get')
//       ->middleware('apicheck.auth')
//       ->middleware('apicheck.admin.auth:user_search');
//   // update
// Route::post('/admin/users/update', 'APIAdmin@users_update')
//       ->middleware('apicheck.auth')
//       ->middleware('apicheck.admin.auth:user_manage');

// Admins 管理权限管理
  // add
Route::post('/admin/rights/add', 'APIAdmin@admins_rights_add')
      ->middleware('apicheck.auth')
      ->middleware('apicheck.admin.auth:site_owner');
  // del
Route::delete('/admin/rights', 'APIAdmin@admins_rights_del')
      ->middleware('apicheck.auth')
      ->middleware('apicheck.admin.auth:site_owner');
// Admins 管理等级管理
  // update
Route::post('/admin/level', 'APIAdmin@admin_level_update')
      ->middleware('apicheck.auth')
      ->middleware('apicheck.admin.auth:admin_level_update');
  // update
Route::delete('/admin/level', 'APIAdmin@admin_level_remove')
      ->middleware('apicheck.auth')
      ->middleware('apicheck.admin.auth:admin_level_remove');

// // Badges
//   // search
// Route::get('/admin/badges/search/{bid}', 'APIAdmin@badges_search')
//       ->middleware('apicheck.auth')
//       ->middleware('apicheck.admin.auth:badges_search');
//   // add
// Route::post('/admin/badges/add', 'APIAdmin@badges_add')
//       ->middleware('apicheck.auth')
//       ->middleware('apicheck.admin.auth:badges_add');
//   // update
// Route::post('/admin/badges/update', 'APIAdmin@badges_update')
//       ->middleware('apicheck.auth')
//       ->middleware('apicheck.admin.auth:badges_update');
//   // delete
// Route::post('/admin/badges/delete', 'APIAdmin@badges_delete')
//       ->middleware('apicheck.auth')
//       ->middleware('apicheck.admin.auth:badges_delete');
//
// // Effects
//   // search
// Route::get('/admin/effects/search/{bid}', 'APIAdmin@effects_search')
//       ->middleware('apicheck.auth')
//       ->middleware('apicheck.admin.auth:effects_search');
//   // add
// Route::post('/admin/effects/add', 'APIAdmin@effects_add')
//       ->middleware('apicheck.auth')
//       ->middleware('apicheck.admin.auth:effects_add');
//   // update
// Route::post('/admin/effects/update', 'APIAdmin@effects_update')
//       ->middleware('apicheck.auth')
//       ->middleware('apicheck.admin.auth:effects_update');
//   // delete
// Route::post('/admin/effects/delete', 'APIAdmin@effects_delete')
//       ->middleware('apicheck.auth')
//       ->middleware('apicheck.admin.auth:effects_delete');
