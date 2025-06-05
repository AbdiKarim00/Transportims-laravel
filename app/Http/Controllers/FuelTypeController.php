<?php

namespace App\Http\Controllers;

use App\Models\FuelType;
use Illuminate\Http\Request;

class FuelTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return FuelType::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:fuel_types',
            'description' => 'nullable|string',
        ]);

        $fuelType = FuelType::create($request->all());
        return response()->json($fuelType, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(FuelType $fuelType)
    {
        return $fuelType;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FuelType $fuelType)
    {
        $request->validate([
            'name' => 'sometimes|required|string|unique:fuel_types,name,' . $fuelType->id,
            'description' => 'nullable|string',
        ]);

        $fuelType->update($request->all());
        return response()->json($fuelType, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FuelType $fuelType)
    {
        $fuelType->delete();
        return response()->json(null, 204);
    }
}