
    <!-- Inventory Table -->
     <style>
        .status-consumed{
            background-color: #ffebee;
            color: #c62828;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
     </style>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Inventory Management</span>
            
            <span><a href="inv/add_item.php" class="btn btn-sm btn-primary me-2">
                <i class="fas fa-plus me-1"></i> Add Item
            </a>
            <a href="inv/print_inventory.php" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-file-csv me-1"></i> Export CSV
            </a>    </span>              
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
                            <th>Status</th>
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

                                    <?php elseif ($row['type_id'] == 8): ?>

                                            <span class="status-consumed">Consumed</span>
                                     

                                    <?php else: ?>

                                            <span class="status-inactive">In Use</span>
                                    

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
