<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Task;
use App\User;

class TaskAssignController extends Controller
{
    public function __invoke(Task $task, Request $request)
    {
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
        $isAssigned = $task->users()->where(['user_id' => $user->id])->exists();
        if ($isAssigned) {
            return response()->json([
                'error' => 'User is already assigned'
            ], 500);
        }

        $task->users()->attach($user->id);
        return response()->json([], 204);
    }
}
