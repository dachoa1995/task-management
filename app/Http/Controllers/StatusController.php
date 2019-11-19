<?php

namespace App\Http\Controllers;

use App\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\Status as StatusResource;

class StatusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $project_id = $request->input('project_id');
        // get projects
        $status = Status::where('project_id', '=', $project_id)
            ->paginate(10);

        // return collection of projects as a resource
        return StatusResource::collection($status);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $status_id = $request->input('status_id');

        $status = $request->isMethod('PUT') ? Status::findOrFail($status_id) : $project = new Status();

        $status->project_id = $request->input('project_id');
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
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        $status_id = $request->input('status_id');

        // get project
        $status = Status::findOrFail($status_id);

        // return single article as a resource
        return new StatusResource($status);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $status_id = $request->input('status_id');

        // get status
        $status = Status::findOrFail($status_id);

        // return single article as a resource
        if ($status->delete()) {
            return response()->json([], 204);
        } else {
            return response()->json([
                'error' => 'Can not delete status'
            ], 500);
        }
    }
}
