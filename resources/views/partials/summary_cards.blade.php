{{-- summary cards row --}}
<div class="row mt-5 align-items-stretch d-flex">

    <div class="col-4">
        <div
            class="card border-2 base-border-color base-font-color base-card-background-color  min-card-size shadow rounded-3 p-3 h-100">
            <h4 class="mb-3">Insiden Aktif</h4>
            <div class="d-flex justify-content-center align-items-center text-center h-100">
                <div>
                    <h1 id="active-incidents-counter" class="mb-3"> ... </h1>
                </div>
            </div>
        </div>
    </div>

    <div class="col-4">
        <div
            class="card border-2 base-border-color base-font-color base-card-background-color  min-card-size shadow rounded-3 p-3 h-100">
            <h4> Rata-rata Kelelahan </h4>

            <div class="d-flex justify-content-center align-items-center h-100">
                <h1 id="avg-fatigue"> ... </h1>
            </div>
        </div>
    </div>

    <div class="col-4">
        <div
            class="card border-2 base-border-color base-font-color base-card-background-color  min-card-size shadow rounded-3 p-3 h-100">
            <h4> Sedang Bekerja </h4>

            <div class="d-flex justify-content-center align-items-center h-100">
                <h1 id="total-break"> ... </h1>
            </div>
        </div>
    </div>
</div>

<script>
    setInterval(() => {
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
    
    fetch('/fetch-total-and-break')
    .then(res => res.text())
    .then(html => {
        document.getElementById('total-break').innerHTML = html;
    })
    .catch(err => console.error(err));
    }, 5000)
</script>
