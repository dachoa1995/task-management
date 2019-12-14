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

// ログインユーザー
Route::middleware('auth:api')->get('/user', function () {
    return Auth::user();
})->name('user');

// プロジェクト一覧を取得
Route::middleware('auth:api')->get('projects', 'ProjectController@index');

// プロジェクト詳細を取得
Route::middleware('auth:api')->get('project', 'ProjectController@show');

// プロジェクトを投稿
Route::middleware('auth:api')->post('project', 'ProjectController@store');

// プロジェクトを更新
Route::middleware('auth:api')->post('change_project', 'ProjectController@update');

// プロジェクトを削除
Route::middleware('auth:api')->post('delete_project', 'ProjectController@destroy');

// プロジェクトに担当者をアサインする
Route::middleware('auth:api')->post('assign_project', 'ProjectController@assign');


// ワークフロー覧を取得
Route::middleware('auth:api')->get('status_list', 'StatusController@index');

// ワークフローを投稿
Route::middleware('auth:api')->post('status', 'StatusController@store');

// ワークフローを更新
Route::middleware('auth:api')->post('change_status', 'StatusController@update');

// ワークフローを削除
Route::middleware('auth:api')->post('delete_status', 'StatusController@destroy');


// タスクー覧を取得
Route::middleware('auth:api')->get('tasks', 'TaskController@index');

// タスク詳細を取得
Route::middleware('auth:api')->get('task', 'TaskController@show');

// タスクを投稿
Route::middleware('auth:api')->post('task', 'TaskController@store');

// タスクを更新
Route::middleware('auth:api')->post('change_task', 'TaskController@update');

// タスクを削除
Route::middleware('auth:api')->post('delete_task', 'TaskController@destroy');

// タスクに担当者をアサインする
Route::middleware('auth:api')->post('assign_task', 'TaskController@assign');

// タスクに担当者をアサインする
Route::middleware('auth:api')->post('moveTask', 'TaskController@moveTask');


// タスクでコメントー覧を取得
Route::middleware('auth:api')->get('comments', 'CommentController@index');

// タスクでコメントを投稿
Route::middleware('auth:api')->post('comment', 'CommentController@store');

// タスクでコメントを更新
Route::middleware('auth:api')->put('comment', 'CommentController@update');

// タスクでコメントを削除
Route::middleware('auth:api')->delete('comment', 'CommentController@destroy');