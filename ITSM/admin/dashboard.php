<?php
    include '../includes/auth.php';
    include '../includes/db.php';
    include 'inventory/includes/inv_sql.php';
    require_once 'ticket/includes/sla_functions.php';
    // include '../config/config.php';

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
<?php
    $filterType = $_GET['range'] ?? 'all'; // day, week, month, year, all
    $adminFilter = $_GET['admin'] ?? 'all';

    $where = "WHERE 1";

    // Date range filter
    if (!empty($_GET['start']) && !empty($_GET['end'])) {
        $start = $_GET['start'];
        $end   = $_GET['end'];
        $where .= " AND DATE(t.date_created) BETWEEN '$start' AND '$end'";
    }

    // Range filter
    if ($filterType == 'day') {
        $where .= " AND DATE(t.date_created) = CURDATE()";
    }
    elseif ($filterType == 'week') {
        $where .= " AND YEARWEEK(t.date_created,1) = YEARWEEK(CURDATE(),1)";
    }
    elseif ($filterType == 'month') {
        $where .= " AND MONTH(t.date_created) = MONTH(CURDATE()) 
                    AND YEAR(t.date_created)=YEAR(CURDATE())";
    }
    elseif ($filterType == 'year') {
        $where .= " AND YEAR(t.date_created)=YEAR(CURDATE())";
    }

    // Admin filter
    if ($adminFilter !== 'all') {
        $where .= " AND t.assigned_to = '$adminFilter'";
    }
