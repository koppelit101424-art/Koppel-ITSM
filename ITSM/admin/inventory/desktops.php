<?php
include __DIR__ . '/../../includes/auth.php';
include __DIR__ . '/../../includes/db.php';
include __DIR__ . '/includes/desktop_sql.php';
?>
<style>
    .btn-outline-blue {
    color: #1E3A8A;
    border-color: #1E3A8A;
    }
    .btn-outline-blue:hover,
    .btn-outline-blue.active {
        background-color: #1E3A8A;
        color: white;
    }
</style>
<style>
.custom-context-menu {
    position: absolute;
    z-index: 9999;            /* sit on top */
    display: none;             /* hidden by default */
    background-color: #ffffff; /* white background */
    border: 1px solid #ccc;    /* subtle border */
    border-radius: 4px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    min-width: 150px;
    padding: 5px 0;
}

.custom-context-menu a {
    display: block;
    padding: 8px 15px;
    color: #333;
    text-decoration: none;
    font-size: 14px;
}

.custom-context-menu a:hover {
    background-color: #f1f1f1;
    color: #1E3A8A; /* match your theme */
    cursor: pointer;
}

</style>
<!-- ===== INVENTORY TABLE ===== -->
    <div class="card shadow-sm rounded-3 mb-4">
        <div class="card-header d-flex justify-content-between align-items-center bg-white border-bottom-0">
            <h5 class="mb-0 fw-semibold">Desktops</h5>
           
            <a href="?page=inventory/crud/add_desktop" class="btn btn-sm btn-primary">
            <i class="fas fa-plus"></i> Add Desktop
            </a>
            <!-- <a href="export_desktop_csv.php" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-file-csv"></i> Export CSV
            </a> -->
        </div>

        <div class="card-body ">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 ">
                <div class="d-flex flex-wrap gap-2">
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
                </div>
            </div><br>

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
            <tr class="desktop-row" 
                data-id="<?= $row['desktop_id'] ?>"
                data-cpu="<?= htmlspecialchars($row['cpu']) ?>"
                data-ram="<?= htmlspecialchars($row['ram']) ?>"
                data-rom="<?= htmlspecialchars($row['rom_w_serial']) ?>"
                data-motherboard="<?= htmlspecialchars($row['motherboard']) ?>"
                data-monitor="<?= htmlspecialchars($row['monitor_w_serial']) ?>"
                data-ip="<?= htmlspecialchars($row['ip_address']) ?>"
                data-mac="<?= htmlspecialchars($row['mac_address']) ?>"
                data-computer="<?= htmlspecialchars($row['computer_name']) ?>"
                data-windows-key="<?= htmlspecialchars($row['windows_key']) ?>"
                data-keyboard="<?= htmlspecialchars($row['keyboard']) ?>"
                data-mouse="<?= htmlspecialchars($row['mouse']) ?>"
                data-avr="<?= htmlspecialchars($row['avr']) ?>"
                data-antivirus="<?= htmlspecialchars($row['antivirus']) ?>"
                data-tag="<?= htmlspecialchars($row['tag_number']) ?>"
                data-area-id="<?= htmlspecialchars($row['desktop_area_id']) ?>"
                data-remarks="<?= htmlspecialchars($row['remarks']) ?>"
            >
            <td><?= $row['desktop_id'] ?></td>
            <td style="display:none;"><?= htmlspecialchars($row['area_name'] ?? '') ?></td>
            <td><?= $row['user_department'] ?? 'N/A' ?></td>
            <td class="truncate-col"><?= $row['user_position'] ?? 'N/A' ?></td>
            <!-- <td class="truncate-col"><?= $row['user_name'] ?? 'N/A' ?></td> -->
            <td class="truncate-col">
            <?php if (!empty($row['user_id'])): ?>
                <a href="index.php?page=organization/includes/user_assets&user_id=<?= $row['user_id'] ?>" 
                class="text-decoration-none text-primary fw-semibold">
                    <?= htmlspecialchars($row['user_name']) ?>
                </a>
            <?php else: ?>
                N/A
            <?php endif; ?>
            </td>
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
    </div></div> <?php include "inventory/includes/desktop_menu.php"; ?>
<?php include 'includes/desktop_js.php'; ?>
<?php include 'includes/desktop_modal.php'?>





