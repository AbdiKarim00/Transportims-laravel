<?php

namespace App\Http\Controllers;

use App\Models\VehicleModel;
use Illuminate\Http\Request;

class VehicleModelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return VehicleModel::with(['vehicleMake'])->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'vehicle_make_id' => 'required|exists:vehicle_makes,id',
            'name' => 'required|string|unique:vehicle_models,name,NULL,id,vehicle_make_id,' . $request->vehicle_make_id,
            'year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
        ]);

        $vehicleModel = VehicleModel::create($request->all());
        return response()->json($vehicleModel, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(VehicleModel $vehicleModel)
    {
        return $vehicleModel->load(['vehicleMake']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, VehicleModel $vehicleModel)
    {
        $request->validate([
            'vehicle_make_id' => 'sometimes|required|exists:vehicle_makes,id',
            'name' => 'sometimes|required|string|unique:vehicle_models,name,' . $vehicleModel->id . ',id,vehicle_make_id,' . $request->vehicle_make_id,
            'year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
        ]);

        $vehicleModel->update($request->all());
        return response()->json($vehicleModel, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VehicleModel $vehicleModel)
    {
        $vehicleModel->delete();
        return response()->json(null, 204);
    }
}