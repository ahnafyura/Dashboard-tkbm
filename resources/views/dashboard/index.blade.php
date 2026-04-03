<head>
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body>
    <div class="container-fluid p-5">
        {{-- title row --}}
        <div class="row">
            <div class="col-6">
                <h1> Informasi Kondisi Pekerja</h1>
            </div>
            <div class="col-6">
                <div class="card rounded-5 shadow">
                    <div class="row m-3  d-flex align-items-center justify-content-between">
                        <div class="col-auto d-flex align-items-center">
                            0 / 0 Aktif
                        </div>
                        <div class="col-auto d-flex align-items-center justify-content-center">
                            Update: 0s yang lalu
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
            <div class="col-6">
                <div class="card shadow rounded-3 p-3 h-100">
                    <h4> Zona Panas </h4>
                    
                    {{-- PLACEHOLDER --}}
                    <img class="img-fluid rounded-3" src="https://picsum.photos/seed/picsum/800/300">
                </div>
            </div>

            <div class="col-3">
                <div class="card shadow rounded-3 p-3 h-100">
                    <h4> Active Incidents </h4>

                </div>
            </div>

            <div class="col-3">
                <div class="card shadow rounded-3 p-3 h-100">
                    <h4> Cognitive Load Index </h4>

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
                <div class="col-6 border-bottom border-end border-3"> 
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
                <div class="col-6 border-bottom border-3"> 
                    <div class="row my-2"> 
                        <div class="col-3"> 
                            WORK TIME
                        </div>
        
                        <div class="col-2"> 
                            HR
                        </div>
        
                        <div class="col-2"> 
                            FATIGUE
                        </div>
        
                        <div class="col-5"> 
                            <div class="row"> 
                                <div class="col-6"> 
                                    INCIDENT
                                </div>
                                <div class="col-6"> 
                                    STATUS
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- row example --}}
            <div class="row">  
                {{-- id zone battery uptime --}}
                <div class="col-6 border-bottom border-end border-3"> 
                    <div class="row my-2"> 
                        <div class="col-3"> 
                            P01
                        </div>
        
                        <div class="col-3"> 
                            DOCK1
                        </div>
        
                        <div class="col-3"> 
                            50%
                        </div>
        
                        <div class="col-3"> 
                            6:30:30
                        </div>
                    </div>
                </div>
                {{-- worktime hr fatigue incident status --}}
                <div class="col-6 border-bottom border-3"> 
                    <div class="row my-2"> 
                        <div class="col-3"> 
                            6:30:30
                        </div>
        
                        <div class="col-2"> 
                            100
                        </div>
        
                        <div class="col-2"> 
                            50%
                        </div>
        
                        <div class="col-5"> 
                            <div class="row"> 
                                <div class="col-6"> 
                                    <div class="card rounded-5 text-center"> NONE </div>
                                </div>
                                <div class="col-6"> 
                                    <div class="card rounded-5 text-center"> BREAK </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
