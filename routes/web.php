<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {return redirect('/dashboard');});
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/fetch-device-data', [DashboardController::class, 'get_latest_device_data_view']);
Route::get('/fetch-total-devices', [DashboardController::class, 'get_active_devices_view']);
Route::get('/fetch-avg-fatigue', [DashboardController::class, 'get_average_fatigue_view']);
