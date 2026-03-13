
<?php
  include 'auth/auth.php';
  include 'db/db.php';
  include 'inv/inv_sql.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asset Management</title>
    <link rel="icon" type="image/x-icon" href="asset/img/Koppel.ico">
     <link rel="icon" type="image/png" sizes="32x32" href="asset/img/Koppel.jpg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="asset/css/main.css">
    <link rel="stylesheet" href="asset/css/menu.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
</head>

<body>
    <!-- <?php include 'sidebar.php'; ?> -->
<!-- Main Content -->
<div class="main-content" id="mainContent">
    <!-- Header -->
    <?php include 'header.php'; ?>
    <?php include 'stats_card.php'; ?>
    
    <!-- Charts Section -->
    <div class="row mb-4">
        <!-- Category Distribution -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5>Assets by Category</h5>
                </div>
                <div class="card-body">
                    <canvas id="categoryChart" height="250"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Status Overview -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Asset Status</h5>
                </div>
                <div class="card-body">
                    <canvas id="statusChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>
    <!-- Monthly Trend -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Monthly Asset Additions (Last 4 Months)</h5>
                </div>
                <div class="card-body">
                    <canvas id="trendChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Inventory Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Inventory Management</span>
            
            <a href="inv/add_item.php">
                <button class="btn btn-blue btn-sm">
                    <i class="fas fa-plus me-1"></i> Add Item
                </button>
            </a>
        </div>
        <div class="card-body">
            <!-- Filter Section -->
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                <!-- Type Filter Buttons -->
                <div class="d-flex flex-wrap gap-2">
                    <button class="btn btn-outline-blue type-filter active" data-type="">All</button>
                    <?php while($t = $typeResult->fetch_assoc()): ?>
                        <button class="btn btn-outline-blue type-filter" data-type="<?= htmlspecialchars($t['type_name']) ?>">
                            <?= htmlspecialchars($t['type_name']) ?>
                        </button>
                    <?php endwhile; ?>
                </div>

                <!-- Filter by Item Name -->
                <div style="width: 188px;">
                    <select id="filterByName" class="form-select form-select-sm">
                        <option value="">Select Item</option>
                        <?php while($n = $itemNames->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($n['name']) ?>"><?= htmlspecialchars($n['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>                                
            <div class="table-responsive">
                <table id="inventoryTable" class="table table-hover">
                    <thead>
                        <tr>
                            <th style="display:none;">ID</th>
                            <th>Code</th>
                            <th>Item</th>
                            <th>Brand</th>
                            <th>Model</th>
                            <th>Serial No.</th>
                            <th style="width: 350px; display: none">Description</th>
                            <th>Qty</th>
                            <th>Received</th>
                            <th>Actions</th>
                            <th style="display:none;">Type</th> <!-- Hidden column -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                            <tr 
                                data-item-id="<?= (int)$row['item_id'] ?>"
                                data-code="<?= htmlspecialchars($row['item_code']) ?>"
                                data-name="<?= htmlspecialchars($row['name']) ?>"
                                data-brand="<?= htmlspecialchars($row['brand']) ?>"
                                data-model="<?= htmlspecialchars($row['model']) ?>"
                                data-serial="<?= htmlspecialchars($row['serial_number']) ?>"
                                data-desc="<?= htmlspecialchars($row['description']) ?>"
                                data-qty="<?= (int)$row['quantity'] ?>"
                                data-received="<?= date('m-d-Y', strtotime($row['date_received'])) ?>"
                                data-type="<?= htmlspecialchars($row['type_name']) ?>"
                            >
                                <td style="display:none;"><?= $row['item_id'] ?></td>
                                <td><?= $row['item_code'] ?></td>
                                <td><?= $row['name'] ?></td>
                                <td><?= $row['brand'] ?></td>
                                <td><?= $row['model'] ?></td>
                                <td><?= $row['serial_number'] ?></td>
                                <td style="display:none;"><?= htmlspecialchars($row['description']) ?></td>
                                <td><?= $row['quantity'] ?></td>
                                <td data-order="<?= date('Y-m-d', strtotime($row['date_received'])) ?>">
                                    <?= date('m-d-Y', strtotime($row['date_received'])) ?>
                                </td>
                                <td>
                                    <?php if ($row['quantity'] > 0): ?>
                                        <span class="status-active">In Stock</span>
                                    <?php else: ?>
                                        <span class="status-inactive">Out of Stock</span>
                                    <?php endif; ?>
                                </td>
                                <td style="display:none;"><?= htmlspecialchars($row['type_name']) ?></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="9" class="text-center">No items found</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div id="contextMenu" class="custom-menu">
    <a href="#" id="editLink">Edit</a>
    <a href="" id="borrowLink">Borrow</a>
    <a href="#" id="issueLink">Issue</a>
</div>

<style>
    .custom-menu {
        display: none;
        position: absolute;
        background: white;
        border: 1px solid #ddd;
        box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        z-index: 10000;
        min-width: 140px;
        border-radius: 4px;
        padding: 5px 0;
    }
    .custom-menu a {
        display: block;
        padding: 8px 16px;
        color: #333;
        text-decoration: none;
        font-size: 0.9rem;
    }
    .custom-menu a:hover {
        background-color: #f8f9fa;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="asset/js/inv.js"></script>
<script src="asset/js/inv_chart.js"></script>
<?php include 'inv/inv_modal.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fetch data for charts
    // 1. Asset count by category (available vs issued)
    const catLabels = [];
    const availableData = [];
    const issuedData = [];
    
    <?php
    // Get all categories with their counts
    $catQuery = "
        SELECT t.type_name, 
               COUNT(*) as total_count,
               SUM(CASE WHEN i.quantity > 0 THEN 1 ELSE 0 END) as available_count,
               SUM(CASE WHEN i.quantity = 0 THEN 1 ELSE 0 END) as issued_count
        FROM item_tb i
        LEFT JOIN item_type t ON i.type_id = t.type_id
        GROUP BY t.type_name
        ORDER BY total_count DESC
    ";
    $catResult = $conn->query($catQuery);
    
    while($row = $catResult->fetch_assoc()) {
        echo "catLabels.push('" . addslashes($row['type_name']) . "');\n";
        echo "availableData.push(" . $row['available_count'] . ");\n";
        echo "issuedData.push(" . $row['issued_count'] . ");\n";
    }
    ?>
    
    // 2. Asset status counts
    const statusData = [<?php echo $available ?? 0; ?>, <?php echo $outOfStock ?? 0; ?>];
    
    // 3. Monthly asset additions (last 4 months)
    const monthLabels = [];
    const monthData = [];
    
    <?php
    $monthQuery = "
        SELECT 
            DATE_FORMAT(date_received, '%b %Y') as month,
            COUNT(*) as count
        FROM item_tb 
        WHERE date_received >= DATE_SUB(NOW(), INTERVAL 4 MONTH)
        GROUP BY DATE_FORMAT(date_received, '%Y-%m')
        ORDER BY date_received
    ";
    $monthResult = $conn->query($monthQuery);
    
    while($row = $monthResult->fetch_assoc()) {
        echo "monthLabels.push('" . addslashes($row['month']) . "');\n";
        echo "monthData.push(" . $row['count'] . ");\n";
    }
    ?>
    
    // Category Chart (Grouped Bar Chart - Available vs Issued)
    const catCtx = document.getElementById('categoryChart').getContext('2d');
    new Chart(catCtx, {
        type: 'bar',
        data: {
            labels: catLabels,
            datasets: [
                {
                    label: 'Available',
                    data: availableData,
                    backgroundColor: '#28a745',
                    borderColor: '#28a745',
                    borderWidth: 1
                },
                {
                    label: 'Issued',
                    data: issuedData,
                    backgroundColor: '#dc3545',
                    borderColor: '#dc3545',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { 
                    beginAtZero: true, 
                    ticks: { precision: 0 },
                    title: {
                        display: true,
                        text: 'Number of Assets'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Category'
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.raw;
                        }
                    }
                }
            }
        }
    });
    
    // Status Chart (Doughnut)
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
                legend: { position: 'bottom' },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
    
    // Trend Chart (Line)
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
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { 
                    beginAtZero: true, 
                    ticks: { precision: 0 },
                    title: {
                        display: true,
                        text: 'Number of Assets'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Month'
                    }
                }
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `Assets Added: ${context.raw}`;
                        }
                    }
                }
            }
        }
    });
});
</script>
</body>
</html>
