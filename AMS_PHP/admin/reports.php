<?php
  include 'auth/auth.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ITSM - Reports</title>
    <link rel="icon" href="asset/img/Koppel_bip.ico">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="asset/css/main.css">
    <!-- <style>
        .sidebar {
            background-color: #0d6efd;
            color: white;
            height: 100vh;
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            padding-top: 20px;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-radius: 0 20px 20px 0;
            margin: 5px 0;
            transition: background-color 0.3s;
        }
        .sidebar a:hover, .sidebar a.active {
            background-color: #0b5ed7;
        }
        .sidebar-title {
            padding: 0 20px 20px;
            font-size: 1.2rem;
            font-weight: bold;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        .status-active {
            background-color: #d4edda;
            color: #155724;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.875em;
            font-weight: 500;
        }
        .status-inactive {
            background-color: #f8d7da;
            color: #721c24;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.875em;
            font-weight: 500;
        }
        .status-low {
            background-color: #fff3cd;
            color: #856404;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.875em;
            font-weight: 500;
        }
        .btn-blue {
            background-color: #0d6efd;
            border-color: #0d6efd;
            color: white;
        }
        .btn-blue:hover {
            background-color: #0b5ed7;
            border-color: #0b5ed7;
        }
        .stats-card {
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 1rem;
        }
    </style> -->
</head>
<body>
<!-- Sidebar -->
<?php include 'sidebar.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggle = document.getElementById('inventory-toggle');
    const submenu = document.getElementById('inventory-submenu');
    const icon = document.getElementById('inventory-icon');

    // Optional: auto-expand if on desktop.php
    const currentPage = window.location.pathname;
    if (currentPage.includes('desktop.php')) {
        submenu.style.display = 'block';
        icon.classList.replace('fa-chevron-down', 'fa-chevron-up');
    }

    toggle.addEventListener('click', function (e) {
        e.preventDefault();
        const isVisible = submenu.style.display === 'block';

        if (isVisible) {
            submenu.style.display = 'none';
            icon.classList.replace('fa-chevron-up', 'fa-chevron-down');
        } else {
            submenu.style.display = 'block';
            icon.classList.replace('fa-chevron-down', 'fa-chevron-up');
        }
    });
});
</script>


    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <?php include 'header.php'; ?>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <!-- <h2 class="text-primary">Reports</h2> -->
            <div class="d-flex gap-2">
                <select id="reportType" class="form-select form-select-sm" style="width: auto;">
                    <option value="inventory">Inventory Status</option>
                    <option value="transactions">Transaction Log</option>
                    <option value="user-activity">User Activity</option>
                </select>
                <button class="btn btn-success btn-sm" id="exportBtn">
                    <i class="fas fa-file-excel me-1"></i>Export Excel
                </button>
                    <a href="inv/print_inventory.php">
                        <button class="btn btn-blue btn-sm">Inventory
                        </button>
                    </a>
                    <a href="transaction/print_transactions.php">
                        <button class="btn btn-blue btn-sm">Transaction
                        </button>
                    </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <!-- <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stats-card border-left-primary shadow">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Assets</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">128</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-boxes fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card border-left-success shadow">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">In Stock</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">94</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card border-left-warning shadow">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Low Stock</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">18</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card border-left-danger shadow">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Out of Stock</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">16</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div> -->

        <!-- Date Range Filter -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row align-items-end">
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Start Date</label>
                        <input type="date" class="form-control form-control-sm" id="startDate">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">End Date</label>
                        <input type="date" class="form-control form-control-sm" id="endDate" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="col-md-4 mb-2">
                        <button class="btn btn-blue btn-sm w-100" id="applyFilter">
                            <i class="fas fa-filter me-1"></i>Apply Filter
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Report Table -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Inventory Status Report</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="reportTable" class="table table-hover">
                        <thead>
                            <tr>
                                <th>Item Code</th>
                                <th>Item Name</th>
                                <th>Brand</th>
                                <th>Model</th>
                                <th>Current Qty</th>
                                <th>Status</th>
                                <th>Last Transaction</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>ITM-1001</td>
                                <td>Laptop</td>
                                <td>Dell</td>
                                <td>Latitude 5420</td>
                                <td>5</td>
                                <td><span class="status-active">In Stock</span></td>
                                <td>2023-10-15</td>
                            </tr>
                            <tr>
                                <td>ITM-1002</td>
                                <td>Monitor</td>
                                <td>LG</td>
                                <td>27UP850</td>
                                <td>0</td>
                                <td><span class="status-inactive">Out of Stock</span></td>
                                <td>2023-10-10</td>
                            </tr>
                            <tr>
                                <td>ITM-1003</td>
                                <td>Keyboard</td>
                                <td>Logitech</td>
                                <td>K845</td>
                                <td>12</td>
                                <td><span class="status-active">In Stock</span></td>
                                <td>2023-10-12</td>
                            </tr>
                            <tr>
                                <td>ITM-1004</td>
                                <td>Mouse</td>
                                <td>Razer</td>
                                <td>DeathAdder</td>
                                <td>8</td>
                                <td><span class="status-active">In Stock</span></td>
                                <td>2023-10-14</td>
                            </tr>
                            <tr>
                                <td>ITM-1005</td>
                                <td>Headphones</td>
                                <td>Sony</td>
                                <td>WH-1000XM4</td>
                                <td>3</td>
                                <td><span class="status-low">Low Stock</span></td>
                                <td>2023-10-16</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script>
    $(document).ready(function() {
        // Set default date range (last 30 days)
        const today = new Date();
        const last30 = new Date();
        last30.setDate(today.getDate() - 30);
        $('#startDate').val(last30.toISOString().split('T')[0]);
        $('#endDate').val(today.toISOString().split('T')[0]);

        // Export to Excel functionality
        $('#exportBtn').on('click', function() {
            const startDate = $('#startDate').val();
            const endDate = $('#endDate').val();
            const reportType = $('#reportType').val();
            
            // Get table data
            const table = document.getElementById('reportTable');
            const wb = XLSX.utils.table_to_book(table, {sheet:"Report"});
            
            // Generate filename with date range
            const filename = `report_${reportType}_${startDate}_to_${endDate}.xlsx`;
            
            // Download the file
            XLSX.writeFile(wb, filename);
        });

        // Apply filter button (in real app, this would filter data from server)
        $('#applyFilter').on('click', function() {
            const startDate = $('#startDate').val();
            const endDate = $('#endDate').val();
            alert(`Filter applied: ${startDate} to ${endDate}\nIn a real application, this would filter the data from the server.`);
            // Here you would typically make an AJAX call to reload filtered data
        });
    });
    </script>
</body>
</html>