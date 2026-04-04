<head>
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>

<body class="base-background-color">
    <div class="d-flex">

        @include('partials.sidebar')

        <div class="container-fluid p-2">

            {{-- toggle sidebar button --}}
            <button class="btn" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar">
                <i class="h1 base-font-color bi bi-list m-0"></i>
            </button>

            <div class="px-5 pt-2">

                {{-- title row --}}
                <div class="row">
                    <div class="col-6">
                        <h1 class="base-font-color"> Informasi Kondisi Pekerja</h1>
                    </div>
                    <div class="col-6">
                        <div class="card border-2 base-border-color base-font-color base-card-background-color  rounded-5 shadow">
                            <div class="row m-3  d-flex align-items-center justify-content-between">
                                <div class="col-auto d-flex align-items-center">
                                    <span id="total-devices"> .../... </span> &nbsp; Aktif
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

                @include('partials.summary_cards')

                {{-- telemetrys --}}
                <div class="card border-2 base-border-color base-font-color base-card-background-color shadow mt-5 p-3">
                    <div class="row">
                        <h2> Live telemetry field</h2>
                    </div>

                    {{-- row headers --}}
                    @include('templates.dashboard_spreadsheet_header')

                    <span id="data-container">

                    </span>
                </div>
            </div>
        </div>
    </div>
</body>

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
            .catch(err => console.error(err));
        
        fetch('/fetch-avg-fatigue')
        .then(res => res.text())
            .then(html => {
                document.getElementById('avg-fatigue').innerHTML = html;
            })
            .catch(err => console.error(err));
        
        fetch('/fetch-incident-count')
        .then(res => res.text())
            .then(html => {
                document.getElementById('active-incidents-counter').innerHTML = html;
            })
            .catch(err => console.error(err));
    }, 2000);
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
