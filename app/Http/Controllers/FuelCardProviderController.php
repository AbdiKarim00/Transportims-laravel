<?php

namespace App\Http\Controllers;

use App\Models\FuelCardProvider;
use Illuminate\Http\Request;

class FuelCardProviderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return FuelCardProvider::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:fuel_card_providers',
            'contact_person' => 'nullable|string',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string',
            'address' => 'nullable|string',
        ]);

        $fuelCardProvider = FuelCardProvider::create($request->all());
        return response()->json($fuelCardProvider, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(FuelCardProvider $fuelCardProvider)
    {
        return $fuelCardProvider;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FuelCardProvider $fuelCardProvider)
    {
        $request->validate([
            'name' => 'sometimes|required|string|unique:fuel_card_providers,name,' . $fuelCardProvider->id,
            'contact_person' => 'nullable|string',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string',
            'address' => 'nullable|string',
        ]);

        $fuelCardProvider->update($request->all());
        return response()->json($fuelCardProvider, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FuelCardProvider $fuelCardProvider)
    {
        $fuelCardProvider->delete();
        return response()->json(null, 204);
    }
}