<head>
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        .min-card-size {
            min-height: 300px;
        }
    </style>
</head>

<div class="d-flex">

    <!-- Sidebar -->
    <div class="collapse collapse-horizontal" id="sidebar">
        <div class="m-5 pt-5">
            <div class="card shadow p-4 mt-5"> 
                <i class="text-center h2 bi bi-people-fill m-0"></i>
            </div>
            <div class="text-center mt-2"> 
                Monitor Kondisi
            </div>

            <div class="card shadow p-4 mt-5"> 
                <i class="text-center h2 bi bi-people-fill m-0"></i>
            </div>
            <div class="text-center mt-2"> 
                Monitor Kondisi
            </div>

            <div class="card shadow p-4 mt-5"> 
                <i class="text-center h2 bi bi-people-fill m-0"></i>
            </div>
            <div class="text-center mt-2"> 
                Monitor Kondisi
            </div>
        </div>
    </div>

    <div class="container-fluid p-2">

        {{-- toggle sidebar button --}}
        <button class="btn" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar">
            <i class="h1 bi bi-list m-0"></i>
        </button>

        <div class="p-5">

            {{-- title row --}}
            <div class="row">
                <div class="col-6">
                    <h1> Informasi Kondisi Pekerja</h1>
                </div>
                <div class="col-6">
                    <div class="card rounded-5 shadow">
                        <div class="row m-3  d-flex align-items-center justify-content-between">
                            <div class="col-auto d-flex align-items-center">
                                <span id="total-devices"> </span> &nbsp; Aktif
                            </div>
                            <div class="col-auto d-flex align-items-center justify-content-center">
                                Update: &nbsp; <span id="seconds">0</span>s yang lalu
                            </div>
                            <div class="col-auto d-flex align-items-center justify-content-end">
                                <i class="fs-1 bi bi-reception-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- summary cards row --}}
            <div class="row mt-5 align-items-stretch d-flex">
                {{-- <div class="col-6">
                <div class="card shadow rounded-3 p-3 h-100">
                    <h4> Zona Panas </h4>

                    <img class="img-fluid rounded-3" src="https://picsum.photos/seed/picsum/800/300">
                </div>
            </div> --}}

                <div class="col-3">
                    <div class="card min-card-size shadow rounded-3 p-3 h-100">
                        <h4 class="mb-3">Active Incidents</h4>
                        <div class="d-flex justify-content-center align-items-center text-center h-100">
                            <div>
                                <h1 class="mb-3">0</h1>
                                {{-- <div class="card rounded-5 p-2">0 Critical</div> --}}
                            </div>
                        </div>

                        {{-- <div class="row d-flex justify-content-between"> 
                        <div class="col-auto"> Last updated: </div>
                        <div class="col"> PLACEHOLDER-TIME </div>
                    </div> --}}
                    </div>
                </div>

                <div class="col-3">
                    <div class="card min-card-size shadow rounded-3 p-3 h-100">
                        <h4> Physical Load Index </h4>

                        <div class="d-flex justify-content-center align-items-center h-100">
                            <h1 class=> 0% </h1>
                        </div>
                    </div>
                </div>
            </div>

            {{-- telemetrys --}}
            <div class="card shadow mt-5 h-50 p-3">
                <div class="row">
                    <h2> Live telemetry field</h2>
                </div>

                {{-- row headers --}}
                <div class="row">
                    {{-- id zone battery uptime --}}
                    <div class="col-4 border-bottom border-end border-3">
                        <div class="row my-2">
                            <div class="col-3">
                                ID
                            </div>

                            <div class="col-3">
                                ZONE
                            </div>

                            <div class="col-3">
                                BATTERY
                            </div>

                            <div class="col-3">
                                UPTIME
                            </div>
                        </div>
                    </div>
                    {{-- worktime hr fatigue incident status --}}
                    <div class="col-8 border-bottom border-3">
                        <div class="row my-2">
                            <div class="col-2">
                                WORK TIME
                            </div>

                            <div class="col-5">
                                <div class="row">
                                    <div class="col-3">
                                        HR
                                    </div>

                                    <div class="col-4">
                                        SpO2
                                    </div>

                                    <div class ="col-5">
                                        FATIGUE
                                    </div>
                                </div>
                            </div>

                            <div class="col-2">
                                STATUS
                            </div>
                        </div>
                    </div>
                </div>

                <span id="data-container">

                </span>
            </div>
        </div>
    </div>
</div>

<script>
    let seconds = 0;

    // for "n seconds yang lalu"
    const secondsSpan = document.getElementById('seconds');

    // increment every second
    setInterval(() => {
        seconds++;
        secondsSpan.textContent = seconds;
    }, 1000);

    function resetCounter() {
        seconds = 0;
        secondsSpan.textContent = seconds;
    }

    // for updating the rows of devices
    setInterval(() => {
        fetch('/fetch-device-data')
            .then(res => res.text())
            .then(html => {
                document.getElementById('data-container').innerHTML = html;
                resetCounter();
            })
            .catch(err => console.error(err));

        fetch('/fetch-total-devices')
            .then(res => res.text())
            .then(html => {
                document.getElementById('total-devices').innerHTML = html;
            })
            .catch(err => console.error(err))
    }, 3500);
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
