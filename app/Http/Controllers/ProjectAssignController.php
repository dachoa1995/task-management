<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Project;
use App\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendMailable;
use Illuminate\Support\Facades\Gate;

class ProjectAssignController extends Controller
{
    public function __invoke(Project $project, Request $request)
    {
        Gate::authorize('access-project', [$project->id]);

        $email = $request->input('email');

        // check if email is invalid
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'error' => 'Email is invalid'
            ], 500);
        }

        $user = User::firstOrCreate([
            'email' => $email,
        ]);

        // check if user is assigned
        $isAssigned = $project->users()->where(['user_id' => $user->id])->exists();
        if ($isAssigned) {
            return response()->json([
                'error' => 'User is already assigned'
            ], 500);
        }
        $project->users()->attach($user->id);

        Mail::to($email)->send(new SendMailable());
        return response()->json([], 204);
    }
}