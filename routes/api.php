<?php

use App\Http\Controllers\Api\TelemetryController;
use Illuminate\Support\Facades\Route;


Route::post('telemetry/store', [TelemetryController::class, 'store_device_state']);