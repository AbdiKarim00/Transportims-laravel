<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use App\Models\Vehicle;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TripController extends Controller
{
    public function index(Request $request)
    {
        $query = Trip::with('driver');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('driver_id')) {
            $query->where('driver_id', $request->driver_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $trips = $query->latest()->paginate(10);
        $drivers = Driver::where('status_id', 1)->get(); // Only active drivers

        return view('admin.trips.index', compact('trips', 'drivers'));
    }

    public function create()
    {
        $drivers = Driver::where('status_id', 1)->get(); // Only active drivers
        $vehicles = Vehicle::where('status', 'available')->get(); // Only available vehicles
        return view('admin.trips.create', compact('drivers', 'vehicles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'driver_id' => 'required|exists:drivers,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'status' => 'required|in:scheduled,in_progress,completed,cancelled',
            'start_time' => 'required|date',
            'end_time' => 'nullable|date|after:start_time',
            'purpose' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $trip = Trip::create($validated);

        return redirect()
            ->route('admin.trips.show', $trip)
            ->with('success', 'Trip created successfully.');
    }

    public function show(Trip $trip)
    {
        $trip->load(['driver', 'vehicle']);
        return view('admin.trips.show', compact('trip'));
    }

    public function edit(Trip $trip)
    {
        $drivers = Driver::where('status_id', 1)->get(); // Only active drivers
        $vehicles = Vehicle::where('status', 'available')->orWhere('id', $trip->vehicle_id)->get(); // Available vehicles + current vehicle
        return view('admin.trips.edit', compact('trip', 'drivers', 'vehicles'));
    }

    public function update(Request $request, Trip $trip)
    {
        $validated = $request->validate([
            'driver_id' => 'required|exists:drivers,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'status' => 'required|in:scheduled,in_progress,completed,cancelled',
            'start_time' => 'required|date',
            'end_time' => 'nullable|date|after:start_time',
            'purpose' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $trip->update($validated);

        return redirect()
            ->route('admin.trips.show', $trip)
            ->with('success', 'Trip updated successfully.');
    }

    public function destroy(Trip $trip)
    {
        $trip->delete();

        return redirect()->route('admin.trips.index')
            ->with('success', 'Trip deleted successfully.');
    }
}
