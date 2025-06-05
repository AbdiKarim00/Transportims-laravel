<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
});

Route::get('/admin/dashboard', [App\Http\Controllers\AdminController::class, 'index']);
Route::get('/driver/dashboard', [App\Http\Controllers\DriverController::class, 'showDashboard']);
Route::get('/transport-officer/dashboard', [App\Http\Controllers\TransportOfficerController::class, 'index']);
Route::get('/operational-admin/dashboard', [App\Http\Controllers\OperationalAdminController::class, 'index']);

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
