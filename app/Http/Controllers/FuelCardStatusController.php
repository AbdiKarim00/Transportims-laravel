<?php

namespace App\Http\Controllers;

use App\Models\FuelCardStatus;
use Illuminate\Http\Request;

class FuelCardStatusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return FuelCardStatus::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:fuel_card_statuses',
            'description' => 'nullable|string',
        ]);

        $fuelCardStatus = FuelCardStatus::create($request->all());
        return response()->json($fuelCardStatus, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(FuelCardStatus $fuelCardStatus)
    {
        return $fuelCardStatus;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FuelCardStatus $fuelCardStatus)
    {
        $request->validate([
            'name' => 'sometimes|required|string|unique:fuel_card_statuses,name,' . $fuelCardStatus->id,
            'description' => 'nullable|string',
        ]);

        $fuelCardStatus->update($request->all());
        return response()->json($fuelCardStatus, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FuelCardStatus $fuelCardStatus)
    {
        $fuelCardStatus->delete();
        return response()->json(null, 204);
    }
}