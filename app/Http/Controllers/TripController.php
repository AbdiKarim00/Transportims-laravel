<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use Illuminate\Http\Request;

class TripController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Trip::with(['vehicle', 'driver', 'route', 'tripStatus', 'recordedBy'])->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'driver_id' => 'required|exists:drivers,id',
            'route_id' => 'required|exists:routes,id',
            'trip_status_id' => 'required|exists:trip_statuses,id',
            'start_time' => 'required|date',
            'end_time' => 'nullable|date|after:start_time',
            'actual_distance' => 'nullable|numeric|min:0',
            'actual_time' => 'nullable|integer|min:0',
            'recorded_by' => 'required|exists:users,id',
        ]);

        $trip = Trip::create($request->all());
        return response()->json($trip, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Trip $trip)
    {
        return $trip->load(['vehicle', 'driver', 'route', 'tripStatus', 'recordedBy']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Trip $trip)
    {
        $request->validate([
            'vehicle_id' => 'sometimes|required|exists:vehicles,id',
            'driver_id' => 'sometimes|required|exists:drivers,id',
            'route_id' => 'sometimes|required|exists:routes,id',
            'trip_status_id' => 'sometimes|required|exists:trip_statuses,id',
            'start_time' => 'sometimes|required|date',
            'end_time' => 'nullable|date|after:start_time',
            'actual_distance' => 'nullable|numeric|min:0',
            'actual_time' => 'nullable|integer|min:0',
            'recorded_by' => 'sometimes|required|exists:users,id',
        ]);

        $trip->update($request->all());
        return response()->json($trip, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Trip $trip)
    {
        $trip->delete();
        return response()->json(null, 204);
    }
}