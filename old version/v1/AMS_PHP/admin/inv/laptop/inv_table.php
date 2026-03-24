
        <!-- Inventory Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Tablets</span>
            <span><a href="../add_item.php" class="btn btn-sm btn-primary me-2">
                <i class="fas fa-plus me-1"></i> Add Item
            </a>
            <a href="print_inventory.php" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-file-csv me-1"></i> Export CSV
            </a>    </span>   
            </div>
            <div class="card-body">
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
                                    data-type="<?= htmlspecialchars($row['name']) ?>"
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