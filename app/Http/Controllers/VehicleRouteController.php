<?php

namespace App\Http\Controllers;

use App\Models\VehicleRoute;
use Illuminate\Http\Request;

class VehicleRouteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return VehicleRoute::with(['vehicle', 'route'])->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'route_id' => 'required|exists:routes,id',
            'assigned_date' => 'required|date',
            'completed_date' => 'nullable|date|after_or_equal:assigned_date',
        ]);

        $vehicleRoute = VehicleRoute::create($request->all());
        return response()->json($vehicleRoute, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(VehicleRoute $vehicleRoute)
    {
        return $vehicleRoute->load(['vehicle', 'route']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, VehicleRoute $vehicleRoute)
    {
        $request->validate([
            'vehicle_id' => 'sometimes|required|exists:vehicles,id',
            'route_id' => 'sometimes|required|exists:routes,id',
            'assigned_date' => 'sometimes|required|date',
            'completed_date' => 'nullable|date|after_or_equal:assigned_date',
        ]);

        $vehicleRoute->update($request->all());
        return response()->json($vehicleRoute, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VehicleRoute $vehicleRoute)
    {
        $vehicleRoute->delete();
        return response()->json(null, 204);
    }
}