<?php
include __DIR__ . '/../../includes/auth.php';
include __DIR__ . '/../../includes/db.php';
include __DIR__ . '/includes/inv_sql.php';
?>
<!-- <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script> -->

<!-- FILTER -->
<div class="card shadow-sm mb-2">
  <div class="card-header bg-white d-flex justify-content-between align-items-center">
    <h6 class="mb-0 text-primary fw-semibold">
      <i class="fas fa-filter me-1"></i> Inventory Filter
    </h6>
    <div class="d-flex gap-2">
      <a href="?page=inventory/all_assets" class="btn btn-secondary btn-sm">
        <i class="fas fa-undo me-1"></i> Reset
      </a>
      <button type="submit" form="filterForm" class="btn btn-primary btn-sm">
        <i class="fas fa-search me-1"></i> Filter
      </button>
      <button type="button" onclick="window.print()" class="btn btn-success btn-sm">
        <i class="fas fa-print me-1"></i> Print
      </button>
    </div>
  </div>

  <div class="card-body">
    <form id="filterForm" method="GET" class="row g-3 align-items-end">
      <input type="hidden" name="page" value="inventory/all_assets">

      <!-- Item -->
      <div class="col-md-2">
        <label class="form-label">Item</label>
        <select name="item" class="form-select searchable">
          <option value="">All Items</option>
          <?php foreach($itemsArr as $item): ?>
            <option value="<?= $item ?>" <?= $filterItem == $item ? 'selected' : '' ?>>
              <?= $item ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Brand -->
      <div class="col-md-2">
        <label class="form-label">Brand</label>
        <select name="brand" class="form-select searchable">
          <option value="">All Brands</option>
          <?php foreach($brandsArr as $brand): ?>
            <option value="<?= $brand ?>" <?= $filterBrand == $brand ? 'selected' : '' ?>>
              <?= $brand ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Type -->
      <div class="col-md-2">
        <label class="form-label">Type</label>
        <select name="type" class="form-select">
          <option value="">All Types</option>
          <?php foreach($typesArr as $type): ?>
            <option value="<?= $type['type_id'] ?>" <?= $filterType == $type['type_id'] ? 'selected' : '' ?>>
              <?= $type['type_name'] ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Status -->
      <div class="col-md-2">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
          <option value="">All Status</option>
          <option value="stock" <?= $filterStatus=='stock'?'selected':'' ?>>In Stock</option>
          <option value="use" <?= $filterStatus=='use'?'selected':'' ?>>In Use</option>
          <option value="consumed" <?= $filterStatus=='consumed'?'selected':'' ?>>Consumed</option>
        </select>
      </div>

      <!-- Date From -->
      <div class="col-md-2">
        <label class="form-label">Date From</label>
        <input type="date" name="date_from" class="form-control" value="<?= $dateFrom ?>">
      </div>

      <!-- Date To -->
      <div class="col-md-2">
        <label class="form-label">Date To</label>
        <input type="date" name="date_to" class="form-control" value="<?= $dateTo ?>">
      </div>
    </form>
  </div>
</div>

<!-- Inventory -->
<div class="card shadow-sm rounded-3 mb-4">
    <div class="card-header d-flex justify-content-between align-items-center bg-white border-bottom-0">
        <h5 class="mb-0 text-primary fw-semibold">Inventory Management</h5>
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
<!--// $(document).ready(function() {

// // SELECT2
// $('.searchable').select2({
//     placeholder: "Select option",
//     allowClear: true,
//     width: '100%'
// }); -->