<?php
include '../../auth/auth.php';
include '../../db/db.php';

/* ===============================
   FETCH DESKTOP RECORDS + AREA (for table)
================================ */
$sql = "
    SELECT 
        d.*,
        a.area AS area_name,
        u.fullname AS user_name,
        u.position AS user_position,
        u.department AS user_department
    FROM desktop_tb d
    LEFT JOIN desktop_area_tb a ON d.desktop_area_id = a.desktop_area_id
    LEFT JOIN user_desktop_tb ud ON d.desktop_id = ud.desktop_id
    LEFT JOIN user_tb u ON ud.user_id = u.user_id
    ORDER BY d.date_created DESC
";
$result = $conn->query($sql);

/* ===============================
   FETCH AREAS FOR FILTER BUTTONS
================================ */
$areaSql = "SELECT DISTINCT area FROM desktop_area_tb ORDER BY desktop_area_id ASC";
$areaResult = $conn->query($areaSql);

/* ===============================
   SUMMARY DATA FOR ANALYTICS (without RAM/CPU)
================================ */

// 1. Windows Key Status (Old pie chart — can remove if desired)
$winSql = "SELECT 
    CASE 
        WHEN windows_key = '' OR windows_key IS NULL THEN 'No Key'
        ELSE 'Has Key'
    END as win_status,
    COUNT(*) as count
FROM desktop_tb GROUP BY win_status";
$winResult = $conn->query($winSql);
$winData = [];
while ($row = $winResult->fetch_assoc()) {
    $winData[] = $row;
}

// 2. Antivirus Status
$avSql = "SELECT antivirus, COUNT(*) as count FROM desktop_tb GROUP BY antivirus";
$avResult = $conn->query($avSql);
$avData = [];
while ($row = $avResult->fetch_assoc()) {
    $avData[] = $row;
}

// 3. SAP Usage & Total Count
$sapSql = "SELECT 
    SUM(CASE WHEN LOWER(computer_name) LIKE '%sap%' THEN 1 ELSE 0 END) as sap_count,
    COUNT(*) as total
FROM desktop_tb";
$sapRow = $conn->query($sapSql)->fetch_assoc();
$sapCount = (int)$sapRow['sap_count'];
$totalDesktops = (int)$sapRow['total'];

// 4. Desktops per Area
$areaCountSql = "
    SELECT 
        COALESCE(a.area, 'Unassigned') as area_name,
        COUNT(d.desktop_id) as count
    FROM desktop_tb d
    LEFT JOIN desktop_area_tb a ON d.desktop_area_id = a.desktop_area_id
    GROUP BY a.area
    ORDER BY count DESC
";
$areaCountResult = $conn->query($areaCountSql);
$areaCountData = [];
while ($row = $areaCountResult->fetch_assoc()) {
    $areaCountData[] = $row;
}

// Calculate Antivirus YES percentage
$avYes = 0;
foreach ($avData as $item) {
    if (in_array(strtoupper(trim($item['antivirus'] ?? '')), ['YES', 'Y', '1'])) {
        $avYes += $item['count'];
    }
}
$avPct = $totalDesktops > 0 ? round(($avYes / $totalDesktops) * 100, 1) : 0;

/* ===============================
   WINDOWS OS + LICENSE STATUS
   WHEN windows_key LIKE 'WIN7%' THEN 'Windows 7'
================================ */
$osSql = "
    SELECT
        CASE
            
            WHEN windows_key LIKE 'WIN10%' THEN 'Windows 10'
            WHEN windows_key LIKE 'WIN11%' THEN 'Windows 11'
            ELSE 'Windows 7'
        END AS os_version,
        SUM(CASE WHEN windows_key LIKE '%- N/A' OR windows_key IS NULL THEN 0 ELSE 1 END) AS with_key,
        SUM(CASE WHEN windows_key LIKE '%- N/A' OR windows_key IS NULL THEN 1 ELSE 0 END) AS without_key
    FROM desktop_tb
    GROUP BY os_version
    ORDER BY os_version ASC
";
$osResult = $conn->query($osSql);
$osData = [];
while ($row = $osResult->fetch_assoc()) {
    $osData[] = $row;
}

// Prepare arrays for chart
$osLabels = array_column($osData, 'os_version');
$osWithKey = array_column($osData, 'with_key');
$osWithoutKey = array_column($osData, 'without_key');
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Desktop Inventory</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../../asset/css/main.css">
<link rel="stylesheet" href="../../css/menu.css">
    <link rel="stylesheet" href="../sidebar.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<style>