?>
<?php
    $totalResponseMinutes = 0;
    $totalResolutionMinutes = 0;
    $countResponse = 0;
    $countResolution = 0;

    $totalMet = 0;
    $totalNotMet = 0;

    // SUBJECT
    $subjectLabels=[];
    $subjectData=[];

    $res=$conn->query("
        SELECT subject, COUNT(*) total 
        FROM ticket_tb t 
        $where
        GROUP BY subject
        ORDER BY total DESC LIMIT 10
    ");

    while($r=$res->fetch_assoc()){
        $subjectLabels[]=$r['subject'];
        $subjectData[]=$r['total'];
    }


    // CATEGORY
    $catLabels=[];
    $catData=[];

    $res=$conn->query("
        SELECT ticket_category, COUNT(*) total 
        FROM ticket_tb t 
        $where
        GROUP BY ticket_category
    ");

    while($r=$res->fetch_assoc()){
        $catLabels[]=$r['ticket_category'];
        $catData[]=$r['total'];
    }

    // TOTAL TICKETS
    $totalTickets = $conn->query("SELECT COUNT(*) total FROM ticket_tb t $where")
                        ->fetch_assoc()['total'] ?? 0;

    // RESOLVED
    $totalResolved = $conn->query("
        SELECT COUNT(*) total FROM ticket_tb t 
        $where AND t.status IN('resolved','closed')
    ")->fetch_assoc()['total'] ?? 0;

    // ON GOING
    $totalOngoing = $conn->query("
        SELECT COUNT(*) total FROM ticket_tb t 
        $where AND t.status NOT IN('resolved','closed')
    ")->fetch_assoc()['total'] ?? 0;

    // AVG PER DAY
    $avgPerDay = $conn->query("
        SELECT COUNT(*) / COUNT(DISTINCT DATE(date_created)) avg_val
        FROM ticket_tb t $where
    ")->fetch_assoc()['avg_val'] ?? 0;

    // AVG PER WEEK
    $avgPerWeek = $conn->query("
        SELECT COUNT(*) / COUNT(DISTINCT YEARWEEK(date_created)) avg_val
        FROM ticket_tb t $where
    ")->fetch_assoc()['avg_val'] ?? 0;

    // AVG PER MONTH
    $avgPerMonth = $conn->query("
        SELECT COUNT(*) / COUNT(DISTINCT DATE_FORMAT(date_created,'%Y-%m')) avg_val
        FROM ticket_tb t $where
    ")->fetch_assoc()['avg_val'] ?? 0;

    $tickets = $conn->query("
        SELECT * FROM ticket_tb t
        $where
    ");

    while($t = $tickets->fetch_assoc()){

        $priority = strtolower($t['priority'] ?? 'medium');
        $created  = $t['date_created'];
        $ticketId = $t['ticket_id'];

        // FIRST RESPONSE
        $firstResponse = $conn->query("
            SELECT created_at FROM ticket_logs
            WHERE ticket_id=$ticketId
            AND field_name='status'
            AND old_value='waiting for support'
            AND new_value IN('in progress','pending','ongoing')
            ORDER BY created_at ASC LIMIT 1
        ")->fetch_assoc()['created_at'] ?? null;

        // RESOLVED TIME
        $resolvedTime = $conn->query("
            SELECT created_at FROM ticket_logs
            WHERE ticket_id=$ticketId
            AND field_name='status'
            AND new_value='resolved'
            ORDER BY created_at ASC LIMIT 1
        ")->fetch_assoc()['created_at'] ?? null;

        $responseMet = false;
        $resolutionMet = false;

        // RESPONSE TIME
        if($firstResponse){
            $respMinutes = calculateBusinessMinutes($conn,$ticketId,$created,$firstResponse);

            $totalResponseMinutes += $respMinutes;
            $countResponse++;

            $responseMet = $respMinutes <= ($responseMatrix[$priority] ?? 240);
        }

        // RESOLUTION TIME
        if($resolvedTime){
            $resMinutes = calculateBusinessMinutes($conn,$ticketId,$created,$resolvedTime);

            $totalResolutionMinutes += $resMinutes;
            $countResolution++;

            $resolutionMet = $resMinutes <= ($slaMatrix[$priority] ?? 4320);
        }

        // FINAL SLA
        if($responseMet && $resolutionMet){
            $totalMet++;
        } else {
            $totalNotMet++;
        }
    }

    $avgResponse = $countResponse > 0 
    ? round($totalResponseMinutes / $countResponse, 2) 
    : 0;

    $avgResolution = $countResolution > 0 
        ? round($totalResolutionMinutes / $countResolution, 2) 
        : 0;
        
    // PRIORITY
    $priorityLabels = [];
    $priorityData = [];

    $res = $conn->query("
        SELECT priority, COUNT(*) total 
        FROM ticket_tb t 
        $where
        GROUP BY priority
    ");

    while($r=$res->fetch_assoc()){
        $priorityLabels[] = $r['priority'];
        $priorityData[] = $r['total'];
    }  
?>
<style>
    .card {
    border-radius: 0.75rem;
    box-shadow:
        0 10px 20px rgba(32, 71, 190, 0.35),
        0 10px 25px rgba(23, 10, 144, 0.25);
}
</style>
  <div id="inventoryTableContainer" style="display: none;">
    <?php include 'inventory/all_assets.php'; ?>
</div>

            <!-- ================= Inventory GRAPHS ================= -->
            <?php include __DIR__ . '/inventory/includes/inv_graph.php'; ?>

            <!-- ================= USER GRAPHS ================= -->
            <div id="userGraphs" class="mt-5">
                <div class="row">

                    <!-- Active vs Disabled -->
                    <div class="col-md-6 mb-4">
                        <div class="card p-3">
                            <h6 class="text-primary">Active vs Disabled Accounts</h6>
                            <div style="height:300px">
                                <canvas id="userStatusChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Users per Department -->
                    <div class="col-md-6 mb-4">
                        <div class="card p-3">
                            <h6 class="text-primary">Users per Department</h6>
                            <div style="height:300px">
                                <canvas id="userDeptChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Users per Company -->
                    <div class="col-md-6 mb-4">
                        <div class="card p-3">
                            <h6 class="text-primary">Users per Company</h6>
                            <div style="height:300px">
                                <canvas id="userCompanyChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Users per Area -->
                    <div class="col-md-6 mb-4">
                        <div class="card p-3">
                            <h6 class="text-primary">Users per Area</h6>
                            <div style="height:300px">
                                <canvas id="userAreaChart"></canvas>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        <div class="row mt-4">

            <?php
            function card($title,$value,$color='primary'){
                echo "
                <div class='col-md-3 mb-3'>
                    <div class='card p-3 text-center'>
                        <h6 class='text-$color'>$title</h6>
                        <h3>$value</h3>
                    </div>
                </div>";
            }
            ?>

            <?php
            card('Total Tickets',$totalTickets);
            card('Avg Ticket Day',round($avgPerDay,2));
            card('Avg Ticket Week',round($avgPerWeek,2));
            card('Avg Ticket Month',round($avgPerMonth,2));
            card('Resolved Tickets',$totalResolved,'success');
            card('On Going Tickets',$totalOngoing,'success');
            card('Met SLA', $totalMet, 'success');
            card('Not Met SLA', $totalNotMet, 'danger');
            card('Avg Response (min)', $avgResponse);
            card('Avg Resolution (min)', $avgResolution);

            ?>
        </div>
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card p-3">
                    <h6>Priority Distribution</h6>
                    <canvas id="priorityChart"></canvas><br>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-3">
                    <h6>Category Distribution</h6>
                    <canvas id="categoryTicketChart"></canvas><br>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card p-3">
                    <h6>Top Subjects</h6>
                    <canvas id="subjectChart"></canvas>
                </div>
            </div>
        </div>

     </div>
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script src="asset/js/inv_chart.js"></script>
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
    <!-- tickets -->
<script>
    // PRIORITY
new Chart(document.getElementById('priorityChart'), {
    type: 'doughnut',
    data: {
        labels: <?= json_encode($priorityLabels) ?>,
        datasets: [{
            data: <?= json_encode($priorityData) ?>,
            backgroundColor: ['#dc3545','#0d6efd', '#ffc107']
        }]
    }
});

// SUBJECT
new Chart(document.getElementById('subjectChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($subjectLabels) ?>,
        datasets: [{
            data: <?= json_encode($subjectData) ?>,
            backgroundColor: '#0d6efd'
        }]
    },
    options: {
        indexAxis: 'y'
    }
});


// CATEGORY
new Chart(document.getElementById('categoryTicketChart'), {
    type: 'pie',
    data: {
        labels: <?= json_encode($catLabels) ?>,
        datasets: [{
            data: <?= json_encode($catData) ?>,
            backgroundColor: ['#6610f2','#20c997','#fd7e14','#0dcaf0']
        }]
    }
});
</script>