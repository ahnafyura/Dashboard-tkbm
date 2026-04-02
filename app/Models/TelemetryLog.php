<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class TelemetryLog extends Model
{
    protected $fillable = [
        'device_id',
        'device_timestamp',
        'gps_lat',
        'gps_long',
        'hr_bpm',
        'imu_state',
        'near_miss_btn',
        'battery_level',
        'capability_index',
        'worker_status',
        'source_ip',
    ];

    protected $casts = [
        'device_timestamp' => 'datetime',
        'gps_lat'          => 'float',
        'gps_long'         => 'float',
        'hr_bpm'           => 'integer',
        'near_miss_btn'    => 'boolean',
        'battery_level'    => 'integer',
        'capability_index' => 'integer',
    ];

    // ---------------------------------------------------------------
    // Business logic helpers
    // ---------------------------------------------------------------

    /**
     * Calculate a Capability Index (0-100) from biometrics.
     *
     * Formula (tunable):
     *   - HR score   : penalty if HR > 100 bpm (critical > 140)
     *   - IMU score  : reward "active", penalise "fall_detected"
     *   - Battery    : minor weight
     */
    public static function computeCapabilityIndex(array $data): int
    {
        $score = 100;

        // Heart-rate penalty
        $hr = $data['hr_bpm'] ?? 80;
        if ($hr > 140) {
            $score -= 40;
        } elseif ($hr > 120) {
            $score -= 20;
        } elseif ($hr > 100) {
            $score -= 10;
        }

        // IMU state penalty
        $imu = $data['imu_state'] ?? 'active';
        if ($imu === 'fall_detected') {
            $score -= 30;
        } elseif ($imu === 'idle') {
            $score -= 5;
        }

        // Battery penalty (< 20% = distraction risk)
        $battery = $data['battery_level'] ?? 100;
        if ($battery < 20) {
            $score -= 10;
        } elseif ($battery < 10) {
            $score -= 20;
        }

        // Near-miss press = critical event
        if (!empty($data['near_miss_btn'])) {
            $score -= 50;
        }

        return max(0, min(100, $score));
    }

    /**
     * Derive worker status from capability index and flags.
     */
    public static function deriveStatus(int $capIndex, bool $nearMiss): string
    {
        if ($nearMiss || $capIndex < 30) {
            return 'critical';
        }
        if ($capIndex < 60) {
            return 'warning';
        }
        return 'fit';
    }

    // ---------------------------------------------------------------
    // Scopes
    // ---------------------------------------------------------------

    /** Latest single row per device_id. */
    public function scopeLatestPerDevice(Builder $query): Builder
    {
        return $query->whereIn('id', function ($sub) {
            $sub->selectRaw('MAX(id)')
                ->from('telemetry_logs')
                ->groupBy('device_id');
        });
    }

    /** Last N hours of data. */
    public function scopeRecentHours(Builder $query, int $hours = 1): Builder
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }

    /** Only critical or near-miss rows for the alert feed. */
    public function scopeAlerts(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->where('worker_status', 'critical')
              ->orWhere('near_miss_btn', true)
              ->orWhere('hr_bpm', '>', 120);
        });
    }
}