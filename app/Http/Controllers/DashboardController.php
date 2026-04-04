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

    public function get_latest_device_data() {
        $data = DB::select("
            select dl.*, d.*, incident.incident_count
            from tkbm.device_log dl
            join (
                select device_id, MAX(timestamp) AS latest_ts
                from tkbm.device_log
                group by device_id
            ) latest
            on dl.device_id = latest.device_id AND dl.timestamp = latest.latest_ts
            join tkbm.devices d on d.device_id = dl.device_id 
            left join (
                select de.device_id,
                    count(case when i.incident_status != 'resolved' then 1 end) as incident_count
                from tkbm.devices de
                left join tkbm.incidents i 
                    on de.device_id = i.device_id
                group by de.device_id
            ) incident
            on incident.device_id = dl.device_id
            order by incident.incident_count desc, dl.device_id 
            "
        );

        return $data;
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

    public function get_latest_device_data_view() : View
    {
        return view('templates.row_template', ['data' => $this->get_latest_device_data()]);
    }
}