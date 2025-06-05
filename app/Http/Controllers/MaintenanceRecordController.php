<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceRecord;
use Illuminate\Http\Request;

class MaintenanceRecordController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return MaintenanceRecord::with(['vehicle', 'maintenanceProvider', 'recordedBy'])->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'maintenance_provider_id' => 'required|exists:maintenance_providers,id',
            'service_date' => 'required|date',
            'cost' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'next_service_date' => 'nullable|date|after:service_date',
            'recorded_by' => 'required|exists:users,id',
        ]);

        $maintenanceRecord = MaintenanceRecord::create($request->all());
        return response()->json($maintenanceRecord, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(MaintenanceRecord $maintenanceRecord)
    {
        return $maintenanceRecord->load(['vehicle', 'maintenanceProvider', 'recordedBy']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MaintenanceRecord $maintenanceRecord)
    {
        $request->validate([
            'vehicle_id' => 'sometimes|required|exists:vehicles,id',
            'maintenance_provider_id' => 'sometimes|required|exists:maintenance_providers,id',
            'service_date' => 'sometimes|required|date',
            'cost' => 'sometimes|required|numeric|min:0',
            'description' => 'nullable|string',
            'next_service_date' => 'nullable|date|after:service_date',
            'recorded_by' => 'sometimes|required|exists:users,id',
        ]);

        $maintenanceRecord->update($request->all());
        return response()->json($maintenanceRecord, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MaintenanceRecord $maintenanceRecord)
    {
        $maintenanceRecord->delete();
        return response()->json(null, 204);
    }
}