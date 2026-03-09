<?php
include '../auth/auth.php';
include '../db/db.php';
include '../../config/config.php';

$created_by = $_SESSION['user_id'];

// Fetch all requests by the logged-in user
$sql = "
    SELECT 
        request_id,
        lmr_no,
        department,
        item,
        description,
        quantity,
        UoM,
        date_needed,
        remarks,
        date_created,
        status,
        created_by
    FROM request_tb
    WHERE created_by = ?
    ORDER BY date_created DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $created_by);
$stmt->execute();
$requests = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Requests</title>

<link rel="icon" href="../asset/img/Koppel_bip.ico">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="../asset/css/main.css" rel="stylesheet">
<link href="../asset/css/menu.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<style>
.table-hover tbody tr:hover { background-color: #f1f1f1; }
/* .badge-open { background-color: #0d6efd; color:#fff; } */
.badge-approved { background-color: #198754; color:#fff; }
.badge-rejected { background-color: #dc3545; color:#fff; }
.badge-pending { background-color: #ffc107; color:#000; }
/* .badge-cancelled { background-color: #6c757d; color:#fff; } */

.status-filter.active { background-color: #0d6efd; color: #fff; }

.custom-menu {
    display: none;
    position: absolute;
    background: white;
    border: 1px solid #ddd;
    box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    z-index: 10000;
    min-width: 180px;
    border-radius: 4px;
    padding: 5px 0;
}
.custom-menu a {
    display: block;
    padding: 8px 16px;
    color: #333;
    text-decoration: none;
}
.custom-menu a:hover { background-color: #f0f8ff; }
</style>
</head>
<body>

<div class="main-content d-flex" id="mainContent">
    <?php include '../sidebar.php'; ?>

    <div class="content flex-grow-1">
        <?php include '../header.php'; ?>

        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between">
                <span>My Requests</span>
                <!-- <a href="add_request.php" class="btn btn-blue btn-sm">
                    <i class="fas fa-plus"></i> Add Request
                </a> -->
            </div>

            <div class="card-body">

                <!-- Status Filter Buttons -->
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <button class="btn btn-outline-primary status-filter active" data-status="">All</button>
                    <!-- <button class="btn btn-outline-primary status-filter" data-status="open">Open</button> -->
                    <button class="btn btn-outline-primary status-filter" data-status="pending">Pending</button>
                    <button class="btn btn-outline-primary status-filter" data-status="approved">Approved</button>
                    <button class="btn btn-outline-primary status-filter" data-status="rejected">Rejected</button>
                    <!-- <button class="btn btn-outline-primary status-filter" data-status="cancelled">Cancelled</button> -->
                </div>

                <div class="table-responsive">
                    <table id="requestsTable" class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>LMR No.</th>
                                <th>Department</th>
                                <th>Item</th>
                                <th>Qty</th>
                                <th>UoM</th>
                                <th>Date Needed</th>
                                <th>Status</th>
                                <th>Date Created</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($requests->num_rows > 0): ?>
                                <?php $i = 1; while ($row = $requests->fetch_assoc()): ?>
                                    <tr 
                                        data-request-id="<?= $row['request_id'] ?>" 
                                        data-status="<?= strtolower($row['status']) ?>"
                                        oncontextmenu="showContextMenu(event, <?= $row['request_id'] ?>)">
                                        <td><?= $i++ ?></td>
                                        <td><?= htmlspecialchars($row['lmr_no']) ?></td>
                                        <td><?= htmlspecialchars($row['department']) ?></td>
                                        <td><?= htmlspecialchars(string: $row['item']) ?></td>
                                        <td><?= $row['quantity'] ?></td>
                                        <td><?= htmlspecialchars($row['UoM']) ?></td>
                                        <td><?= htmlspecialchars(string: $row['date_needed']) ?></td>
                                        <td>
                                            <?php 
                                                $statusClass = '';
                                                switch (strtolower($row['status'])) {
                                                    // case 'open': $statusClass = 'badge-open'; break;
                                                    case 'approved': $statusClass = 'badge-approved'; break;
                                                    case 'rejected': $statusClass = 'badge-rejected'; break;
                                                    case 'pending': $statusClass = 'badge-pending'; break;
                                                    // case 'cancelled': $statusClass = 'badge-cancelled'; break;
                                                    default: $statusClass = 'badge-pending'; break;
                                                }
                                            ?>
                                            <span class="badge <?= $statusClass ?>">
                                                <?= ucfirst($row['status']) ?>
                                            </span>
                                        </td>
                                        <td><?= date('m-d-Y', strtotime($row['date_created'])) ?></td>
                                        <td><?= htmlspecialchars($row['remarks'] ?? '-') ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                            
                            
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

    </div>
</div>

<!-- Context Menu -->
<!-- <div id="contextMenu" class="custom-menu">
    <a href="#" id="deleteRequest" class="text-danger"><i class="fas fa-trash"></i> Delete Request</a>
</div> -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function () {
    const table = $('#requestsTable').DataTable({
        pageLength: 10,
        order: [[0, "desc"]],
        columnDefs: [{ orderable: false, targets: [5, 9] }]
    });

    // Status filter
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        const selectedStatus = $('.status-filter.active').data('status');
        const rowStatus = $(table.row(dataIndex).node()).data('status');
        if (!selectedStatus) return true;
        return rowStatus === selectedStatus;
    });

    $('.status-filter').on('click', function () {
        $('.status-filter').removeClass('active');
        $(this).addClass('active');
        table.draw();
    });
});

// Context menu
let currentRequestId = null;

function showContextMenu(e, requestId) {
    e.preventDefault();
    currentRequestId = requestId;
    const menu = document.getElementById('contextMenu');
    menu.style.top = e.pageY + 'px';
    menu.style.left = e.pageX + 'px';
    menu.style.display = 'block';
}

document.addEventListener('click', () => {
    document.getElementById('contextMenu').style.display = 'none';
});

document.getElementById('deleteRequest').addEventListener('click', (e) => {
    e.preventDefault();
    if (!currentRequestId) return;

    if (confirm("Are you sure you want to delete this request? This action cannot be undone.")) {
        window.location.href = "delete_request.php?id=" + currentRequestId;
    }
});
</script>

</body>
</html>
<?php $conn->close(); ?>
