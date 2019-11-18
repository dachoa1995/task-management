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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

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
