<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Task;
use App\Status;
use App\TasksUsers;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\Task as TaskResource;
use DateTime;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $status_id = $request->input('status_id');

        $tasks = Task::where('status_id', '=', $status_id)
            ->paginate(10);

        return TaskResource::collection($tasks);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // ワークフローが存在しているか、チェック
        $project_id = $request->input('project_id');
        $status_id = $request->input('status_id');
        $status = Status::select('id')
            ->where('project_id', '=', $project_id)
            ->where('id', '=', $status_id)
            ->get();
        if (is_null($status) || count($status) === 0) {
            return response()->json([
                'error' => 'status is not exist'
            ], 404);
        }

        $task_id = $request->input('task_id');

        $task = $request->isMethod('PUT') ? Task::findOrFail($task_id) : new Task();

        $task->status_id = $status_id;
        $task->name = $request->input('name');
        $task->description = $request->input('description');
        $task->deadline = $request->input('deadline');

        if ($task->save()) {
            //タスクを作成したら、ユーザーとの関係を作成
            if ($request->isMethod('POST')) {
                $task_user = new TasksUsers();
                $task_user->user_id = Auth::id();
                $task_user->task_id = $task->id;
                if ($task_user->save()) {
                    return new TaskResource($task);
                }
            } else {
                return new TaskResource($task);
            }
        }
        return response()->json([
            'error' => 'Can not save your task'
        ], 500);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        $task_id = $request->input('task_id');

        $task = Task::findOrFail($task_id);

        // return single article as a resource
        return new TaskResource($task);
    }

    public function destroy(Request $request)
    {
        $task_id = $request->input('task_id');

        $task = Task::findOrFail($task_id);

        $task_user = TasksUsers::where('task_id', '=', $task_id)
            ->where('user_id', '=', Auth::id());

        if ($task->delete() && $task_user->delete()) {
            return response()->json([], 204);
        } else {
            return response()->json([
                'error' => 'Can not delete your project'
            ], 500);
        }
    }

    /*
     * タスクに担当者をアサインする
     */
    public function assign(Request $request) {
        $task_id = $request->input('task_id');

        $task = Task::select('id')
            ->where('id', '=', $task_id)
            ->get();
        if (is_null($task) || count($task) === 0) {
            return response()->json([
                'error' => 'task is not exist'
            ], 404);
        }

        $user_id = $request->input('user_id');
        $task_user = new TasksUsers();
        $task_user->user_id = $user_id;
        $task_user->task_id = $task_id;
        if ($task_user->save()) {
            return response()->json([], 204);
        }

        return response()->json([
            'error' => 'Can not assign user to task'
        ], 500);

    }
}
