<?php

namespace App\Http\Controllers;

use App\Models\VehicleAssignment;
use Illuminate\Http\Request;

class VehicleAssignmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return VehicleAssignment::with(['vehicle', 'driver', 'assignedBy'])->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'driver_id' => 'required|exists:drivers,id',
            'assignment_date' => 'required|date',
            'return_date' => 'nullable|date|after_or_equal:assignment_date',
            'assigned_by' => 'required|exists:users,id',
        ]);

        $vehicleAssignment = VehicleAssignment::create($request->all());
        return response()->json($vehicleAssignment, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(VehicleAssignment $vehicleAssignment)
    {
        return $vehicleAssignment->load(['vehicle', 'driver', 'assignedBy']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, VehicleAssignment $vehicleAssignment)
    {
        $request->validate([
            'vehicle_id' => 'sometimes|required|exists:vehicles,id',
            'driver_id' => 'sometimes|required|exists:drivers,id',
            'assignment_date' => 'sometimes|required|date',
            'return_date' => 'nullable|date|after_or_equal:assignment_date',
            'assigned_by' => 'sometimes|required|exists:users,id',
        ]);

        $vehicleAssignment->update($request->all());
        return response()->json($vehicleAssignment, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VehicleAssignment $vehicleAssignment)
    {
        $vehicleAssignment->delete();
        return response()->json(null, 204);
    }
}