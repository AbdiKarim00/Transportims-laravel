<?php

namespace App\Http\Controllers;

use App\Models\DriverStatusType;
use Illuminate\Http\Request;

class DriverStatusTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return DriverStatusType::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:driver_status_types',
            'description' => 'nullable|string',
        ]);

        $driverStatusType = DriverStatusType::create($request->all());
        return response()->json($driverStatusType, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(DriverStatusType $driverStatusType)
    {
        return $driverStatusType;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DriverStatusType $driverStatusType)
    {
        $request->validate([
            'name' => 'sometimes|required|string|unique:driver_status_types,name,' . $driverStatusType->id,
            'description' => 'nullable|string',
        ]);

        $driverStatusType->update($request->all());
        return response()->json($driverStatusType, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DriverStatusType $driverStatusType)
    {
        $driverStatusType->delete();
        return response()->json(null, 204);
    }
}