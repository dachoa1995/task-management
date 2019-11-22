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

// プロジェクト一覧を取得
Route::middleware('auth:api')->get('projects', 'ProjectController@index');

// プロジェクト詳細を取得
Route::middleware('auth:api')->get('project', 'ProjectController@show')->middleware('projectAuth');

// プロジェクトを投稿
Route::middleware('auth:api')->post('project', 'ProjectController@store');

// プロジェクトを更新
Route::middleware('auth:api')->put('project', 'ProjectController@store')->middleware('projectAuth');

// プロジェクトを削除
Route::middleware('auth:api')->delete('project', 'ProjectController@destroy')->middleware('projectAuth');

// プロジェクトに担当者をアサインする
Route::middleware('auth:api')->post('assign_project', 'ProjectController@assign')->middleware('projectAuth');


// ワークフロー覧を取得
Route::middleware('auth:api')->get('status_list', 'StatusController@index')->middleware('projectAuth');

// ワークフロー詳細を取得
Route::middleware('auth:api')->get('status', 'StatusController@show')->middleware('projectAuth');

// ワークフローを投稿
Route::middleware('auth:api')->post('status', 'StatusController@store')->middleware('projectAuth');

// ワークフローを更新
Route::middleware('auth:api')->put('status', 'StatusController@store')->middleware('projectAuth');

// ワークフローを削除
Route::middleware('auth:api')->delete('status', 'StatusController@destroy')->middleware('projectAuth');


// タスクー覧を取得
Route::middleware('auth:api')->get('tasks', 'TaskController@index')->middleware('projectAuth');

// タスク詳細を取得
Route::middleware('auth:api')->get('task', 'TaskController@show')->middleware('projectAuth');

// タスクを投稿
Route::middleware('auth:api')->post('task', 'TaskController@store')->middleware('projectAuth');

// タスクを更新
Route::middleware('auth:api')->put('task', 'TaskController@store')->middleware('projectAuth');

// タスクを削除
Route::middleware('auth:api')->delete('task', 'TaskController@destroy')->middleware('projectAuth');

// タスクに担当者をアサインする
Route::middleware('auth:api')->post('assign_task', 'TaskController@assign')->middleware('projectAuth');


// タスクでコメントー覧を取得
Route::middleware('auth:api')->get('comments', 'CommentController@index')->middleware('projectAuth');

// タスクでコメントを投稿
Route::middleware('auth:api')->post('comment', 'CommentController@store')->middleware('projectAuth');

// タスクでコメントを更新
Route::middleware('auth:api')->put('comment', 'CommentController@update')->middleware('projectAuth')->middleware('commentAuth');

// タスクでコメントを削除
Route::middleware('auth:api')->delete('comment', 'CommentController@destroy')->middleware('projectAuth')->middleware('commentAuth');