<?php

namespace App\Http\Controllers;

use App\Models\InsurancePolicy;
use Illuminate\Http\Request;

class InsurancePolicyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return InsurancePolicy::with(['vehicle', 'insuranceProvider'])->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'policy_number' => 'required|string|unique:insurance_policies',
            'provider_id' => 'required|exists:insurance_providers,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'coverage_details' => 'nullable|string',
            'premium_amount' => 'required|numeric|min:0',
            'status' => 'required|string|max:255',
        ]);

        $insurancePolicy = InsurancePolicy::create($request->all());
        return response()->json($insurancePolicy, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(InsurancePolicy $insurancePolicy)
    {
        return $insurancePolicy->load(['vehicle', 'insuranceProvider']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, InsurancePolicy $insurancePolicy)
    {
        $request->validate([
            'policy_number' => 'sometimes|required|string|unique:insurance_policies,policy_number,' . $insurancePolicy->id,
            'provider_id' => 'sometimes|required|exists:insurance_providers,id',
            'vehicle_id' => 'sometimes|required|exists:vehicles,id',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after:start_date',
            'coverage_details' => 'nullable|string',
            'premium_amount' => 'sometimes|required|numeric|min:0',
            'status' => 'sometimes|required|string|max:255',
        ]);

        $insurancePolicy->update($request->all());
        return response()->json($insurancePolicy, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InsurancePolicy $insurancePolicy)
    {
        $insurancePolicy->delete();
        return response()->json(null, 204);
    }
}