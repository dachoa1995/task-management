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
Route::get('projects', 'ProjectController@index');

// プロジェクト詳細を取得
Route::get('project/{id}', 'ProjectController@show');

// プロジェクトを投稿
Route::post('project', 'ProjectController@store');

// プロジェクトを更新
Route::put('project', 'ProjectController@store');

// プロジェクトを削除
Route::delete('project/{id}', 'ProjectController@destroy');