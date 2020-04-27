<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Task;
use App\Status;

class MoveTaskController extends Controller
{
    public function __invoke(Task $task, Status $status, Request $request)
    {
        $task->status()->associate($status);

        if ($task->save()) {
            return response()->json([], 204);
        }

        return response()->json([
            'error' => 'Can not change status'
        ], 500);
    }
}