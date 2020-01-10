<?php
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

Route::middleware(['auth:api'])->group(function () {
    // プロジェクトに担当者をアサインする
    Route::post('assign_project/{project}', 'ProjectAssignController');

    // タスクに担当者をアサインする
    Route::post('assign_task/{task}', 'TaskAssignController');

    // ワークフロー間を移動
    Route::post('move/{task}/{status}', 'MoveTaskController');

    Route::apiResources([
        'projects' => 'ProjectController',
        'status' => 'StatusController',
        'tasks' => 'TaskController',
        'comments' => 'CommentController'
    ]);
});