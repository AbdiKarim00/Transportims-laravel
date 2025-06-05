<?php

namespace App\Http\Controllers;

use App\Models\InsuranceProvider;
use Illuminate\Http\Request;

class InsuranceProviderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return InsuranceProvider::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:insurance_providers',
            'contact_person' => 'nullable|string',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string',
            'address' => 'nullable|string',
        ]);

        $insuranceProvider = InsuranceProvider::create($request->all());
        return response()->json($insuranceProvider, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(InsuranceProvider $insuranceProvider)
    {
        return $insuranceProvider;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, InsuranceProvider $insuranceProvider)
    {
        $request->validate([
            'name' => 'sometimes|required|string|unique:insurance_providers,name,' . $insuranceProvider->id,
            'contact_person' => 'nullable|string',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string',
            'address' => 'nullable|string',
        ]);

        $insuranceProvider->update($request->all());
        return response()->json($insuranceProvider, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InsuranceProvider $insuranceProvider)
    {
        $insuranceProvider->delete();
        return response()->json(null, 204);
    }
}