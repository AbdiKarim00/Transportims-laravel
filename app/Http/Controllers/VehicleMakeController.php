<?php

namespace App\Http\Controllers;

use App\Models\VehicleMake;
use Illuminate\Http\Request;

class VehicleMakeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return VehicleMake::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:vehicle_makes',
            'country_of_origin' => 'nullable|string',
        ]);

        $vehicleMake = VehicleMake::create($request->all());
        return response()->json($vehicleMake, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(VehicleMake $vehicleMake)
    {
        return $vehicleMake;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, VehicleMake $vehicleMake)
    {
        $request->validate([
            'name' => 'sometimes|required|string|unique:vehicle_makes,name,' . $vehicleMake->id,
            'country_of_origin' => 'nullable|string',
        ]);

        $vehicleMake->update($request->all());
        return response()->json($vehicleMake, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VehicleMake $vehicleMake)
    {
        $vehicleMake->delete();
        return response()->json(null, 204);
    }
}