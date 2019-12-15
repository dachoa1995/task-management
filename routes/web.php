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

// ログインユーザー
Route::get('/user', function () {
    return Auth::user();
})->name('user');

Route::get('logout', 'Auth\LoginController@logout');
Route::get('auth/google', 'Auth\LoginController@redirectToProvider');
Route::get('auth/google/callback', 'Auth\LoginController@handleProviderCallback');

Route::get('/{any?}', function () {
    return view('index');
})->where('any', '.+');