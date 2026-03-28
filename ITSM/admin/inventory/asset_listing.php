<?php
include __DIR__ . '/../../includes/auth.php';
include __DIR__ . '/../../includes/db.php';

// ============================
// GET ITEM TYPE FROM URL
// ============================
$itemName = $_GET['type'] ?? 'Laptop'; // default
$isLaptop = strtolower($itemName) === 'laptop';

// ============================
// BUILD SQL DYNAMICALLY
// ============================
$sql = "SELECT i.*, t.type_name";

// Add laptop specs if needed
if ($isLaptop) {
    $sql .= ", s.cpu, s.ram, s.rom, s.motherboard, s.os, s.`key`, s.antivirus, s.comp_name";
}

$sql .= " FROM item_tb i
          LEFT JOIN item_type t ON i.type_id = t.type_id";

if ($isLaptop) {
    $sql .= " LEFT JOIN laptop_pc_specs s ON i.item_id = s.item_id";
}

// Filter by item name
$sql .= " WHERE i.name = ? ORDER BY i.item_id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $itemName);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="card shadow-sm rounded-3 mb-4">
    <div class="card-header d-flex justify-content-between align-items-center bg-white border-bottom-0">
        <h5 class="mb-0 text-primary fw-semibold"><?= htmlspecialchars($itemName) ?></h5>
        <div>
            <a href="?page=inventory/crud/add_item" class="btn btn-sm btn-primary me-2">
                <i class="fas fa-plus me-1"></i> Add Item
            </a>
        </div>
    </div>

    <div class="card-body">
        <div class="table-responsive">
<table id="inventoryTable" class="table table-hover align-middle">
    <thead class="table-light">
        <tr>
            <th style="display:none;">ID</th>
            <th>QR</th>
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
            <th>Condition</th>
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
                data-qr="<?= htmlspecialchars($row['qr_code_path'] ?? '') ?>"
                <?php if ($isLaptop): ?>
                    data-cpu="<?= htmlspecialchars($row['cpu'] ?? '') ?>"
                    data-ram="<?= htmlspecialchars($row['ram'] ?? '') ?>"
                    data-rom="<?= htmlspecialchars($row['rom'] ?? '') ?>"
                    data-motherboard="<?= htmlspecialchars($row['motherboard'] ?? '') ?>"
                    data-os="<?= htmlspecialchars($row['os'] ?? '') ?>"
                    data-key="<?= htmlspecialchars($row['key'] ?? '') ?>"
                    data-antivirus="<?= htmlspecialchars($row['antivirus'] ?? '') ?>"
                    data-compname="<?= htmlspecialchars($row['comp_name'] ?? '') ?>"
                <?php endif; ?>
            >
                <td style="display:none;"><?= $row['item_id'] ?></td>
                
                <!-- QR Column -->
                <td>
                    <?php if (!empty($row['qr_code_path'])): ?>
                        <a href="inventory/<?= htmlspecialchars($row['qr_code_path']) ?>" target="_blank">
                            <img src="inventory/<?= htmlspecialchars($row['qr_code_path']) ?>" width="50">
                        </a>
                    <?php else: ?>
                        <span class="text-muted">No QR</span>
                    <?php endif; ?>
                </td>
                
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
                <td class="text-center">
                    <?php
                    if ($row['quantity'] > 0) {
                        echo '<span class="badge bg-success">In Stock</span>';
                    } elseif ($row['type_id'] == 8) {
                        echo '<span class="badge bg-danger">Consumed</span>';
                    } else {
                        echo '<span class="badge bg-danger">In Use</span>';
                    }
                    ?>
                </td>
                <td style="display:none;"><?= htmlspecialchars($row['type_name']) ?></td>
                <td style="text-align:center;">
                    <?php
                    $condRaw = $row['condition_name'] ?? 'N/A';
                    $cond = strtolower(trim($condRaw));
                    $badgeClass = match($cond) {
                        'good' => 'bg-success',
                        'damaged' => 'bg-warning',
                        'defective' => 'bg-danger',
                        'disposed' => 'bg-secondary',
                        default => 'bg-secondary'
                    };
                    ?>
                    <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($condRaw) ?></span>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="13" class="text-center text-muted">No items found</td>
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

