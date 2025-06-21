<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceRecord;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    public function index(Request $request)
    {
        $query = MaintenanceRecord::with('vehicle');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        $maintenanceRecords = $query->latest()->paginate(10);
        $vehicles = Vehicle::all();

        return view('admin.maintenance.index', compact('maintenanceRecords', 'vehicles'));
    }

    public function create()
    {
        $vehicles = Vehicle::all();
        return view('admin.maintenance.create', compact('vehicles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'type' => 'required|in:routine,repair,inspection,emergency',
            'status' => 'required|in:scheduled,in_progress,completed,cancelled',
            'date' => 'required|date',
            'cost' => 'required|numeric|min:0',
            'mileage' => 'required|integer|min:0',
            'description' => 'required|string',
            'service_provider' => 'required|string|max:255',
            'next_maintenance_date' => 'nullable|date|after:date',
        ]);

        $maintenance = MaintenanceRecord::create($validated);

        // Update vehicle status if maintenance is in progress
        if ($maintenance->status === 'in_progress') {
            $maintenance->vehicle->update(['status' => 'maintenance']);
        }

        return redirect()
            ->route('admin.maintenance.show', $maintenance)
            ->with('success', 'Maintenance record created successfully.');
    }

    public function show(MaintenanceRecord $maintenance)
    {
        $maintenance->load(['vehicle', 'status']);
        return view('admin.maintenance.show', compact('maintenance'));
    }

    public function edit(MaintenanceRecord $maintenance)
    {
        $vehicles = Vehicle::all();
        return view('admin.maintenance.edit', compact('maintenance', 'vehicles'));
    }

    public function update(Request $request, MaintenanceRecord $maintenance)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'type' => 'required|in:routine,repair,inspection,emergency',
            'status' => 'required|in:scheduled,in_progress,completed,cancelled',
            'date' => 'required|date',
            'cost' => 'required|numeric|min:0',
            'mileage' => 'required|integer|min:0',
            'description' => 'required|string',
            'service_provider' => 'required|string|max:255',
            'next_maintenance_date' => 'nullable|date|after:date',
        ]);

        $maintenance->update($validated);

        // Update vehicle status if maintenance is in progress
        if ($maintenance->status === 'in_progress') {
            $maintenance->vehicle->update(['status' => 'maintenance']);
        } elseif ($maintenance->status === 'completed') {
            $maintenance->vehicle->update(['status' => 'available']);
        }

        return redirect()
            ->route('admin.maintenance.show', $maintenance)
            ->with('success', 'Maintenance record updated successfully.');
    }

    public function destroy(MaintenanceRecord $maintenance)
    {
        // If the maintenance is in progress, update the vehicle status back to available
        if ($maintenance->status === 'in_progress') {
            $maintenance->vehicle->update(['status' => 'available']);
        }

        $maintenance->delete();

        return redirect()
            ->route('admin.maintenance.index')
            ->with('success', 'Maintenance record deleted successfully.');
    }
} 