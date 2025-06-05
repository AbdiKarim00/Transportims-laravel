<?php

namespace App\Http\Controllers;

use App\Models\VehicleDocument;
use Illuminate\Http\Request;

class VehicleDocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return VehicleDocument::with(['vehicle'])->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'document_type' => 'required|string|max:255',
            'document_path' => 'required|string|max:255',
            'issue_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after_or_equal:issue_date',
        ]);

        $vehicleDocument = VehicleDocument::create($request->all());
        return response()->json($vehicleDocument, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(VehicleDocument $vehicleDocument)
    {
        return $vehicleDocument->load(['vehicle']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, VehicleDocument $vehicleDocument)
    {
        $request->validate([
            'vehicle_id' => 'sometimes|required|exists:vehicles,id',
            'document_type' => 'sometimes|required|string|max:255',
            'document_path' => 'sometimes|required|string|max:255',
            'issue_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after_or_equal:issue_date',
        ]);

        $vehicleDocument->update($request->all());
        return response()->json($vehicleDocument, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VehicleDocument $vehicleDocument)
    {
        $vehicleDocument->delete();
        return response()->json(null, 204);
    }
}