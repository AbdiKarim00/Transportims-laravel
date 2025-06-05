<?php

namespace App\Http\Controllers;

use App\Models\FuelCardType;
use Illuminate\Http\Request;

class FuelCardTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return FuelCardType::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:fuel_card_types',
            'description' => 'nullable|string',
        ]);

        $fuelCardType = FuelCardType::create($request->all());
        return response()->json($fuelCardType, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(FuelCardType $fuelCardType)
    {
        return $fuelCardType;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FuelCardType $fuelCardType)
    {
        $request->validate([
            'name' => 'sometimes|required|string|unique:fuel_card_types,name,' . $fuelCardType->id,
            'description' => 'nullable|string',
        ]);

        $fuelCardType->update($request->all());
        return response()->json($fuelCardType, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FuelCardType $fuelCardType)
    {
        $fuelCardType->delete();
        return response()->json(null, 204);
    }
}