#desktopTable th,
#desktopTable td {
    font-size: 0.8125rem;
    padding: 0.5rem !important;
    white-space: nowrap;
}
.truncate-col {
    max-width: 180px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.table-responsive-desktop {
    overflow-x: auto;
}
/* Context Menu */
.custom-context-menu {
    display: none;
    position: absolute;
    background: white;
    border: 1px solid #ddd;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    border-radius: 4px;
    min-width: 180px;
    z-index: 1000;
    font-size: 0.875rem;
}
.custom-context-menu a {
    display: flex;
    align-items: center;
    padding: 8px 16px;
    color: #333;
    text-decoration: none;
}
.custom-context-menu a:hover {
    background-color: #f0f8ff;
}
.custom-context-menu i {
    margin-right: 8px;
    width: 16px;
    text-align: center;
}
.card-body canvas {
    height: 200px !important;
}
</style>
</head>

<body>

<!-- Sidebar -->
<div class="sidebar col-md-2" style="height:100vh;width:250px;">
<br>
<center>
<img src="../../asset/img/Koppel.png" class="img-fluid" width="200" style="margin-bottom:15px;">
</center>

<a href="../../index.php" class="<?= $currentDir === 'index.php' ? 'active' : '' ?>">
    <i class="fas fa-server"></i>
    <span class="sidebar-text">Dashboard</span>
</a>
<a href="#" id="inventory-toggle">
<i class="fas fa-boxes"></i>            
<span class="sidebar-text">Inventory</span>
<i class="fas fa-chevron-up ms-auto" id="inventory-icon" style="font-size: 0.75rem; margin-left: auto;"></i>
<!-- <i class="fas fa-chevron-down ms-auto" id="inventory-icon" style="font-size: 0.75rem; margin-left: auto;"></i> -->
</a>

<div id="inventory-submenu" style="display:block;" class="submenu-container">
                <a href="../../inventory.php" style="padding-left:2.5rem;">
                <i class="fas fa-list"></i>
                <span class="sidebar-text">Items</span>
                </a>
<a href="desktop.php" class="active" style="padding-left:2.5rem;">
<i class="fas fa-desktop"></i> Desktops
</a>
<a href="../laptop/laptop.php" style="padding-left:2.5rem;">
<i class="fas fa-laptop"></i> Laptops
</a>
 <a href="../printer/printer.php" style="padding-left: 2.5rem; font-size: 0.92rem; opacity: 0.9;" >
<i class="fas fa-print" style="width: 1.25rem;"></i>
<span class="sidebar-text">Printers</span>
</a>
<a href="../ip_phone/ip_phone.php" style="padding-left: 2.5rem; font-size: 0.92rem; opacity: 0.9;">
<i class="fas fa-phone" style="width: 1.25rem;"></i>
<span class="sidebar-text">IP Phones</span>
</a>
<a href="../biometrics/biometrics.php" style="padding-left: 2.5rem; font-size: 0.92rem; opacity: 0.9;">
<i class="fas fa-fingerprint" style="width: 1.25rem;"></i>
<span class="sidebar-text">Biometrics</span>
</a>
<a href="../network/network.php" style="padding-left: 2.5rem; font-size: 0.92rem; opacity: 0.9;">
<i class="fas fa-network-wired" style="width: 1.25rem;" ></i>
<span class="sidebar-text">Networks</span>
</a>

<a href="../server/server.php" style="padding-left: 2.5rem; font-size: 0.92rem; opacity: 0.9;">
<i class="fas fa-server" style="width: 1.25rem;"></i>
<span class="sidebar-text">Servers</span>
</a>

<a href="../credential/credential.php" style="padding-left: 2.5rem; font-size: 0.92rem; opacity: 0.9;" >
<i class="fas fa-key" style="width: 1.25rem;"></i>
<span class="sidebar-text">Credentials</span>
</a>
</div>

<a href="../../users.php"><i class="fas fa-user-tie"></i> Users</a>
<a href="../../tickets.php">
    <i class="fas fa-ticket"></i>
        <span class="sidebar-text flex-grow-1">Tickets</span>
        <i class="fas fa-chevron-down toggle-icon <?= $isTicketSection ? 'open' : '' ?>" 
        id="tickets-icon"></i>
    </a>
</a>
<a href="../../requests.php"><i class="fas fa-file-alt"></i> Requests</a>

<a href="../../transactions.php"><i class="fas fa-file-invoice-dollar"></i> Transactions</a>
<a href="../../reports.php"><i class="fas fa-chart-line"></i> Reports</a>
<a href="../../settings.php"><i class="fas fa-cog"></i> Settings</a>
</div>


<!-- Main Content -->
<div class="main-content">
<?php include '../header.php'; ?>
<?php include 'stats_card.php'; ?>


<!-- ===== INVENTORY TABLE ===== -->
<div class="card mt-4">
<div class="card-header d-flex justify-content-between">
<span>Desktops</span>
<div>
<a href="add_desktop.php" class="btn btn-sm btn-primary">
<i class="fas fa-plus"></i> Add Desktop
</a>
<a href="export_desktop_csv.php" class="btn btn-sm btn-outline-secondary">
<i class="fas fa-file-csv"></i> Export CSV
</a>
</div>
</div>

<div class="card-body p-2">
<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
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
</div>

<div class="table-responsive-desktop">
<table id="desktopTable" class="table table-bordered table-sm" style="min-width:1200px;">
<thead class="table-dark">
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
</div>
 <!-- Left Column: Desktops per Area -->
    <!-- <div class="col-md-6 d-flex flex-column">
        <div class="card flex-grow-1">
            <div class="card-header">Desktops per Area</div>
            <div class="card-body">
                <canvas id="areaChart" style="width: 100%; height: 100%;"></canvas>
            </div>
        </div>
    </div> -->

    <!-- ===== ANALYTICS DASHBOARD (Summary Cards Only) ===== -->
<!-- <div class="row mt-4">
    <div class="col-md-3 mb-3">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <h5>Total Desktops</h5>
                <h2 class="mb-0"><?= $totalDesktops ?></h2><br>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card text-white bg-info">
            <div class="card-body">
                <h5>Antivirus Installed</h5>
                <h2 class="mb-0"><?= $avYes ?></h2>
                <small><?= $avPct ?>%</small>
            </div>
        </div>
    </div>
</div> -->
<!-- ===== CHARTS BELOW TABLE ===== -->
<div class="row ">

    <!-- Windows OS License Status -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">Windows OS License Status</div>
            <div class="card-body">
                <canvas id="osChart" style="width: 100%; height: 100%;"></canvas>
            </div>
        </div>
    </div>

    <!-- Antivirus Status -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">Antivirus Status</div>
            <div class="card-body">
                <canvas id="avChart" style="width: 100%; height: 100%;"></canvas>
            </div>
        </div>
    </div>

</div>


<!-- Context Menu -->
<div id="contextMenu" class="custom-context-menu">
<a href="#" id="editOption">
<i class="fas fa-edit text-primary"></i> Edit
</a>
<a href="#" id="assignOption">
<i class="fas fa-user-plus text-success"></i> Assign User
</a>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function () {
    const table = $('#desktopTable').DataTable({
        pageLength: 15,
        order: [[0,'desc']],
        columnDefs: [
            { targets: 1, visible:false }
        ]
    });

    $('.area-filter').on('click', function(){
        $('.area-filter').removeClass('active');
        $(this).addClass('active');
        const area = $(this).data('area');
        table.column(1).search(area).draw();
        table.order([0, 'desc']).draw();
    });

    let selectedDesktopId = null;
    const contextMenu = $('#contextMenu');

    $('#desktopTable tbody').on('contextmenu', 'tr', function(e){
        e.preventDefault();
        selectedDesktopId = $(this).find('td:first').text();
        contextMenu.css({ top: e.pageY + 'px', left: e.pageX + 'px' }).show();
    });

    $(document).on('click', function(e){
        if(!$(e.target).closest('#contextMenu').length){
            contextMenu.hide();
        }
    });

    $('#editOption').on('click', function(e){
        e.preventDefault();
        if(selectedDesktopId) window.location.href = 'edit_desktop.php?id=' + encodeURIComponent(selectedDesktopId);
        contextMenu.hide();
    });

    $('#assignOption').on('click', function(e){
        e.preventDefault();
        if(selectedDesktopId) window.location.href = 'assign_desktop.php?id=' + encodeURIComponent(selectedDesktopId);
        contextMenu.hide();
    });
});


