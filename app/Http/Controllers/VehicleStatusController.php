<?php

namespace App\Http\Controllers;

use App\Models\VehicleStatus;
use Illuminate\Http\Request;

class VehicleStatusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return VehicleStatus::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:vehicle_statuses',
            'description' => 'nullable|string',
        ]);

        $vehicleStatus = VehicleStatus::create($request->all());
        return response()->json($vehicleStatus, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(VehicleStatus $vehicleStatus)
    {
        return $vehicleStatus;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, VehicleStatus $vehicleStatus)
    {
        $request->validate([
            'name' => 'sometimes|required|string|unique:vehicle_statuses,name,' . $vehicleStatus->id,
            'description' => 'nullable|string',
        ]);

        $vehicleStatus->update($request->all());
        return response()->json($vehicleStatus, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VehicleStatus $vehicleStatus)
    {
        $vehicleStatus->delete();
        return response()->json(null, 204);
    }
}