<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\ProjectsUsers;

class projectAuth
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
        $project_id = $request->input('project_id');
        $project_user = ProjectsUsers::select('id')
            ->where('project_id', '=', $project_id)
            ->where('user_id', '=', Auth::id())
            ->get();
        if (is_null($project_user) || count($project_user) === 0) {
            return response()->json([
                'error' => 'Can not access because of unauthorized'
            ], 403);
        }
        return $next($request);
    }
}
