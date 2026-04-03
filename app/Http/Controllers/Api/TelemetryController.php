<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TelemetryController extends Controller
{
    // ---------------------------------------------------------------
    // POST /api/telemetry
    // Receives sensor payload from ESP32-C3 wearable.
    // ---------------------------------------------------------------
    public function store_device_state(Request $request): JsonResponse
    {
        // 1. Validate incoming JSON
        $validator = Validator::make($request->all(), [
            'device_id'      => ['required', 'string', 'max:32', 'regex:/^[A-Za-z0-9\-_]+$/'],
            'timestamp'      => ['required', 'int'],
            'battery_level'  => ['required', 'numeric', 'between:0,1'],
            'gps_lat'        => ['required', 'numeric', 'between:-90,90'],
            'gps_long'       => ['required', 'numeric', 'between:-180,180'],
            'hr_bpm'         => ['required', 'numeric', 'between:0,300'],
            'uptime'         => ['required', 'int'],
            'work_time'      => ['required', 'int'],
            'fatigue'        => ['required', 'numeric', 'between:0,1'],
            'incident_type'  => ['required', 'string', 'in:none,near_miss'],
            'status'         => ['required', 'string', 'in:working,break,critical']
        ]);


        return response()->json([
            'success'          => true,
            'message'          => 'Telemetry received',
        ], 201);
    }
}