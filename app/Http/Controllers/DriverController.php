<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Driver::with(['user', 'driverLicense', 'driverStatus'])->get();
    }

    public function showDashboard()
    {
        return view('driver.dashboard');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id|unique:drivers',
            'license_id' => 'required|exists:driver_licenses,id|unique:drivers',
            'hire_date' => 'required|date',
            'is_active' => 'boolean',
        ]);

        $driver = Driver::create($request->all());
        return response()->json($driver, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Driver $driver)
    {
        return $driver->load(['user', 'driverLicense', 'driverStatus']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Driver $driver)
    {
        $request->validate([
            'user_id' => 'sometimes|required|exists:users,id|unique:drivers,user_id,' . $driver->id,
            'license_id' => 'sometimes|required|exists:driver_licenses,id|unique:drivers,license_id,' . $driver->id,
            'hire_date' => 'sometimes|required|date',
            'is_active' => 'sometimes|boolean',
        ]);

        $driver->update($request->all());
        return response()->json($driver, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Driver $driver)
    {
        $driver->delete();
        return response()->json(null, 204);
    }
}