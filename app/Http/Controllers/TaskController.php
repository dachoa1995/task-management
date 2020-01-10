<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Task;
use App\Status;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\Task as TaskResource;
use Illuminate\Support\Facades\Gate;

class TaskController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Task::class);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $status = Status::findOrFail($request->input('status_id'));

        Gate::authorize('access-project', [$status->project_id]);

        $tasks = Task::where('status_id', '=', $status->id)
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

        $task = new Task();

        $task->status_id = $status_id;
        $task->name = $request->input('name');
        $task->description = $request->input('description');
        $task->deadline = $request->input('deadline');

        if ($task->save()) {
            //タスクを作成したら、ユーザーとの関係を作成
            $task->users()->attach(Auth::id());

            return new TaskResource($task);
        }
        return response()->json([
            'error' => 'Can not save your task'
        ], 500);
    }

    public function update(Task $task, Request $request)
    {
        $task->name = $request->input('name');
        $task->description = $request->input('description');
        $task->deadline = $request->input('deadline');

        if ($task->save()) {
            return new TaskResource($task);
        }
        return response()->json([
            'error' => 'Can not save your task'
        ], 500);
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        $task->load('tasksUsers.user:id,name,avatarURL');

        if (is_null($task)) {
            return response()->json([
                'error' => 'Task does not exists'
            ], 404);
        }

        // return single article as a resource
        return new TaskResource($task);
    }

    public function destroy(Task $task)
    {
        if ($task->delete()) {
            return response()->json([], 204);
        } else {
            return response()->json([
                'error' => 'Can not delete your task'
            ], 500);
        }
    }
}
