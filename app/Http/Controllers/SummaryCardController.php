<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SummaryCardController extends Controller
{
    public function get_average_fatigue_view() : View
    {
        $data = DB::select('
            SELECT AVG(dl.fatigue)
            FROM tkbm.device_log dl
            JOIN (
                SELECT device_id, MAX(timestamp) AS latest_ts
                FROM tkbm.device_log
                GROUP BY device_id
            ) latest
            ON dl.device_id = latest.device_id AND dl.timestamp = latest.latest_ts 
            '
        );

        return view('templates.summary_cards.avg_fatigue_template', ['data' => $data]);
    }

    public function get_incident_count_view() : View
    {
        $data = DB::select("
            select count(case when i.incident_status != 'resolved' then 1 end) as incident_count from tkbm.incidents i
            group by i.incident_status
        ");

        return view('templates.summary_cards.incident_count_template', ['data' => $data]);
    }

    public function get_total_and_break_view() : View
    {
        $data = DB::select("
            SELECT count(*) as total, count(case when dl.status = 'working' then 1 end) as working
            FROM tkbm.device_log dl
            JOIN (
                SELECT device_id, MAX(timestamp) AS latest_ts
                FROM tkbm.device_log
                GROUP BY device_id
            ) latest
            ON dl.device_id = latest.device_id AND dl.timestamp = latest.latest_ts 
        ");

        return view('templates.summary_cards.total_and_break_template', ['data' => $data]);
    }
}