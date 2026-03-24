<?php
include __DIR__ . '/../../includes/auth.php';
include __DIR__ . '/../../includes/db.php';
include __DIR__ . '/includes/desktop_sql.php';
?>

<!-- ===== INVENTORY TABLE ===== -->
<div class="card shadow-sm rounded-3 mb-4">
    <div class="card-header d-flex justify-content-between align-items-center bg-white border-bottom-0">
        <h5 class="mb-0 text-primary fw-semibold">Desktops</h5>
        <div>
<a href="add_desktop.php" class="btn btn-sm btn-primary">
<i class="fas fa-plus"></i> Add Desktop
</a>
<!-- <a href="export_desktop_csv.php" class="btn btn-sm btn-outline-secondary">
<i class="fas fa-file-csv"></i> Export CSV
</a> -->
</div>
</div>

<div class="card-body ">
<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 ">
<!-- <div class="d-flex flex-wrap gap-2">
<button class="btn btn-outline-blue area-filter active" data-area="">
All
</button>
<?php
$areaResult->data_seek(0);
while($a = $areaResult->fetch_assoc()): ?>
<button class="btn btn-outline-blue area-filter"
        data-area="<?= htmlspecialchars($a['area']) ?>">
<?= htmlspecialchars($a['area']) ?>
</button>
<?php endwhile; ?>
</div> -->
</div>

<div class="table-responsive-desktop">
<table id="desktopTable" class="table table-hover align-middle" style="min-width:1200px;">
<thead class="table-light">
<tr>
<th>ID</th>
<th style="display:none;">Area</th>
<th>Department</th>
<th>Position</th>
<th>User</th>
<th>CPU</th>
<th>RAM</th>
<th>ROM</th>
<th>IP</th>
<th>MAC</th>
</tr>
</thead>
<tbody>
<?php while($row = $result->fetch_assoc()): ?>
<tr class="desktop-row" data-id="<?= $row['desktop_id'] ?>">
<td><?= $row['desktop_id'] ?></td>
<td style="display:none;"><?= htmlspecialchars($row['area_name'] ?? '') ?></td>
<td><?= $row['user_department'] ?? 'N/A' ?></td>
<td class="truncate-col"><?= $row['user_position'] ?? 'N/A' ?></td>
<td class="truncate-col"><?= $row['user_name'] ?? 'N/A' ?></td>
<td class="truncate-col"><?= $row['cpu'] ?></td>
<td><?= $row['ram'] ?></td>
<td class="truncate-col"><?= $row['rom_w_serial'] ?></td>
<td><?= $row['ip_address'] ?></td>
<td><?= $row['mac_address'] ?></td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
</div>
</div>

<?php include "inventory/includes/desktop_menu.php"; ?>
<?php include 'includes/desktop_modal.php'?>



<?php include 'includes/desktop_js.php'; ?>




