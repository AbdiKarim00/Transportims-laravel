<?php

namespace App\Http\Controllers;

use App\Models\VehicleLog;
use Illuminate\Http\Request;

class VehicleLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return VehicleLog::with(['vehicle', 'recordedBy'])->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'log_type' => 'required|string|max:255',
            'description' => 'nullable|string',
            'log_date' => 'required|date',
            'recorded_by' => 'required|exists:users,id',
        ]);

        $vehicleLog = VehicleLog::create($request->all());
        return response()->json($vehicleLog, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(VehicleLog $vehicleLog)
    {
        return $vehicleLog->load(['vehicle', 'recordedBy']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, VehicleLog $vehicleLog)
    {
        $request->validate([
            'vehicle_id' => 'sometimes|required|exists:vehicles,id',
            'log_type' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'log_date' => 'sometimes|required|date',
            'recorded_by' => 'sometimes|required|exists:users,id',
        ]);

        $vehicleLog->update($request->all());
        return response()->json($vehicleLog, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VehicleLog $vehicleLog)
    {
        $vehicleLog->delete();
        return response()->json(null, 204);
    }
}