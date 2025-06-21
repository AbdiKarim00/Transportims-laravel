<?php

namespace App\Http\Controllers;

use App\Models\DriverStatus;
use Illuminate\Http\Request;

class DriverStatusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return DriverStatus::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'driver_id' => 'required|exists:drivers,id',
            'status_id' => 'required|exists:driver_status_types,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'reason' => 'nullable|string',
        ]);

        $driverStatus = DriverStatus::create($request->all());
        return response()->json($driverStatus, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(DriverStatus $driverStatus)
    {
        return $driverStatus;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DriverStatus $driverStatus)
    {
        $request->validate([
            'driver_id' => 'sometimes|required|exists:drivers,id',
            'status_id' => 'sometimes|required|exists:driver_status_types,id',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'nullable|date|after:start_date',
            'reason' => 'nullable|string',
        ]);

        $driverStatus->update($request->all());
        return response()->json($driverStatus, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DriverStatus $driverStatus)
    {
        $driverStatus->delete();
        return response()->json(null, 204);
    }
}
