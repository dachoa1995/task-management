<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Project;
use App\ProjectsUsers;
use App\User;
use App\Http\Resources\Project as ProjectResource;
use App\Http\Resources\ProjectList as ProjectList;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendMailable;
use Illuminate\Support\Facades\Gate;

class ProjectController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Project::class);
    }

    /**
     * プロジェクト一覧を取得
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        $projects = projectsUsers::with(['project'])
            ->where('user_id', '=', Auth::id())
            ->paginate(10);

        // return collection of projects as a resource
        return ProjectList::collection($projects);
    }

    /**
     * @param Request $request
     * @return ProjectResource
     */
    public function store(Request $request)
    {
        $project = new Project();

        $project->name = $request->input('name');
        $project->description = $request->input('description');

        if ($project->save()) {
            //プロジェクトを作成したら、ユーザーとの関係を作成
            $project_user = new ProjectsUsers();
            $project_user->user_id = Auth::id();
            $project_user->project_id = $project->id;
            if ($project_user->save()) {
                return new ProjectResource($project);
            }
        }
        return response()->json([
            'error' => 'Can not save your project'
        ], 500);
    }

    public function update(Project $project, Request $request)
    {
        $project->name = $request->input('name');
        $project->description = $request->input('description');

        if ($project->save()) {
            return new ProjectResource($project);
        }

        return response()->json([
            'error' => 'Can not save your project'
        ], 500);
    }

    /**
     * @param  \App\Project  $project
     */
    public function show(Project $project)
    {
        $project = Project::with('projectsUsers.user:id,name,avatarURL')
            ->where('id', '=', $project->id)
            ->first();

        return new ProjectResource($project);
    }

    /**
     * @param  \App\Project  $project
     * @return ProjectResource|\Illuminate\Http\JsonResponse
     */
    public function destroy(Project $project)
    {
        if ($project->delete()) {
            return response()->json([], 204);
        } else {
            return response()->json([
                'error' => 'Can not delete your project'
            ], 500);
        }
    }

    /*
     * プロジェクトに担当者をアサインする
     */
    public function assign(Request $request)
    {
        $project_id = $request->input('project_id');

        Gate::authorize('access-project', [$project_id]);

        $email = $request->input('email');

        // check if email is invalid
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'error' => 'Email is invalid'
            ], 500);
        }

        //check if user exist
        $user = User::firstOrCreate([
            'email' => $email,
        ]);

        // check if user is assigned
        $isAssigned = ProjectsUsers::where(['project_id' => $project_id, 'user_id' => $user->id])->exists();
        if ($isAssigned) {
            return response()->json([
                'error' => 'User is already assigned'
            ], 500);
        }

        $project_user = new ProjectsUsers();
        $project_user->user_id = $user->id;
        $project_user->project_id = $project_id;
        if ($project_user->save()) {
            Mail::to($email)->send(new SendMailable());
            return response()->json([], 204);
        }

        return response()->json([
            'error' => 'Can not assign user to your project'
        ], 500);

    }
}
