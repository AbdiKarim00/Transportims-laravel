<?php

namespace App\Http\Controllers;

use App\Models\TripStatus;
use Illuminate\Http\Request;

class TripStatusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return TripStatus::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:trip_statuses',
            'description' => 'nullable|string',
        ]);

        $tripStatus = TripStatus::create($request->all());
        return response()->json($tripStatus, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(TripStatus $tripStatus)
    {
        return $tripStatus;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TripStatus $tripStatus)
    {
        $request->validate([
            'name' => 'sometimes|required|string|unique:trip_statuses,name,' . $tripStatus->id,
            'description' => 'nullable|string',
        ]);

        $tripStatus->update($request->all());
        return response()->json($tripStatus, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TripStatus $tripStatus)
    {
        $tripStatus->delete();
        return response()->json(null, 204);
    }
}