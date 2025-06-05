<?php

namespace App\Http\Controllers;

use App\Models\DriverLicense;
use Illuminate\Http\Request;

class DriverLicenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return DriverLicense::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'license_number' => 'required|string|unique:driver_licenses',
            'issue_date' => 'required|date',
            'expiry_date' => 'required|date|after:issue_date',
            'state_of_issue' => 'required|string|max:255',
            'license_type' => 'required|string|max:255',
        ]);

        $driverLicense = DriverLicense::create($request->all());
        return response()->json($driverLicense, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(DriverLicense $driverLicense)
    {
        return $driverLicense;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DriverLicense $driverLicense)
    {
        $request->validate([
            'user_id' => 'sometimes|required|exists:users,id',
            'license_number' => 'sometimes|required|string|unique:driver_licenses,license_number,' . $driverLicense->id,
            'issue_date' => 'sometimes|required|date',
            'expiry_date' => 'sometimes|required|date|after:issue_date',
            'state_of_issue' => 'sometimes|required|string|max:255',
            'license_type' => 'sometimes|required|string|max:255',
        ]);

        $driverLicense->update($request->all());
        return response()->json($driverLicense, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DriverLicense $driverLicense)
    {
        $driverLicense->delete();
        return response()->json(null, 204);
    }
}