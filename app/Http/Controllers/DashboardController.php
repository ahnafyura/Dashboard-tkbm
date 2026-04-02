<?php

namespace App\Http\Controllers;

use App\Models\TelemetryLog;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        // Initial SSR KPI data (hydrated into Alpine.js on page load)
        $latestRows = TelemetryLog::latestPerDevice()->get();

        $kpi = [
            'active_workers'    => $latestRows->whereNotIn('worker_status', ['inactive'])->count(),
            'avg_capability'    => round($latestRows->avg('capability_index') ?? 0),
            'critical_alerts'   => $latestRows->where('worker_status', 'critical')->count(),
            'near_miss_reports' => $latestRows->where('near_miss_btn', true)->count(),
        ];

        $workers = $latestRows->map(fn ($r) => [
            'device_id'        => $r->device_id,
            'gps_lat'          => $r->gps_lat,
            'gps_long'         => $r->gps_long,
            'hr_bpm'           => $r->hr_bpm,
            'imu_state'        => $r->imu_state,
            'battery_level'    => $r->battery_level,
            'capability_index' => $r->capability_index,
            'worker_status'    => $r->worker_status,
            'updated_at'       => $r->created_at?->format('H:i:s'),
        ]);

        return view('dashboard.index', compact('kpi', 'workers'));
    }
}