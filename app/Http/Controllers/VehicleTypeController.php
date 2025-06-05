<?php

namespace App\Http\Controllers;

use App\Models\VehicleType;
use Illuminate\Http\Request;

class VehicleTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return VehicleType::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:vehicle_types',
            'description' => 'nullable|string',
        ]);

        $vehicleType = VehicleType::create($request->all());
        return response()->json($vehicleType, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(VehicleType $vehicleType)
    {
        return $vehicleType;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, VehicleType $vehicleType)
    {
        $request->validate([
            'name' => 'sometimes|required|string|unique:vehicle_types,name,' . $vehicleType->id,
            'description' => 'nullable|string',
        ]);

        $vehicleType->update($request->all());
        return response()->json($vehicleType, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VehicleType $vehicleType)
    {
        $vehicleType->delete();
        return response()->json(null, 204);
    }
}