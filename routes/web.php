<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\AccessRequestController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\VehicleAnalyticsController;
use App\Http\Controllers\Admin\FuelReportController;
use App\Http\Controllers\Admin\MaintenanceReportController;
use App\Http\Controllers\Admin\TripAnalyticsController;
use App\Http\Controllers\Admin\ExportReportController;
use App\Http\Controllers\Admin\DriverAnalyticsController;
use App\Http\Controllers\Admin\FinancialManagementController;
use App\Http\Controllers\Admin\SafetyDashboardController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
    Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [RegisterController::class, 'register']);
    Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');

    // Access Request Routes
    Route::get('/request-access', function () {
        return view('auth.request-access');
    })->name('access.request');

    Route::post('/request-access', [App\Http\Controllers\Auth\AccessRequestController::class, 'store'])->name('access.request.store');
});

// Protected Routes
Route::middleware(['web', 'auth'])->group(function () {
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');

    // Default Dashboard
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

    // Admin Routes - Only Analytics and Reports (Managerial Functions)
    Route::middleware([AdminMiddleware::class])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/vehicle-analytics', [VehicleAnalyticsController::class, 'index'])->name('vehicle-analytics');
        Route::get('/vehicle-analytics/permits', [VehicleAnalyticsController::class, 'permits'])->name('vehicle-analytics.permits');
        Route::get('/vehicle-analytics/odometer', [VehicleAnalyticsController::class, 'odometer'])->name('vehicle-analytics.odometer');
        Route::get('/vehicle-analytics/equipment', [VehicleAnalyticsController::class, 'equipment'])->name('vehicle-analytics.equipment');
        Route::get('/vehicle-analytics/service-providers', [VehicleAnalyticsController::class, 'serviceProviders'])->name('vehicle-analytics.service-providers');
        Route::get('/fuel-reports', [FuelReportController::class, 'index'])->name('fuel-reports');
        Route::get('/maintenance-reports', [MaintenanceReportController::class, 'index'])->name('maintenance-reports');
        Route::get('/trip-analytics', [TripAnalyticsController::class, 'index'])->name('trip-analytics');
        Route::get('/export-reports', [ExportReportController::class, 'index'])->name('export-reports');
        Route::post('/export-reports', [ExportReportController::class, 'export'])->name('export-reports.export');
        Route::get('/driver-analytics', [DriverAnalyticsController::class, 'index'])->name('driver-analytics');

        // Financial & Resource Management Routes
        Route::prefix('financial-management')->name('financial-management.')->group(function () {
            Route::get('/', [FinancialManagementController::class, 'index'])->name('index');
            Route::get('/fuel-reports', [FinancialManagementController::class, 'fuelReports'])->name('fuel-reports');
            Route::get('/fuel-cards', [FinancialManagementController::class, 'fuelCards'])->name('fuel-cards');
            Route::get('/trip-expenses', [FinancialManagementController::class, 'tripExpenses'])->name('trip-expenses');
            Route::get('/insurance', [FinancialManagementController::class, 'insurance'])->name('insurance');
        });

        // Financial Management Actions
        Route::post('/financial-management/approve-expense/{expenseId}', [FinancialManagementController::class, 'approveExpense'])->name('financial-management.approve-expense');
        Route::post('/financial-management/create-budget', [FinancialManagementController::class, 'createBudget'])->name('financial-management.create-budget');
        Route::post('/financial-management/create-budget-allocation', [FinancialManagementController::class, 'createBudgetAllocation'])->name('financial-management.create-budget-allocation');
        Route::post('/financial-management/create-cost-center', [FinancialManagementController::class, 'createCostCenter'])->name('financial-management.create-cost-center');
        Route::post('/financial-management/create-cost-center-allocation', [FinancialManagementController::class, 'createCostCenterAllocation'])->name('financial-management.create-cost-center-allocation');

        // Safety Management Routes
        Route::prefix('safety')->name('safety.')->group(function () {
            Route::get('/dashboard', [SafetyDashboardController::class, 'index'])->name('dashboard');
            Route::get('/incidents', [SafetyDashboardController::class, 'incidents'])->name('incidents');
            Route::get('/incidents/create', [SafetyDashboardController::class, 'createIncident'])->name('incidents.create');
            Route::post('/incidents', [SafetyDashboardController::class, 'storeIncident'])->name('incidents.store');
            Route::get('/incidents/{incident}', [SafetyDashboardController::class, 'showIncident'])->name('incidents.show');
            Route::get('/incidents/{incident}/edit', [SafetyDashboardController::class, 'editIncident'])->name('incidents.edit');
            Route::put('/incidents/{incident}', [SafetyDashboardController::class, 'updateIncident'])->name('incidents.update');
            Route::get('/compliance', [SafetyDashboardController::class, 'compliance'])->name('compliance');
            Route::get('/risk-assessment', [SafetyDashboardController::class, 'riskAssessment'])->name('risk-assessment');
        });

        // Removed resource routes (CRUD operations) to focus only on analytics and reporting
        Route::get('/safety-dashboard', [App\Http\Controllers\Admin\SafetyDashboardController::class, 'index'])->name('safety-dashboard');
    });

    // Password Change Routes
    Route::get('/password/change', [App\Http\Controllers\Auth\PasswordController::class, 'showChangeForm'])->name('password.change');
    Route::post('/password/change', [App\Http\Controllers\Auth\PasswordController::class, 'change'])->name('password.change.submit');
});

// Remove old dashboard routes
// Route::get('/admin/dashboard', [App\Http\Controllers\AdminController::class, 'index']);
// Route::get('/driver/dashboard', [App\Http\Controllers\DriverController::class, 'showDashboard']);
// Route::get('/transport-officer/dashboard', [App\Http\Controllers\TransportOfficerController::class, 'index']);
// Route::get('/operational-admin/dashboard', [App\Http\Controllers\OperationalAdminController::class, 'index']);
// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
