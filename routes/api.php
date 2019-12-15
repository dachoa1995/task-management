<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

// プロジェクトに担当者をアサインする
Route::post('assign_project', 'ProjectController@assign');

// タスクに担当者をアサインする
Route::post('assign_task', 'TaskController@assign');

// タスクに担当者をアサインする
Route::post('move_task', 'TaskController@moveTask');

Route::apiResources([
    'projects' => 'ProjectController',
    'status' => 'StatusController',
    'tasks' => 'TaskController',
    'comments' => 'CommentController'
]);