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
    /**
     * プロジェクト一覧を取得
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        // get projects
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
        $project_id = $request->input('project_id');

        $project = $request->isMethod('PUT') ? Project::findOrFail($project_id) : $project = new Project();

        $project->name = $request->input('name');
        $project->description = $request->input('description');

        if ($project->save()) {
            //プロジェクトを作成したら、ユーザーとの関係を作成
            if ($request->isMethod('POST')) {
                $project_user = new ProjectsUsers();
                $project_user->user_id = Auth::id();
                $project_user->project_id = $project->id;
                if ($project_user->save()) {
                    return new ProjectResource($project);
                }
            } else {
                return new ProjectResource($project);
            }
        }
        return response()->json([
            'error' => 'Can not save your project'
        ], 500);
    }

    /**
     * @param $id
     * @return ProjectResource
     */
    public function show(Request $request)
    {
        $project_id = $request->input('project_id');

        // get project
        $project = Project::findOrFail($project_id);

        // return single article as a resource
        return new ProjectResource($project);
    }

    /**
     * @param $id
     * @return ProjectResource|\Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request)
    {
        $project_id = $request->input('project_id');

        // get project
        $project = Project::findOrFail($project_id);

        $project_user = ProjectsUsers::where('project_id', '=', $project_id)
            ->where('user_id', '=', Auth::id());

        // return single article as a resource
        if ($project->delete() && $project_user->delete()) {
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
    public function assign(Request $request) {
        $project_id = $request->input('project_id');
        $user_id = $request->input('user_id');
        $project_user = new ProjectsUsers();
        $project_user->user_id = $user_id;
        $project_user->project_id = $project_id;
        if ($project_user->save()) {
            return response()->json([], 204);
        }

        return response()->json([
            'error' => 'Can not assign user to your project'
        ], 500);

    }
}
