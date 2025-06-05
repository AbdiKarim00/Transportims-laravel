<?php

namespace App\Http\Controllers;

use App\Models\FuelCardHistory;
use Illuminate\Http\Request;

class FuelCardHistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return FuelCardHistory::with(['fuelCard', 'user'])->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'fuel_card_id' => 'required|exists:fuel_cards,id',
            'user_id' => 'required|exists:users,id',
            'action' => 'required|string|max:255',
            'details' => 'nullable|string',
        ]);

        $fuelCardHistory = FuelCardHistory::create($request->all());
        return response()->json($fuelCardHistory, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(FuelCardHistory $fuelCardHistory)
    {
        return $fuelCardHistory->load(['fuelCard', 'user']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FuelCardHistory $fuelCardHistory)
    {
        $request->validate([
            'fuel_card_id' => 'sometimes|required|exists:fuel_cards,id',
            'user_id' => 'sometimes|required|exists:users,id',
            'action' => 'sometimes|required|string|max:255',
            'details' => 'nullable|string',
        ]);

        $fuelCardHistory->update($request->all());
        return response()->json($fuelCardHistory, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FuelCardHistory $fuelCardHistory)
    {
        $fuelCardHistory->delete();
        return response()->json(null, 204);
    }
}