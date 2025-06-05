<?php

namespace App\Http\Controllers;

use App\Models\FuelCard;
use Illuminate\Http\Request;

class FuelCardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return FuelCard::with(['provider', 'type', 'status', 'vehicle', 'driver'])->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'card_number' => 'required|string|unique:fuel_cards',
            'provider_id' => 'required|exists:fuel_card_providers,id',
            'type_id' => 'required|exists:fuel_card_types,id',
            'status_id' => 'required|exists:fuel_card_statuses,id',
            'issue_date' => 'required|date',
            'expiry_date' => 'required|date|after:issue_date',
            'pin_code' => 'nullable|string',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'driver_id' => 'nullable|exists:drivers,id',
            'daily_limit' => 'nullable|numeric|min:0',
            'monthly_limit' => 'nullable|numeric|min:0',
        ]);

        $fuelCard = FuelCard::create($request->all());
        return response()->json($fuelCard, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(FuelCard $fuelCard)
    {
        return $fuelCard->load(['provider', 'type', 'status', 'vehicle', 'driver']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FuelCard $fuelCard)
    {
        $request->validate([
            'card_number' => 'sometimes|required|string|unique:fuel_cards,card_number,' . $fuelCard->id,
            'provider_id' => 'sometimes|required|exists:fuel_card_providers,id',
            'type_id' => 'sometimes|required|exists:fuel_card_types,id',
            'status_id' => 'sometimes|required|exists:fuel_card_statuses,id',
            'issue_date' => 'sometimes|required|date',
            'expiry_date' => 'sometimes|required|date|after:issue_date',
            'pin_code' => 'nullable|string',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'driver_id' => 'nullable|exists:drivers,id',
            'daily_limit' => 'nullable|numeric|min:0',
            'monthly_limit' => 'nullable|numeric|min:0',
        ]);

        $fuelCard->update($request->all());
        return response()->json($fuelCard, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FuelCard $fuelCard)
    {
        $fuelCard->delete();
        return response()->json(null, 204);
    }
}