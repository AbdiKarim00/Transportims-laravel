<?php

namespace App\Http\Controllers\Api;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VehicleController extends Controller
{
    public function index()
    {
        return Vehicle::with(['fuelTransactions', 'maintenanceRecords', 'trips'])->paginate(10);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vin' => 'required|unique:vehicles|max:17',
            'license_plate' => 'required|unique:vehicles',
            // ... other validation rules
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        return Vehicle::create($request->validated());
    }

    public function show(string $id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        $vehicle = Vehicle::findOrFail($id);
        $vehicle->update($request->validated());
        return $vehicle;
    }

    public function destroy($id)
    {
        Vehicle::destroy($id);
        return response()->noContent();
    }
}
