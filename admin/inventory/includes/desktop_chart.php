<script>
    // Windows OS + License Chart
const osCtx = document.getElementById('osChart').getContext('2d');
new Chart(osCtx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($osLabels) ?>,
        datasets: [
            {
                label: 'With License',
                data: <?= json_encode($osWithKey) ?>,
                backgroundColor: '#3196d9'
            },
            {
                label: 'Without License',
                data: <?= json_encode($osWithoutKey) ?>,
                backgroundColor: '#d53434'
            }
        ]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: { stepSize: 1 }
            }
        }
    }
});

// Antivirus Chart with Custom Colors
const avCtx = document.getElementById('avChart').getContext('2d');
new Chart(avCtx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($avData, 'antivirus')) ?>,
        datasets: [{
            label: 'Count',
            data: <?= json_encode(array_column($avData, 'count')) ?>,
            backgroundColor: [
                '#d53434', // color for first antivirus
                '#3c9c0c', // color for second antivirus
                '#FFCE56', // color for third antivirus
                '#4BC0C0', // color for fourth antivirus
                '#9966FF', // color for fifth antivirus
                '#FF9F40'  // add more if needed
            ]
        }]
    },
    options: {
        responsive: true,
        scales: { 
            y: { 
                beginAtZero: true, 
                ticks: { stepSize: 1 } 
            } 
        }
    }
});
</script>

<!-- ===== CHARTS BELOW TABLE ===== -->
<div class="row ">

    <!-- Windows OS License Status -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">Windows OS License Status</div>
            <div class="card-body">
                <canvas id="osChart" style="width: 100%; height: 100%;"></canvas>
            </div>
        </div>
    </div>

    <!-- Antivirus Status -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">Antivirus Status</div>
            <div class="card-body">
                <canvas id="avChart" style="width: 100%; height: 100%;"></canvas>
            </div>
        </div>
    </div>

</div>

 <!-- Left Column: Desktops per Area -->
    <!-- <div class="col-md-6 d-flex flex-column">
        <div class="card flex-grow-1">
            <div class="card-header">Desktops per Area</div>
            <div class="card-body">
                <canvas id="areaChart" style="width: 100%; height: 100%;"></canvas>
            </div>
        </div>
    </div> -->

    <!-- ===== ANALYTICS DASHBOARD (Summary Cards Only) ===== -->
    <!-- <div class="row mt-4">
    <div class="col-md-3 mb-3">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <h5>Total Desktops</h5>
                <h2 class="mb-0"><?= $totalDesktops ?></h2><br>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card text-white bg-info">
            <div class="card-body">
                <h5>Antivirus Installed</h5>
                <h2 class="mb-0"><?= $avYes ?></h2>
                <small><?= $avPct ?>%</small>
            </div>
        </div>
    </div>
</div> -->

