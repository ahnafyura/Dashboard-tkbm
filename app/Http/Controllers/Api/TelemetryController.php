<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TelemetryController extends Controller
{
    // ---------------------------------------------------------------
    // POST /api/telemetry
    // Receives sensor payload from ESP32-C3 wearable.
    // ---------------------------------------------------------------
    public function store_device_state(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'device_id'      => ['required', 'int'],
            'timestamp'      => ['required', 'int'],
            'battery_level'  => ['required', 'numeric', 'between:0,1'],
            'gps_lat'        => ['required', 'numeric', 'between:-90,90'],
            'gps_long'       => ['required', 'numeric', 'between:-180,180'],
            'hr_bpm'         => ['required', 'numeric', 'between:0,300'],
            'uptime'         => ['required', 'int'],
            'work_time'      => ['required', 'int'],
            'fatigue'        => ['required', 'numeric', 'between:0,1'],
            'sp_o2'          => ['required', 'numeric', 'between:0,1'],
            'status'         => ['required', 'string', 'in:working,break,incident']
        ]);

        $data = $validator->validated();

        DB::insert(
            'INSERT INTO tkbm.device_log 
                (device_id, 
                timestamp, battery_level, gps_lat, gps_long, hr_bpm, uptime, work_time, fatigue, status, sp_o2) 
            VALUES 
                (?, to_timestamp(?), ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $data['device_id'],
                $data['timestamp'],
                $data['battery_level'],
                $data['gps_lat'],
                $data['gps_long'],
                $data['hr_bpm'],
                $data['uptime'],
                $data['work_time'],
                $data['fatigue'],
                $data['status'],
                $data['sp_o2'],
            ]
        );

        return response()->json([
            'success'          => true,
            'message'          => 'Telemetry received',
        ], 201);
    }
}