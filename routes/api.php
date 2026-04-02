<?php

use App\Http\Controllers\Api\TelemetryController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| SENSE-TKBM API Routes  —  /api/*
|--------------------------------------------------------------------------
| All routes here are stateless (no CSRF, session middleware is stripped).
| Rate-limiting is applied via the "api" middleware group (60/min default).
|
| ESP32 Authentication Strategy:
|   Option A (simple)  : Shared API key in X-API-Key header → validated in
|                         middleware/HandleApiKey.php (add when ready).
|   Option B (scalable): Laravel Sanctum token tied to each device.
|
| For MVP we leave the store endpoint open and rely on:
|   - Device-ID whitelist check (inside controller)
|   - VPN / firewall rules at infra level
*/

// ── Telemetry ingestion (ESP32 → server) ─────────────────────────────────
Route::post('telemetry', [TelemetryController::class, 'store'])
     ->name('api.telemetry.store');

// ── Dashboard polling endpoints (browser ← server) ────────────────────────
Route::get('telemetry/latest', [TelemetryController::class, 'latest'])
     ->name('api.telemetry.latest');

Route::get('telemetry/hr-trend/{device_id}', [TelemetryController::class, 'hrTrend'])
     ->name('api.telemetry.hr-trend')
     ->where('device_id', '[A-Za-z0-9\-_]+');

Route::get('telemetry/alerts', [TelemetryController::class, 'alerts'])
     ->name('api.telemetry.alerts');

// ── Health check (Vercel / load balancer probe) ───────────────────────────
Route::get('health', fn () => response()->json([
    'status'  => 'ok',
    'service' => 'SENSE-TKBM API',
    'time'    => now()->toISOString(),
]))->name('api.health');