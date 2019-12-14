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
Route::get('/user', function () {
    return Auth::user();
})->name('user');

// プロジェクト一覧を取得
Route::get('projects', 'ProjectController@index');

// プロジェクト詳細を取得
Route::get('project', 'ProjectController@show');

// プロジェクトを投稿
Route::post('project', 'ProjectController@store');

// プロジェクトを更新
Route::put('project', 'ProjectController@store');

// プロジェクトを削除
Route::delete('project', 'ProjectController@destroy');

// プロジェクトに担当者をアサインする
Route::post('assign_project', 'ProjectController@assign');


// ワークフロー覧を取得
Route::get('status_list', 'StatusController@index');

// ワークフローを投稿
Route::post('status', 'StatusController@store');

// ワークフローを更新
Route::put('status', 'StatusController@store');

// ワークフローを削除
Route::delete('status', 'StatusController@destroy');


// タスクー覧を取得
Route::get('tasks', 'TaskController@index');

// タスク詳細を取得
Route::get('task', 'TaskController@show');

// タスクを投稿
Route::post('task', 'TaskController@store');

// タスクを更新
Route::put('task', 'TaskController@store');

// タスクを削除
Route::delete('task', 'TaskController@destroy');

// タスクに担当者をアサインする
Route::post('assign_task', 'TaskController@assign');

// タスクに担当者をアサインする
Route::post('moveTask', 'TaskController@moveTask');


// タスクでコメントー覧を取得
Route::get('comments', 'CommentController@index');

// タスクでコメントを投稿
Route::post('comment', 'CommentController@store');

// タスクでコメントを更新
Route::put('comment', 'CommentController@update');

// タスクでコメントを削除
Route::delete('comment', 'CommentController@destroy');