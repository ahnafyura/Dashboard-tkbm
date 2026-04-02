<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TelemetryLog;
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
    public function store(Request $request): JsonResponse
    {
        // 1. Validate incoming JSON
        $validator = Validator::make($request->all(), [
            'device_id'      => ['required', 'string', 'max:32', 'regex:/^[A-Za-z0-9\-_]+$/'],
            'timestamp'      => ['required', 'date'],
            'gps_lat'        => ['required', 'numeric', 'between:-90,90'],
            'gps_long'       => ['required', 'numeric', 'between:-180,180'],
            'hr_bpm'         => ['required', 'integer', 'between:0,300'],
            'imu_state'      => ['required', 'string', 'in:active,idle,fall_detected'],
            'near_miss_btn'  => ['required', 'boolean'],
            'battery_level'  => ['required', 'integer', 'between:0,100'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        // 2. Compute derived fields
        $capabilityIndex = TelemetryLog::computeCapabilityIndex($validated);
        $workerStatus    = TelemetryLog::deriveStatus(
            $capabilityIndex,
            (bool) $validated['near_miss_btn']
        );

        // 3. Persist
        $log = TelemetryLog::create([
            'device_id'        => $validated['device_id'],
            'device_timestamp' => $validated['timestamp'],
            'gps_lat'          => $validated['gps_lat'],
            'gps_long'         => $validated['gps_long'],
            'hr_bpm'           => $validated['hr_bpm'],
            'imu_state'        => $validated['imu_state'],
            'near_miss_btn'    => $validated['near_miss_btn'],
            'battery_level'    => $validated['battery_level'],
            'capability_index' => $capabilityIndex,
            'worker_status'    => $workerStatus,
            'source_ip'        => $request->ip(),
        ]);

        // 4. Log critical events to stderr (Vercel-friendly)
        if ($workerStatus === 'critical' || $validated['near_miss_btn']) {
            Log::channel('stderr')->critical('SENSE-TKBM ALERT', [
                'device_id'   => $validated['device_id'],
                'status'      => $workerStatus,
                'near_miss'   => $validated['near_miss_btn'],
                'hr_bpm'      => $validated['hr_bpm'],
                'log_id'      => $log->id,
            ]);
        }

        return response()->json([
            'success'          => true,
            'message'          => 'Telemetry received',
            'log_id'           => $log->id,
            'capability_index' => $capabilityIndex,
            'worker_status'    => $workerStatus,
            'server_time'      => now()->toISOString(),
        ], 201);
    }

    // ---------------------------------------------------------------
    // GET /api/telemetry/latest
    // Returns the latest snapshot per device (for dashboard polling).
    // ---------------------------------------------------------------
    public function latest(): JsonResponse
    {
        $rows = TelemetryLog::latestPerDevice()
            ->orderBy('device_id')
            ->get([
                'device_id', 'gps_lat', 'gps_long', 'hr_bpm',
                'imu_state', 'near_miss_btn', 'battery_level',
                'capability_index', 'worker_status', 'created_at',
            ]);

        $activeCount   = $rows->whereNotIn('worker_status', ['inactive'])->count();
        $avgCapability = $rows->avg('capability_index');
        $criticalCount = $rows->where('worker_status', 'critical')->count();
        $nearMissCount = $rows->where('near_miss_btn', true)->count();

        return response()->json([
            'success' => true,
            'kpi' => [
                'active_workers'    => $activeCount,
                'avg_capability'    => round($avgCapability ?? 0),
                'critical_alerts'   => $criticalCount,
                'near_miss_reports' => $nearMissCount,
            ],
            'workers' => $rows,
        ]);
    }

    // ---------------------------------------------------------------
    // GET /api/telemetry/hr-trend/{device_id}
    // Returns last 30 HR data points for Chart.js sparkline.
    // ---------------------------------------------------------------
    public function hrTrend(string $deviceId): JsonResponse
    {
        $points = TelemetryLog::where('device_id', $deviceId)
            ->recentHours(6)
            ->orderBy('created_at', 'asc')
            ->limit(60)
            ->get(['hr_bpm', 'created_at']);

        return response()->json([
            'success'   => true,
            'device_id' => $deviceId,
            'labels'    => $points->pluck('created_at')->map(fn ($t) => $t->format('H:i:s')),
            'data'      => $points->pluck('hr_bpm'),
        ]);
    }

    // ---------------------------------------------------------------
    // GET /api/telemetry/alerts
    // Returns recent alert events for the live feed sidebar.
    // ---------------------------------------------------------------
    public function alerts(): JsonResponse
    {
        $alerts = TelemetryLog::alerts()
            ->recentHours(8)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get(['device_id', 'hr_bpm', 'near_miss_btn', 'worker_status', 'imu_state', 'created_at']);

        $feed = $alerts->map(function ($row) {
            if ($row->near_miss_btn) {
                $icon = '🚨';
                $msg  = "{$row->device_id}: Near-Miss Button Pressed!";
                $level = 'critical';
            } elseif ($row->worker_status === 'critical' || $row->hr_bpm > 140) {
                $icon = '🚨';
                $msg  = "{$row->device_id}: HR Critical — {$row->hr_bpm} BPM";
                $level = 'critical';
            } elseif ($row->imu_state === 'fall_detected') {
                $icon = '⚠️';
                $msg  = "{$row->device_id}: Fall Detected!";
                $level = 'warning';
            } else {
                $icon = '⚠️';
                $msg  = "{$row->device_id}: Heart Rate High — {$row->hr_bpm} BPM";
                $level = 'warning';
            }

            return [
                'icon'  => $icon,
                'msg'   => $msg,
                'level' => $level,
                'time'  => $row->created_at->format('H:i:s'),
                'ago'   => $row->created_at->diffForHumans(),
            ];
        });

        return response()->json(['success' => true, 'feed' => $feed]);
    }
}