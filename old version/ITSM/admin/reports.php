<?php
  include __DIR__ . '/../includes/auth.php';
?>




    <!-- Main Content -->
    <div class="main-content" id="mainContent">
 
        <div class="d-flex justify-content-between align-items-center ">
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
            <div class="card-header" style="background-color: white;" >
                <h6 class="mb-0 text-primary">Inventory Status Report</h6>
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