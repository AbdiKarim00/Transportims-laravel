<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceProvider;
use Illuminate\Http\Request;

class MaintenanceProviderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return MaintenanceProvider::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:maintenance_providers',
            'contact_person' => 'nullable|string',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string',
            'address' => 'nullable|string',
        ]);

        $maintenanceProvider = MaintenanceProvider::create($request->all());
        return response()->json($maintenanceProvider, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(MaintenanceProvider $maintenanceProvider)
    {
        return $maintenanceProvider;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MaintenanceProvider $maintenanceProvider)
    {
        $request->validate([
            'name' => 'sometimes|required|string|unique:maintenance_providers,name,' . $maintenanceProvider->id,
            'contact_person' => 'nullable|string',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string',
            'address' => 'nullable|string',
        ]);

        $maintenanceProvider->update($request->all());
        return response()->json($maintenanceProvider, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MaintenanceProvider $maintenanceProvider)
    {
        $maintenanceProvider->delete();
        return response()->json(null, 204);
    }
}