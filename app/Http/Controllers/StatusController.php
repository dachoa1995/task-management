<?php

namespace App\Http\Controllers;

use App\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\Status as StatusResource;
use Illuminate\Support\Facades\Gate;

class StatusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $project_id = $request->input('project_id');

        Gate::authorize('access-project', [$project_id]);

        $status = Status::with(['task'])->where('project_id', '=', $project_id)
            ->paginate(10);

        return StatusResource::collection($status);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $project_id = $request->input('project_id');

        Gate::authorize('access-project', [$project_id]);

        $status = new Status();

        $status->project_id = $project_id;
        $status->name = $request->input('name');
        $status->order = $request->input('order');

        if ($status->save()) {
            return new StatusResource($status);
        }
        return response()->json([
            'error' => 'Can not save your status'
        ], 500);
    }

    public function update($status_id, Request $request)
    {
        $project_id = $request->input('project_id');

        Gate::authorize('access-project', [$project_id]);

        $status = Status::findOrFail($status_id);

        $status->project_id = $project_id;
        $status->name = $request->input('name');
        $status->order = $request->input('order');

        if ($status->save()) {
            return new StatusResource($status);
        }
        return response()->json([
            'error' => 'Can not save your status'
        ], 500);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($status_id, Request $request)
    {
        Gate::authorize('access-project', [$request->input('project_id')]);

        $status = Status::findOrFail($status_id);

        if ($status->delete()) {
            return response()->json([], 204);
        } else {
            return response()->json([
                'error' => 'Can not delete status'
            ], 500);
        }
    }
}
