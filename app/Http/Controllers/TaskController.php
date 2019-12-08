<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Task;
use App\Status;
use App\TasksUsers;
use App\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\Task as TaskResource;
use Illuminate\Support\Facades\Gate;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('access-project', [$request->input('project_id')]);

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

        Gate::authorize('access-project', [$project_id]);

        $status_id = $request->input('status_id');
        $status = Status::where('project_id', '=', $project_id)
            ->where('id', '=', $status_id)
            ->doesntExist();
        if ($status) {
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
        Gate::authorize('access-project', [$request->input('project_id')]);

        $task_id = $request->input('task_id');

        $task = Task::with('tasksUsers.user:id,name,avatarURL')
            ->where('id', '=', $task_id)
            ->first();

        // return single article as a resource
        return new TaskResource($task);
    }

    public function destroy(Request $request)
    {
        Gate::authorize('access-project', [$request->input('project_id')]);

        $task_id = $request->input('task_id');

        $task = Task::findOrFail($task_id);

        $task_user = TasksUsers::where('task_id', '=', $task_id)
            ->where('user_id', '=', Auth::id());

        if ($task_user->delete() && $task->delete()) {
            return response()->json([], 204);
        } else {
            return response()->json([
                'error' => 'Can not delete your task'
            ], 500);
        }
    }

    /*
     * タスクに担当者をアサインする
     */
    public function assign(Request $request) {
        Gate::authorize('access-project', [$request->input('project_id')]);

        $task_id = $request->input('task_id');
        $email = $request->input('email');

        // check if email is invalid
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'error' => 'Email is invalid'
            ], 500);
        }

        //check if user exist
        $user = User::where(['email' => $email])->first();
        if (is_null($user)) {
            $user = User::firstOrCreate([
                'email' => $email,
            ]);
        }

        // check if user is assigned
        $isAssigned = TasksUsers::where(['task_id' => $task_id, 'user_id' => $user->id])->exists();
        if ($isAssigned) {
            return response()->json([
                'error' => 'User is already assigned'
            ], 500);
        }

        // check if task exist
        $task = Task::where('id', '=', $task_id)
            ->doesntExist();
        if ($task) {
            return response()->json([
                'error' => 'task is not exist'
            ], 404);
        }

        $task_user = new TasksUsers();
        $task_user->user_id = $user->id;
        $task_user->task_id = $task_id;
        if ($task_user->save()) {
            return response()->json([], 204);
        }

        return response()->json([
            'error' => 'Can not assign user to task'
        ], 500);

    }

    /*
     * ワークフロー間を移動の保存
     */
    public function moveTask(Request $request) {
        Gate::authorize('access-project', [$request->input('project_id')]);

        $task_id = $request->input('task_id');
        $change_to_status_id = $request->input('change_to_status_id');

        //check if status exist
        $doesntStatusExists = Status::where(['id' => $change_to_status_id])->doesntExist();
        if ($doesntStatusExists) {
            return response()->json([
                'error' => 'status is not exist'
            ], 404);
        }

        //check if task exist
        $task = Task::where(['id' => $task_id])->first();
        if (is_null($task)) {
            return response()->json([
                'error' => 'task is not exist'
            ], 404);
        }

        $task->status_id = $change_to_status_id;

        if ($task->save()) {
            return response()->json([], 204);
        }

        return response()->json([
            'error' => 'Can not change status'
        ], 500);

    }
}
