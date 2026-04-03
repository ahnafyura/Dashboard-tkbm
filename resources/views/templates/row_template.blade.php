{{-- row example --}}

@foreach ($data as $snapshot)
    <div class="row">
        {{-- id zone battery uptime --}}
        <div class="col-4 border-bottom border-end border-3">
            <div class="row my-2">
                <div class="col-3">
                    {{ $snapshot->device_id }}
                </div>

                <div class="col-3">
                    {{ $snapshot->gps_lat }}, {{ $snapshot->gps_long }}
                </div>

                <div class="col-3">
                    {{ $snapshot->battery_level }}
                </div>

                <div class="col-3">
                    {{ $snapshot->uptime }}
                </div>
            </div>
        </div>
        {{-- worktime hr fatigue incident status --}}
        <div class="col-8 border-bottom border-3">
            <div class="row my-2">
                <div class="col-2">
                    {{ $snapshot->work_time }}
                </div>

                <div class="col-5">
                    <div class="row">
                        <div class="col-3">
                            {{ $snapshot->hr_bpm }}
                        </div>

                        <div class="col-4">
                            {{ $snapshot->sp_o2 }}
                        </div>

                        <div class ="col-5">
                            {{ $snapshot->fatigue }}
                        </div>
                    </div>
                </div>

                <div class="col-5">
                    <div class="row">
                        <div class="col-6">
                            <div class="card rounded-5 text-center"> NULL </div>
                        </div>
                        <div class="col-6">
                            <div class="card rounded-5 text-center"> {{ $snapshot->status }} </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endforeach