// const areaCtx = document.getElementById('areaChart').getContext('2d');
// new Chart(areaCtx, {
//     type: 'pie',
//     data: {
//         labels: <?= json_encode(array_column($areaCountData, 'area_name')) ?>,
//         datasets: [{
//             label: 'Desktops',
//             data: <?= json_encode(array_column($areaCountData, 'count')) ?>,
//             backgroundColor: [
//                 '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
//                 '#FF9F40', '#E74C3C', '#2ECC71', '#F39C12', '#8E44AD', '#1ABC9C'
//             ]
//         }]
//     },
//     options: {
//         responsive: true,
//         aspectRatio: 1,
//         plugins: {
//             legend: {
//                 display: false // hides the legend
//             },
//             tooltip: {
//                 callbacks: {
//                     label: function(context) {
//                         return context.label + ': ' + context.raw;
//                     }
//                 }
//             }
//         }
//     }
// });

// Windows OS + License Chart
const osCtx = document.getElementById('osChart').getContext('2d');
new Chart(osCtx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($osLabels) ?>,
        datasets: [
            {
                label: 'With License',
                data: <?= json_encode($osWithKey) ?>,
                backgroundColor: '#3196d9'
            },
            {
                label: 'Without License',
                data: <?= json_encode($osWithoutKey) ?>,
                backgroundColor: '#d53434'
            }
        ]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: { stepSize: 1 }
            }
        }
    }
});

