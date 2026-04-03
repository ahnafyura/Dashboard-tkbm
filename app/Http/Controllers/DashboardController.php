<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {

        return view('dashboard.index');
    }

    public function get_latest_device_data_view() : View
    {
        $data = DB::select(
            'SELECT dl.*
             FROM tkbm.device_log dl
             JOIN (
                 SELECT device_id, MAX(timestamp) AS latest_ts
                 FROM tkbm.device_log
                 GROUP BY device_id
             ) latest
             ON dl.device_id = latest.device_id AND dl.timestamp = latest.latest_ts'
        );

        return view('templates.row_template', ['data' => $data]);
    }

    public function get_active_devices_view() : View
    {
        $data = DB::select('
            SELECT 
                COUNT(*) AS total_devices,
                COUNT(*) FILTER (WHERE d.is_active) AS active_devices
            FROM tkbm.devices d;
        ');

        return view('templates.active_devices_template', ['data' => $data]);
    }
}