<?php

namespace App\Http\Controllers;

use App\Models\FuelCardTransaction;
use Illuminate\Http\Request;

class FuelCardTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return FuelCardTransaction::with(['fuelCard', 'fuelType'])->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'fuel_card_id' => 'required|exists:fuel_cards,id',
            'transaction_date' => 'required|date',
            'fuel_type_id' => 'required|exists:fuel_types,id',
            'volume_liters' => 'required|numeric|min:0',
            'unit_price' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'odometer_reading' => 'nullable|integer|min:0',
            'location' => 'nullable|string',
        ]);

        $fuelCardTransaction = FuelCardTransaction::create($request->all());
        return response()->json($fuelCardTransaction, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(FuelCardTransaction $fuelCardTransaction)
    {
        return $fuelCardTransaction->load(['fuelCard', 'fuelType']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FuelCardTransaction $fuelCardTransaction)
    {
        $request->validate([
            'fuel_card_id' => 'sometimes|required|exists:fuel_cards,id',
            'transaction_date' => 'sometimes|required|date',
            'fuel_type_id' => 'sometimes|required|exists:fuel_types,id',
            'volume_liters' => 'sometimes|required|numeric|min:0',
            'unit_price' => 'sometimes|required|numeric|min:0',
            'total_amount' => 'sometimes|required|numeric|min:0',
            'odometer_reading' => 'nullable|integer|min:0',
            'location' => 'nullable|string',
        ]);

        $fuelCardTransaction->update($request->all());
        return response()->json($fuelCardTransaction, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FuelCardTransaction $fuelCardTransaction)
    {
        $fuelCardTransaction->delete();
        return response()->json(null, 204);
    }
}