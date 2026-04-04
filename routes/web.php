<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SummaryCardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {return redirect('/dashboard');});
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::get('/fetch-device-data', [DashboardController::class, 'get_latest_device_data_view']);
Route::get('/fetch-total-devices', [DashboardController::class, 'get_active_devices_view']);

Route::get('/fetch-avg-fatigue', [SummaryCardController::class, 'get_average_fatigue_view']);
Route::get('/fetch-incident-count', [SummaryCardController::class, 'get_incident_count_view']);
Route::get('/fetch-total-and-break', [SummaryCardController::class, 'get_total_and_break_view']);

