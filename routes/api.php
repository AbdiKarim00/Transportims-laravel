<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Api\FuelController;
use App\Http\Controllers\Api\MaintenanceController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\TripController;

// Vehicles
Route::apiResource('vehicles', VehicleController::class);

// Fuel
Route::apiResource('fuel', FuelController::class);

// Maintenance
Route::apiResource('maintenance', MaintenanceController::class);

// Reports
Route::apiResource('reports', ReportController::class);

// Trips
Route::apiResource('trips', TripController::class);