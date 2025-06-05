<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DriverLicenseController;
use App\Http\Controllers\DriverStatusController;
use App\Http\Controllers\DriverStatusTypeController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\FuelCardHistoryController;
use App\Http\Controllers\FuelCardProviderController;
use App\Http\Controllers\FuelCardStatusController;
use App\Http\Controllers\FuelCardTransactionController;
use App\Http\Controllers\FuelCardTypeController;
use App\Http\Controllers\FuelCardController;
use App\Http\Controllers\FuelTypeController;
use App\Http\Controllers\InsurancePolicyController;
use App\Http\Controllers\InsuranceProviderController;
use App\Http\Controllers\MaintenanceRecordController;
use App\Http\Controllers\MaintenanceProviderController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\RouteController;
use App\Http\Controllers\TripController;
use App\Http\Controllers\TripStatusController;
use App\Http\Controllers\VehicleAssignmentController;
use App\Http\Controllers\VehicleDocumentController;
use App\Http\Controllers\VehicleLogController;
use App\Http\Controllers\VehicleMakeController;
use App\Http\Controllers\VehicleModelController;
use App\Http\Controllers\VehicleStatusController;
use App\Http\Controllers\VehicleTypeController;
use App\Http\Controllers\UserActivityLogController;
use App\Http\Controllers\UserLoginAttemptController;
use App\Http\Controllers\UserPreferenceController;
use App\Http\Controllers\UserRoleController;
use App\Http\Controllers\VehicleRouteController;

// Driver Management
Route::apiResource('driver-licenses', DriverLicenseController::class);
Route::apiResource('driver-statuses', DriverStatusController::class);
Route::apiResource('driver-status-types', DriverStatusTypeController::class);
Route::apiResource('drivers', DriverController::class);

// Fuel Card Management
Route::apiResource('fuel-card-histories', FuelCardHistoryController::class);
Route::apiResource('fuel-card-providers', FuelCardProviderController::class);
Route::apiResource('fuel-card-statuses', FuelCardStatusController::class);
Route::apiResource('fuel-card-transactions', FuelCardTransactionController::class);
Route::apiResource('fuel-card-types', FuelCardTypeController::class);
Route::apiResource('fuel-cards', FuelCardController::class);
Route::apiResource('fuel-types', FuelTypeController::class);

// Insurance Management
Route::apiResource('insurance-policies', InsurancePolicyController::class);
Route::apiResource('insurance-providers', InsuranceProviderController::class);

// Maintenance Management
Route::apiResource('maintenance-records', MaintenanceRecordController::class);
Route::apiResource('maintenance-providers', MaintenanceProviderController::class);

// Media Management
Route::apiResource('media', MediaController::class);

// Notification Management
Route::apiResource('notifications', NotificationController::class);

// Access Control
Route::apiResource('permissions', PermissionController::class);
Route::apiResource('roles', RoleController::class);

// Route and Trip Management
Route::apiResource('routes', RouteController::class);
Route::apiResource('trips', TripController::class);
Route::apiResource('trip-statuses', TripStatusController::class);
Route::apiResource('vehicle-routes', VehicleRouteController::class);

// Vehicle Management
Route::apiResource('vehicle-assignments', VehicleAssignmentController::class);
Route::apiResource('vehicle-documents', VehicleDocumentController::class);
Route::apiResource('vehicle-logs', VehicleLogController::class);
Route::apiResource('vehicle-makes', VehicleMakeController::class);
Route::apiResource('vehicle-models', VehicleModelController::class);
Route::apiResource('vehicle-statuses', VehicleStatusController::class);
Route::apiResource('vehicle-types', VehicleTypeController::class);

// User Management
Route::apiResource('user-activity-logs', UserActivityLogController::class);
Route::apiResource('user-login-attempts', UserLoginAttemptController::class);
Route::apiResource('user-preferences', UserPreferenceController::class);
Route::apiResource('user-roles', UserRoleController::class);