// Antivirus Chart with Custom Colors
const avCtx = document.getElementById('avChart').getContext('2d');
new Chart(avCtx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($avData, 'antivirus')) ?>,
        datasets: [{
            label: 'Count',
            data: <?= json_encode(array_column($avData, 'count')) ?>,
            backgroundColor: [
                '#d53434', // color for first antivirus
                '#3c9c0c', // color for second antivirus
                '#FFCE56', // color for third antivirus
                '#4BC0C0', // color for fourth antivirus
                '#9966FF', // color for fifth antivirus
                '#FF9F40'  // add more if needed
            ]
        }]
    },
    options: {
        responsive: true,
        scales: { 
            y: { 
                beginAtZero: true, 
                ticks: { stepSize: 1 } 
            } 
        }
    }
});

// CLICK
    $(document).on('click', '.desktop-row', function () {

        let desktopId = $(this).data('id');

        $.ajax({
            url: 'get_desktop_details.php',
            type: 'POST',
            data: { id: desktopId },
            dataType: 'json',
            success: function (response) {

                $('#modalDesktopId').text(response.desktop_id);
                $('#modalCpu').text(response.cpu);
                $('#modalRam').text(response.ram);
                $('#modalRom').text(response.rom_w_serial);
                $('#modalMotherboard').text(response.motherboard);
                $('#modalMonitor').text(response.monitor_w_serial);
                $('#modalIp').text(response.ip_address);
                $('#modalMac').text(response.mac_address);
                $('#modalComputerName').text(response.computer_name);
                $('#modalWindowsKey').text(response.windows_key);
                $('#modalKeyboard').text(response.keyboard);
                $('#modalMouse').text(response.mouse);
                $('#modalAvr').text(response.avr);
                $('#modalAntivirus').text(response.antivirus);
                $('#modalTagNumber').text(response.tag_number);
                $('#modalAreaId').text(response.desktop_area_id);
                $('#modalRemarks').text(response.remarks);

                $('#editDesktopLink').attr('href', 'edit_desktop.php?id=' + response.desktop_id);

                new bootstrap.Modal(document.getElementById('desktopDetailsModal')).show();
            }
        });

    });
</script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
    const currentPage = '<?= $currentPage ?>';
    const currentDir = '<?= $currentDir ?>';

    function setupToggle(toggleId, submenuId, iconId) {
        const toggle = document.getElementById(toggleId);
        const submenu = document.getElementById(submenuId);
        const icon = document.getElementById(iconId);
        if (!toggle || !submenu || !icon) return;

        if (!submenu.classList.contains('collapsed')) {
            icon.classList.add('open');
        }

        toggle.addEventListener("click", function (e) {
            e.preventDefault();
            submenu.classList.toggle('collapsed');
            icon.classList.toggle('open');

            document.querySelectorAll('.submenu-container').forEach(container => {
                if (container !== submenu && !container.classList.contains('collapsed')) {
                    container.classList.add('collapsed');
                    const relatedIcon = container.previousElementSibling?.querySelector('.toggle-icon');
                    if (relatedIcon) relatedIcon.classList.remove('open');
                }
            });
        });
    }

    setupToggle("inventory-toggle", "inventory-submenu", "inventory-icon");
    setupToggle("tickets-toggle", "tickets-submenu", "tickets-icon");
});
</script>
<?php include 'desktop_modal.php'?>

</body>
</html>
<?php $conn->close(); ?>
