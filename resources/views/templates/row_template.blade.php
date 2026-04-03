{{-- row example --}}

<?php
function secondsToHms($seconds) {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $seconds = $seconds % 60;

    return sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
}
?>

@foreach ($data as $snapshot)
    <div class="row">
        {{-- id zone battery uptime --}}
        <div class="col-6 border-bottom border-end border-3">
            <div class="row my-2">
                <div class="col-2">
                    {{ $snapshot->device_id }}
                </div>

                {{-- <div class="col-2">
                    @if ($snapshot->is_active)
                    {{ $snapshot->gps_lat }}, {{ $snapshot->gps_long }}
                    @else
                    _
                    @endif
                </div> --}}

                <div class="col-3">
                    @if ($snapshot->is_active)
                    {{ $snapshot->battery_level * 100}}%
                    @else
                    _
                    @endif
                </div>

                <div class="col-2">
                    @if ($snapshot->is_active)
                    {{ secondsToHms($snapshot->uptime) }}
                    @else
                    _
                    @endif
                </div>

                <div class="col-3">
                    @if ($snapshot->is_active)
                        {{ secondsToHms(time() - strtotime($snapshot->timestamp)) }}
                    @else
                        _
                    @endif
                </div>
            </div>
        </div>
        {{-- worktime hr fatigue incident status --}}
        @if ($snapshot->is_active)
        <div class="col-6 border-bottom border-3">
            <div class="row my-2">
                <div class="col-3">
                    {{ secondsToHms($snapshot->work_time) }}
                </div>

                <div class="col-6">
                    <div class="row">
                        <div class="col-3">
                            {{ $snapshot->hr_bpm }}
                        </div>

                        <div class="col-4">
                            {{ $snapshot->sp_o2 * 100 }}%
                        </div>

                        <div class ="col-5">
                            {{ $snapshot->fatigue * 100 }}%
                        </div>
                    </div>
                </div>

                <div class="col-3">
                    <div class="card rounded-5 text-center"> {{ $snapshot->status }} </div>
                </div>
            </div>
        </div>
        @else
        <div class="col-6 border-bottom border-3"> </div>
        @endif
    </div>
@endforeach
