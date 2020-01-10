<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Project;
use App\ProjectsUsers;
use App\Http\Resources\Project as ProjectResource;
use App\Http\Resources\ProjectList as ProjectList;
use Illuminate\Support\Facades\Auth;

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
        $projects = ProjectsUsers::with(['project'])
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
            $project->users()->attach(Auth::id());

            return new ProjectResource($project);
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
     * @param  \App\Project $project
     */
    public function show(Project $project)
    {
        $project->load('projectsUsers.user:id,name,avatarURL');

        return new ProjectResource($project);
    }

    /**
     * @param  \App\Project $project
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
}
