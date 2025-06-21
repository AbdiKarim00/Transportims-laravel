<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\DriverStatus;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    public function index()
    {
        $drivers = Driver::with(['status'])->latest()->paginate(10);
        return view('admin.drivers.index', compact('drivers'));
    }

    public function create()
    {
        $statuses = DriverStatus::all();
        return view('admin.drivers.create', compact('statuses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'personal_number' => 'required|unique:drivers',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|unique:drivers',
            'status_id' => 'required|exists:driver_statuses,id',
            'joining_date' => 'required|date',
            'blood_type' => 'nullable|string|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'medical_conditions' => 'nullable|string',
            'emergency_contact_name' => 'required|string|max:255',
            'emergency_contact_phone' => 'required|string|max:20',
            'emergency_contact_relationship' => 'required|string|max:255',
        ]);

        Driver::create($validated);

        return redirect()->route('admin.drivers.index')
            ->with('success', 'Driver created successfully.');
    }

    public function show(Driver $driver)
    {
        $driver->load(['status', 'trips' => function ($query) {
            $query->latest()->take(5);
        }]);
        return view('admin.drivers.show', compact('driver'));
    }

    public function edit(Driver $driver)
    {
        $statuses = DriverStatus::all();
        return view('admin.drivers.edit', compact('driver', 'statuses'));
    }

    public function update(Request $request, Driver $driver)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'personal_number' => 'required|unique:drivers,personal_number,' . $driver->id,
            'phone' => 'required|string|max:20',
            'email' => 'required|email|unique:drivers,email,' . $driver->id,
            'status_id' => 'required|exists:driver_statuses,id',
            'joining_date' => 'required|date',
            'blood_type' => 'nullable|string|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'medical_conditions' => 'nullable|string',
            'emergency_contact_name' => 'required|string|max:255',
            'emergency_contact_phone' => 'required|string|max:20',
            'emergency_contact_relationship' => 'required|string|max:255',
        ]);

        $driver->update($validated);

        return redirect()->route('admin.drivers.index')
            ->with('success', 'Driver updated successfully.');
    }

    public function destroy(Driver $driver)
    {
        $driver->delete();

        return redirect()->route('admin.drivers.index')
            ->with('success', 'Driver deleted successfully.');
    }
}
