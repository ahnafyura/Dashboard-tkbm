<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Table: telemetry_logs
     * Stores all incoming ESP32-C3 wearable telemetry data for SENSE-TKBM.
     */
    public function up(): void
    {
        Schema::create('telemetry_logs', function (Blueprint $table) {
            $table->id();

            // Device identification
            $table->string('device_id', 32)->index();           // e.g. "TKBM-001"

            // Timestamp from the device (ISO 8601, can differ from server time)
            $table->timestamp('device_timestamp')->nullable();

            // GPS coordinates
            $table->decimal('gps_lat',  10, 7)->nullable();     // latitude
            $table->decimal('gps_long', 10, 7)->nullable();     // longitude

            // Biometric & motion
            $table->unsignedSmallInteger('hr_bpm')->nullable(); // heart-rate BPM
            $table->string('imu_state', 32)->nullable();        // "active"|"idle"|"fall_detected"

            // Safety
            $table->boolean('near_miss_btn')->default(false);   // SOS / near-miss button

            // Hardware health
            $table->unsignedTinyInteger('battery_level')->nullable(); // 0-100 %

            // Computed fields (populated by backend logic)
            $table->unsignedTinyInteger('capability_index')->nullable(); // 0-100 score
            $table->enum('worker_status', ['fit', 'warning', 'critical', 'inactive'])
                  ->default('fit');

            // Audit
            $table->ipAddress('source_ip')->nullable();
            $table->timestamps(); // created_at = server receive time
        });

        // Composite index for dashboard queries: latest N rows per device
        Schema::table('telemetry_logs', function (Blueprint $table) {
            $table->index(['device_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telemetry_logs');
    }
};