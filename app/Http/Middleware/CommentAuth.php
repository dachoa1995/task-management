<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Comment;

class CommentAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $comment_id = $request->input('comment_id');
        $comment = Comment::select('id')
            ->where('id', '=', $comment_id)
            ->where('user_id', '=', Auth::id())
            ->get();
        if (is_null($comment) || count($comment) === 0) {
            return response()->json([
                'error' => 'Can not access comment because of unauthorized or comment is not exist'
            ], 403);
        }
        return $next($request);
    }
}
