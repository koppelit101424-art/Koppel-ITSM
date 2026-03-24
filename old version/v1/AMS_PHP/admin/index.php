<?php
    include 'auth/auth.php';
    include 'db/db.php';
    include 'inv/inv_sql.php';
    include '../config/config.php';

    // USERS
    // ================= USER GRAPH DATA =================

    // Active vs Disabled
    $activeCount = 0;
    $disabledCount = 0;

    $statusQuery = $conn->query("
        SELECT 
            SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active,
            SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as disabled
        FROM user_tb
    ");
    $statusRow = $statusQuery->fetch_assoc();
    $activeCount = $statusRow['active'] ?? 0;
    $disabledCount = $statusRow['disabled'] ?? 0;


    // Users per Department
    $deptLabels = [];
    $deptCounts = [];
    $deptQuery = $conn->query("
        SELECT department, COUNT(*) as total 
        FROM user_tb 
        GROUP BY department
    ");
    while($row = $deptQuery->fetch_assoc()){
        $deptLabels[] = $row['department'];
        $deptCounts[] = $row['total'];
    }


    // Users per Company
    $companyLabels = [];
    $companyCounts = [];
    $companyQuery = $conn->query("
        SELECT company, COUNT(*) as total 
        FROM user_tb 
        GROUP BY company
    ");
    while($row = $companyQuery->fetch_assoc()){
        $companyLabels[] = $row['company'];
        $companyCounts[] = $row['total'];
    }


    // Users per Area
    $areaLabels = [];
    $areaCounts = [];
    $areaQuery = $conn->query("
        SELECT area, COUNT(*) as total 
        FROM user_tb 
        GROUP BY area
    ");
    while($row = $areaQuery->fetch_assoc()){
        $areaLabels[] = $row['area'];
        $areaCounts[] = $row['total'];
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ITSM - Inventory</title>
    <link rel="icon" type="image/x-icon" href="asset/img/Koppel_bip.ico">
     <link rel="icon" type="image/png" sizes="32x32" href="asset/img/Koppel.jpg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="asset/css/main.css">
    <link rel="stylesheet" href="asset/css/menu.css">
    <link rel="stylesheet" href="asset/css/inv_toggle.css">
</head>
<body>
    <?php include 'sidebar.php'; ?> 
    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Header -->
        <?php include 'header_dashboard.php'; ?>
            <div id="inventoryTableContainer">
                <?php include 'inv/inv_table.php'; ?>
            </div>

            <!-- ================= Inventory GRAPHS ================= -->
            <?php include 'inv/inv_graph.php'; ?>

            <!-- ================= USER GRAPHS ================= -->
            <div id="userGraphs" class="mt-5">
                <div class="row">

                    <!-- Active vs Disabled -->
                    <div class="col-md-6 mb-4">
                        <div class="card p-3">
                            <h6 class="text-center">Active vs Disabled Accounts</h6>
                            <div style="height:300px">
                                <canvas id="userStatusChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Users per Department -->
                    <div class="col-md-6 mb-4">
                        <div class="card p-3">
                            <h6 class="text-center">Users per Department</h6>
                            <div style="height:300px">
                                <canvas id="userDeptChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Users per Company -->
                    <div class="col-md-6 mb-4">
                        <div class="card p-3">
                            <h6 class="text-center">Users per Company</h6>
                            <div style="height:300px">
                                <canvas id="userCompanyChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Users per Area -->
                    <div class="col-md-6 mb-4">
                        <div class="card p-3">
                            <h6 class="text-center">Users per Area</h6>
                            <div style="height:300px">
                                <canvas id="userAreaChart"></canvas>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="asset/js/inv.js"></script>
    <script src="asset/js/inv_chart.js"></script>
    <?php include 'inv/inv_modal.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="asset/js/inv_toggle_table.js"></script>

<!-- Charts --> 
 <!-- INVENTORY -->
<script>
       
        document.addEventListener('DOMContentLoaded', function() {
        // Category Chart Data
        const catLabels = <?php 
            $labels = [];
            $data = [];
            $availableData = [];
            $issuedData = [];
            $catResult->data_seek(0); // Reset result pointer
            while($row = $catResult->fetch_assoc()) {
                $labels[] = $row['type_name'];
                $data[] = $row['total'];
                $availableData[] = $row['available'];
                $issuedData[] = $row['issued'];
            }
            echo json_encode($labels);
        ?>;
        const catData = <?php echo json_encode($data); ?>;
        const availableCounts = <?php echo json_encode($availableData); ?>;
        const issuedCounts = <?php echo json_encode($issuedData); ?>;

        // Status Chart Data
        const statusData = [<?php echo $available; ?>, <?php echo $outOfStock; ?>];

        // Monthly Trend Data
        const monthLabels = <?php 
            $months = [];
            $counts = [];
            while($row = $monthResult->fetch_assoc()) {
                $months[] = $row['month'];
                $counts[] = $row['count'];
            }
            echo json_encode($months);
        ?>;
        const monthData = <?php echo json_encode($counts); ?>;

        // Category Chart
        const catCtx = document.getElementById('categoryChart').getContext('2d');
        new Chart(catCtx, {
            type: 'bar',
            data: {
                labels: catLabels,
                datasets: [{
                    label: 'Total Assets',
                    data: catData,
                    backgroundColor: catLabels.map((_, i) => 
                        `hsl(${i * 60}, 70%, 60%)`
                    ),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 } }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const index = context.dataIndex;
                                const total = context.parsed.y;
                                const available = availableCounts[index];
                                const issued = issuedCounts[index];
                                
                                return [
                                    `Total: ${total}`,
                                    `Available: ${available}`,
                                    `Issued: ${issued}`
                                ];
                            }
                        }
                    }
                }
            }
        });

        // Status Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Available', 'Issued'],
                datasets: [{
                    data: statusData,
                    backgroundColor: ['#28a745', '#dc3545']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });

        // Trend Chart
        const trendCtx = document.getElementById('trendChart').getContext('2d');
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: monthLabels,
                datasets: [{
                    label: 'New Assets Added',
                    data: monthData,
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                indexAxis: 'x',
                responsive: true,
                maintainAspectRatio: false,
                barThickness: 28, // ←←← This makes bars noticeably bigger
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: { precision: 0 }
                    }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
        
    });
    
    // === NEW CHART: Available Stock Per Item (colored by type_id) ===
    const itemLabels = <?php echo json_encode($itemLabels); ?>;
    const itemStocks = <?php echo json_encode($itemStocks); ?>;
    const itemType = <?php echo json_encode($itemType); ?>;

    if (itemLabels.length > 0) {
        // Define color mapping by type_id (customize as needed)
        const typeColorMap = {
            1: 'rgba(214, 58, 219, 0.8)',   //  — PC Parts
            2: 'rgba(31, 184, 39, 0.8)',    // — Peripherals
            8: 'rgba(53, 43, 192, 0.8)',   //  — Software
            6: 'rgba(226, 56, 47, 0.8)',   //  — Devices
            7: 'rgba(207, 219, 40, 0.8)',   // — Storage
            4: 'rgba(203, 119, 24, 0.8)',   //  — Cosumables
            // Add more type_id → color mappings as needed
        };

        // Default color if type_id not in map
        const defaultColor = 'rgba(120, 120, 120, 0.8)'; // Grey

        const backgroundColors = [];
        const borderColors = [];

        for (let i = 0; i < itemType.length; i++) {
            const typeId = itemType[i];
            const color = typeColorMap[typeId] || defaultColor;
            backgroundColors.push(color);
            // Solid border (opacity 1)
            const borderColor = color.replace(/0\.8/, '1').replace(/rgba\((\d+,\s*\d+,\s*\d+),\s*0\.\d+\)/, 'rgb($1)');
            borderColors.push(borderColor);
        }

        const stockCtx = document.getElementById('stockPerItemChart').getContext('2d');
        new Chart(stockCtx, {
            type: 'bar',
            data: {
                labels: itemLabels,
                datasets: [{
                    label: 'Available Stock',
                    data: itemStocks,
                    backgroundColor: backgroundColors,
                    borderColor: borderColors,
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: { precision: 0 }
                    }
                },
                plugins: {
                    legend: { display: false }
                },
                barThickness: 18
            }
        });
    }
</script>
<!-- users -->
 <script>
    // ================= USER CHARTS =================

// Active vs Disabled (Pie)
new Chart(document.getElementById('userStatusChart'), {
    type: 'doughnut',
    data: {
        labels: ['Active', 'Disabled'],
        datasets: [{
            data: [<?= $activeCount ?>, <?= $disabledCount ?>],
            backgroundColor: ['#28a745', '#dc3545']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});


// Users per Department (Horizontal Bar)
new Chart(document.getElementById('userDeptChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($deptLabels) ?>,
        datasets: [{
            label: 'Users',
            data: <?= json_encode($deptCounts) ?>,
            backgroundColor: '#0d6efd'
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            x: { beginAtZero: true }
        }
    }
});


// Users per Company (Pie)
new Chart(document.getElementById('userCompanyChart'), {
    type: 'pie',
    data: {
        labels: <?= json_encode($companyLabels) ?>,
        datasets: [{
            data: <?= json_encode($companyCounts) ?>,
            backgroundColor: [
                '#0d6efd','#20c997','#ffc107','#6610f2',
                '#fd7e14','#dc3545','#198754'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});


// Users per Area (Vertical Bar)
new Chart(document.getElementById('userAreaChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($areaLabels) ?>,
        datasets: [{
            label: 'Users',
            data: <?= json_encode($areaCounts) ?>,
            backgroundColor: '#6f42c1'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: { beginAtZero: true }
        }
    }
});
 </script>

<script>
    // Show only user graphs when Users card clicked
document.getElementById('usersCard')?.addEventListener('click', function() {

    // Hide inventory graphs
    document.getElementById('inventoryTableContainer').style.display = 'none';

    // Show user graphs
    document.getElementById('userGraphs').style.display = 'block';

});
</script>



</body>
</html>