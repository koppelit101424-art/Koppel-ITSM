<?php
include __DIR__ . '/../../includes/auth.php';
include __DIR__ . '/../../includes/db.php';
  $sql = "
      SELECT *
      FROM item_tb 
      Where name = 'Laptop'
      
      ORDER BY item_id DESC
  ";
  $result = $conn->query($sql);
?>
<div class="card shadow-sm rounded-3 mb-4">
    <div class="card-header d-flex justify-content-between align-items-center bg-white border-bottom-0">
        <h5 class="mb-0 text-primary fw-semibold">Laptop</h5>
        <div>
            <a href="?page=inventory/crud/add_item" class="btn btn-sm btn-primary me-2">
                <i class="fas fa-plus me-1"></i> Add Item
            </a>
            <!-- <a href="inv/print_inventory.php" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-file-csv me-1"></i> Export CSV
            </a> -->
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="inventoryTable" class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="display:none;">ID</th>
                        <th>Code</th>
                        <th>Item</th>
                        <th>Brand</th>
                        <th>Model</th>
                        <th>Serial No.</th>
                        <th style="display:none;">Description</th>
                        <th>Qty</th>
                        <th>Received</th>
                        <th>Status</th>
                        <th style="display:none;">Type</th>
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
                            data-received="<?= date('m-d-Y', strtotime($row['date_received'])) ?>">
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
                                <?php
                                if ($row['quantity'] > 0) {
                                    echo '<span class="badge bg-success">In Stock</span>';
                                } elseif ($row['type_id'] == 8) {
                                    echo '<span class="badge bg-danger">Consumed</span>';
                                } else {
                                    echo '<span class="badge bg-danger ">In Use</span>';
                                }
                                ?>
                            </td>
                            <td style="display:none;"><?= htmlspecialchars($row['type_name']) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="text-center text-muted">No items found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
    </div>
         
</div>
<?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin'): ?>
<?php include "inventory/includes/context_menu.php"; ?>
<?php endif; ?>
<?php include __DIR__ . '/includes/inv_modal.php'; ?>
<?php include __DIR__ . '/includes/inv_js.php'; ?>