        <!-- Inventory Table -->

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Transaction</span>
                <a href="inventory/add_item.php">
                    <!-- <button class="btn btn-blue btn-sm">
                        <i class="fas fa-plus me-1"></i> Add Item
                    </button> -->
                </a>
                <a href="transaction/print_transactions.php" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-file-csv me-1"></i> Export CSV
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
                    <table id="transactionTable" class="table table-hover">
                         <thead >
                            <tr>
                            <th>ID</th>
                            <th>User</th>                         
                            <th>Item</th>
                            <th>Code</th>   
                            <th>Brand</th>
                            <th>Model</th>
                            <th>Serial</th>
                            <th style="display:none;">Remarks</th>
                            <th >Qty</th>
                            <th>Date</th>
                            <th>Returned</th>
                            <th>Status</th>
                            <th style="display:none;">Type</th> <!-- hidden type column -->
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                            <tr data-transaction="<?= $row['transaction_id'] ?>"  
                                data-user="<?= htmlspecialchars($row['fullname']) ?>" 
                                data-item="<?= htmlspecialchars($row['item_name']) ?>" 
                                 data-item_code="<?= $row['item_code'] ?>"
                                data-brand="<?= htmlspecialchars($row['brand']) ?>" 
                                data-model="<?= htmlspecialchars($row['model']) ?>" 
                                data-qty="<?= $row['serial_number'] ?>" 
                                data-qty="<?= $row['quantity'] ?>" 
                                data-date="<?= date('m-d-Y', strtotime($row['action_date'])) ?>" 
                                data-returned-label="<?= (empty($row['date_returned']) || $row['date_returned']=='0000-00-00 00:00:00') ? 'Not Returned' : date('m-d-Y', strtotime($row['date_returned'])) ?>" 
                                data-returned="<?= (empty($row['date_returned']) || $row['date_returned']=='0000-00-00 00:00:00') ? '0' : '1' ?>" 
                                data-remarks="<?= htmlspecialchars($row['remarks']) ?>" 
                                data-status="<?= $row['action'] ?>">
                                
                                <td><?= $row['transaction_id'] ?></td>
                                <td><?= $row['fullname'] ?></td>
                                <td><?= $row['item_name'] ?></td>
                                <td><?= $row['item_code'] ?></td>
                                <td><?= $row['brand'] ?></td>
                                <td ><?= $row['model'] ?></td>
                                 <td ><?= $row['serial_number'] ?></td>
                                <td style="display:none;"><?= $row['remarks'] ?></td>
                                <td ><?= $row['quantity'] ?></td>
                                <td style="width: 85px;"><?= date('m-d-Y', strtotime($row['action_date'])) ?></td>
                                <td style="width: 100px;">
                                <?php 
                                    if (empty($row['date_returned']) || $row['date_returned'] == '0000-00-00 00:00:00') 
                                        echo '<span class="text-danger">Not Returned</span>';
                                    else 
                                        echo date('m-d-Y', strtotime($row['date_returned']));
                                ?>
                                </td>
                                <td>
                                <?php if ($row['action'] == 'issued'): ?>
                                    <span class="status-issued">Issued</span>
                                <?php elseif ($row['action'] == 'borrowed'): ?>
                                    <span class="status-borrowed">Borrowed</span>
                                <?php elseif ($row['action'] == 'returned'): ?>
                                    <span class="status-returned">Returned</span>
                                <?php endif; ?>
                                </td>
                                <td style="display:none;"><?= htmlspecialchars($row['type_name']) ?></td> <!-- hidden type -->
                            </tr>
                            <?php endwhile; ?>
                        </tbody>

                    </table>
                </div>
            </div>
        </div>
    </div>
    <div id="contextMenu" class="custom-menu">
        <a href="#" id="editLink">
            <i class="fas fa-pen-to-square text-primary"></i> Edit
        </a>

        <a href="#" id="returnLink">
            <i class="fas fa-rotate-left text-primary"></i> Return
        </a>

        <a href="#" id="printLink" target="_blank">
            <i class="fas fa-print text-primary"></i> Print Issuance
        </a>

        <a href="#" id="printReturnedLink" target="_blank" style="display:none;">
            <i class="fas fa-print text-primary"></i> Print Returned
        </a>
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
