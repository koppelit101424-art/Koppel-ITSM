<?php
include '../../auth/auth.php';
include '../../db/db.php';

/* ===============================
   FETCH DESKTOP RECORDS + AREA
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
</style>
</head>

<body>

<!-- Sidebar -->
<div class="sidebar col-md-2" style="height:100vh;width:250px;">
<br>
<center>
<img src="../../asset/img/Koppel.png" class="img-fluid" width="200">
</center>

<a href="#" id="inventory-toggle">
<i class="fas fa-boxes"></i> Inventory
<i class="fas fa-chevron-down ms-auto" id="inventory-icon"></i>
</a>

<div id="inventory-submenu" style="display:block;">
<a href="desktop.php" class="active" style="padding-left:2.5rem;">
<i class="fas fa-desktop"></i> Desktops
</a>
<a href="../laptop/laptop.php" style="padding-left:2.5rem;">
<i class="fas fa-laptop"></i> Laptops
</a>
</div>

<a href="../../users.php"><i class="fas fa-user-tie"></i> Users</a>
<a href="../../requests.php"><i class="fas fa-file-alt"></i> Requests</a>
<a href="../../transactions.php"><i class="fas fa-file-invoice-dollar"></i> Transactions</a>
<a href="../../reports.php"><i class="fas fa-chart-line"></i> Reports</a>
<a href="../../settings.php"><i class="fas fa-cog"></i> Settings</a>
</div>

<!-- Main Content -->
<div class="main-content">
<?php include '../../header.php'; ?>
<?php include 'stats_card.php'; ?>

<div class="container-fluid mt-4">
<div class="card">

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

<!-- ===============================
     AREA FILTER BUTTONS
================================ -->
<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
<div class="d-flex flex-wrap gap-2">
<button class="btn btn-outline-blue area-filter active" data-area="">
All
</button>
<?php while($a = $areaResult->fetch_assoc()): ?>
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
<!--<th>Motherboard</th>
 <th>Monitor</th>
<th>AVR</th>
<th>Mouse</th>
<th>Keyboard</th> -->
<th>IP</th>
<th>MAC</th>
<!-- <th>PC Name</th>
<th>Windows</th>
<th>Antivirus</th>
<th>Tag #</th>
<th>Remarks</th>
<th>Date</th> -->
</tr>
</thead>

<tbody>
<?php while($row = $result->fetch_assoc()): ?>
<tr>
<td><?= $row['desktop_id'] ?></td>
<td style="display:none;"><?= htmlspecialchars($row['area_name']) ?></td>
<td><?= $row['user_department'] ?? 'N/A' ?></td>
<td class="truncate-col"><?= $row['user_position'] ?? 'N/A' ?></td>
<td class="truncate-col"><?= $row['user_name'] ?? 'N/A' ?></td>
<td class="truncate-col"><?= $row['cpu'] ?></td>
<td><?= $row['ram'] ?></td>
<td class="truncate-col"><?= $row['rom_w_serial'] ?></td>
<!-- <td class="truncate-col"><?= $row['motherboard'] ?></td>
<td class="truncate-col"><?= $row['monitor_w_serial'] ?></td>
<td><?= $row['avr'] ?></td>
<td><?= $row['mouse'] ?></td>
<td><?= $row['keyboard'] ?></td> -->
<td><?= $row['ip_address'] ?></td>
<td><?= $row['mac_address'] ?></td>
<!-- <td><?= $row['computer_name'] ?></td>
<td><?= $row['windows_key'] ?></td>
<td><?= $row['antivirus'] ?></td>
<td><?= $row['tag_number'] ?></td>
<td><?= $row['remarks'] ?></td>
<td><?= $row['date_created'] ?></td> -->
</tr>
<?php endwhile; ?>
</tbody>
</table>
</div>

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

<!-- <script>
$(document).ready(function () {
const table = $('#desktopTable').DataTable({
pageLength: 15,
order: [[0,'desc']],
columnDefs: [
{ targets: 1, visible:false }
]
});

// Area filter
$('.area-filter').on('click', function(){
    $('.area-filter').removeClass('active');
    $(this).addClass('active');

    const area = $(this).data('area');
    table.column(1).search(area).draw();   // filter by area
    table.order([0, 'desc']).draw();        // sort by ID descending
});


// Context menu
let selectedDesktopId = null;
const contextMenu = $('#contextMenu');

$('#desktopTable tbody tr').on('contextmenu', function(e){
e.preventDefault();
selectedDesktopId = $(this).find('td:first').text();

contextMenu.css({ top: e.pageY + 'px', left: e.pageX + 'px' }).show();
});

$(document).on('click', function(){
contextMenu.hide();
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
</script> -->
<script>
$(document).ready(function () {
    // Initialize DataTable
    const table = $('#desktopTable').DataTable({
        pageLength: 15,
        order: [[0,'desc']],
        columnDefs: [
            { targets: 1, visible:false } // area column hidden
        ]
    });

    // Area filter buttons
    $('.area-filter').on('click', function(){
        $('.area-filter').removeClass('active');
        $(this).addClass('active');

        const area = $(this).data('area');
        table.column(1).search(area).draw();   // filter by area
        table.order([0, 'desc']).draw();       // always sort by ID desc
    });

    // Context menu handling with delegation
    let selectedDesktopId = null;
    const contextMenu = $('#contextMenu');

    $('#desktopTable tbody').on('contextmenu', 'tr', function(e){
        e.preventDefault();
        selectedDesktopId = $(this).find('td:first').text();

        contextMenu.css({
            top: e.pageY + 'px',
            left: e.pageX + 'px'
        }).show();
    });

    // Hide context menu on clicking outside
    $(document).on('click', function(e){
        if(!$(e.target).closest('#contextMenu').length){
            contextMenu.hide();
        }
    });

    // Edit option
    $('#editOption').on('click', function(e){
        e.preventDefault();
        if(selectedDesktopId) window.location.href = 'edit_desktop.php?id=' + encodeURIComponent(selectedDesktopId);
        contextMenu.hide();
    });

    // Assign user option
    $('#assignOption').on('click', function(e){
        e.preventDefault();
        if(selectedDesktopId) window.location.href = 'assign_desktop.php?id=' + encodeURIComponent(selectedDesktopId);
        contextMenu.hide();
    });

});
</script>

</body>
</html>

<?php $conn->close(); ?>
