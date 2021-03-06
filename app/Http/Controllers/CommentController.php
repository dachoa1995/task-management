<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Task;
use App\Comment;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\Comment as CommentResource;
use Illuminate\Support\Facades\Gate;

class CommentController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Comment::class);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('access-project', [$request->input('project_id')]);

        $task_id = $request->input('task_id');

        $tasks = Comment::with('user:id,name,avatarURL')
            ->where('task_id', '=', $task_id)
            ->paginate(10);

        return CommentResource::collection($tasks);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize('access-project', [$request->input('project_id')]);

        //タスクが存在しているか、チェック
        $task_id = $request->input('task_id');
        $task = Task::where('id', '=', $task_id)
            ->doesntExist();

        if ($task) {
            return response()->json([
                'error' => 'task is not exist'
            ], 404);
        }

        $comment = new Comment();

        $comment->user_id = Auth::id();
        $comment->task_id = $task_id;
        $comment->content = $request->input('content');

        if ($comment->save()) {
            return new CommentResource($comment);
        }
        return response()->json([
            'error' => 'Can not save your task'
        ], 500);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Comment $comment, Request $request)
    {
        $comment->content = $request->input('content');

        if ($comment->save()) {
            return new CommentResource($comment);
        }

        return response()->json([
            'error' => 'Can not save your comment'
        ], 500);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Comment $comment)
    {
        if ($comment->delete()) {
            return response()->json([], 204);
        } else {
            return response()->json([
                'error' => 'Can not delete comment'
            ], 500);
        }
    }
}
