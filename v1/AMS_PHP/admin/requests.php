<?php
include 'auth/auth.php';
include 'db/db.php';

// Generate base URL dynamically
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$scriptPath = dirname($_SERVER['SCRIPT_NAME']);
$basePath = rtrim($scriptPath, '/\\');
$baseUrl = "$protocol://$host$basePath";

// Fetch requests
$sql = "SELECT 
            r.request_id,
            r.lmr_no,
            r.requestor,
            r.department AS request_department,
            r.item,
            r.description,
            r.quantity,
            r.UoM,
            r.date_needed,
            r.status,
            r.ticket_id,
            t.ticket_number AS t_number,
            r.date_created
        FROM request_tb r
        LEFT JOIN ticket_tb t ON t.ticket_id = r.ticket_id
        ORDER BY r.request_id DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ITSM - Requests</title>
<link rel="icon" href="asset/img/Koppel_bip.ico">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="asset/css/main.css" rel="stylesheet">
<link href="asset/css/menu.css" rel="stylesheet">
<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">

<style>
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

.btn-outline-blue {
    color: #0d6efd;
    border-color: #0d6efd;
}
.btn-outline-blue:hover,
.btn-outline-blue.active {
    background-color: #0d6efd;
    color: white;
}
</style>
</head>

<body>
<!-- Sidebar -->
<?php include 'sidebar.php'; ?>
<div class="main-content" id="mainContent">
<?php include 'header.php'; ?>


<div class="card">
<div class="card-header d-flex justify-content-between">
<span>Request Management</span>
<!-- <a href="request/add_request.php" class="btn btn-blue btn-sm">
<i class="fas fa-plus"></i> Add Request
</a> -->
</div>

<div class="card-body">

<!-- FILTER BUTTONS (UNCHANGED) -->
<div class="d-flex flex-wrap gap-2 mb-3">
<button class="btn btn-outline-blue status-filter active" data-status="">All</button>
<button class="btn btn-outline-blue status-filter" data-status="pending">Pending</button>
<button class="btn btn-outline-blue status-filter" data-status="approved">Approved</button>
<button class="btn btn-outline-blue status-filter" data-status="rejected">Rejected</button>
<button class="btn btn-outline-blue status-filter" data-status="received">Received</button>
</div>

<div class="table-responsive">
<table id="requestsTable" class="table table-hover">
<thead class="table-header-blue">
<tr>
<th>ID</th>
<th>LMR</th>
<th>Requestor</th>
<th>Ticket ID</th>
<th>Department</th>
<th>Item</th>
<th>Description</th>
<th>Qty</th>
<th>UoM</th>
<th>Created</th>
<th>Status</th>
<th>Needed</th>
</tr>
</thead>
<tbody>
<?php while($row = $result->fetch_assoc()): ?>
<tr data-request-id="<?= (int)$row['request_id'] ?>"
    data-lmr-no="<?= htmlspecialchars($row['lmr_no']) ?>"
    data-status="<?= strtolower($row['status']) ?>">

<td><?= $row['request_id'] ?></td>
<td><?= htmlspecialchars($row['lmr_no']) ?></td>
<td><?= htmlspecialchars($row['requestor']) ?></td>
<td>
    <a href="ticket/view_ticket.php?ticket_id=<?= (int)$row['ticket_id'] ?>" style="text-decoration: none;">
        <?= htmlspecialchars($row['t_number']) ?>
    </a>
</td>
<td><?= htmlspecialchars($row['request_department']) ?></td>
<td><?= htmlspecialchars($row['item']) ?></td>
<td><?= htmlspecialchars($row['description']) ?></td>
<td><?= (int)$row['quantity'] ?></td>

<td><?= htmlspecialchars($row['UoM']) ?></td>
<td><?= date('m-d-Y', strtotime($row['date_created'])) ?></td>

<td>
<span class="badge
<?php
switch(strtolower($row['status'])) {
    case 'pending': echo 'bg-warning'; break;
    case 'approved': echo 'bg-info'; break;
    case 'rejected': echo 'bg-danger'; break;
    case 'received': echo 'bg-success'; break;
    default: echo 'bg-secondary';
}
?>">
<?= htmlspecialchars($row['status']) ?>
</span>
</td>


<td><?= htmlspecialchars($row['date_needed']) ?></td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
</div>

</div>
</div>

<div id="contextMenu" class="custom-menu"></div>
</div>

<script>const BASE_URL = '<?= $baseUrl ?>';</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function () {

    const table = $('#requestsTable').DataTable({
        pageLength: 10,
        order: [[0, "desc"]],
        columnDefs: [{ orderable: false, targets: [5] }]
    });
        // === Global Search ===
    $('#globalSearch').on('keyup', function() {
        table.search(this.value).draw();
    });

    // ✅ FIXED STATUS FILTER (USES data-status)
    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        const selectedStatus = $('.status-filter.active').data('status');
        const row = table.row(dataIndex).node();
        const rowStatus = row.getAttribute('data-status');

        if (!selectedStatus) return true;
        return rowStatus === selectedStatus;
    });

    $('.status-filter').on('click', function () {
        $('.status-filter').removeClass('active');
        $(this).addClass('active');
        table.draw();
    });

});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    let selectedRequestId = null;
    let selectedLmrNo = null;
    const contextMenu = document.getElementById('contextMenu');

    // Hide menu on click anywhere
    document.addEventListener('click', () => {
        contextMenu.style.display = 'none';
    });

    document.querySelectorAll('#requestsTable tbody tr').forEach(row => {
        row.addEventListener('contextmenu', function (e) {
            e.preventDefault();

            selectedRequestId = this.dataset.requestId;
            selectedLmrNo = this.dataset.lmrNo;
            const currentStatus = this.dataset.status;

            const allStatuses = ['pending', 'approved', 'rejected', 'received'];
            const statusLabels = {
                pending: 'Mark as Pending',
                approved: 'Mark as Approved',
                rejected: 'Mark as Rejected',
                received: 'Mark as Received'
            };
            const icons = {
                pending: 'fa-clock text-warning',
                approved: 'fa-check-double text-info',
                rejected: 'fa-times text-danger',
                received: 'fa-check text-success'
            };

            const statusItems = allStatuses
                .filter(status => status !== currentStatus)
                .map(status => `
                    <a href="#" class="context-menu-item" data-status="${status}">
                        <i class="fas ${icons[status]}"></i> ${statusLabels[status]}
                    </a>
                `).join('');

            let addToInventory = '';
            if (currentStatus === 'received') {
                addToInventory = `
                    <a href="${BASE_URL}/add_item.php?request_id=${selectedRequestId}" target="_blank">
                        <i class="fas fa-box text-success"></i> Add to Inventory
                    </a>
                    <div style="border-top:1px solid #eee;margin:5px 0;"></div>
                `;
            }

            contextMenu.innerHTML = `
                <a href="#" id="editRequest">
                    <i class="fas fa-edit text-primary"></i> Edit
                </a>
                <a href="#" id="deleteRequest" class="text-danger"><i class="fas fa-trash">
                    </i> Delete
                </a>
                <a href="#" id="printRequest">
                    <i class="fas fa-print text-secondary"></i> Print
                </a>    

                ${addToInventory}
                ${statusItems}
            `;

            contextMenu.style.display = 'block';
            contextMenu.style.left = e.pageX + 'px';
            contextMenu.style.top = e.pageY + 'px';

            const rect = contextMenu.getBoundingClientRect();
            if (rect.right > window.innerWidth) {
                contextMenu.style.left = (e.pageX - rect.width) + 'px';
            }
            if (rect.bottom > window.innerHeight) {
                contextMenu.style.top = (e.pageY - rect.height) + 'px';
            }
        });
    });

    contextMenu.addEventListener('click', function (e) {
        e.preventDefault();

        if (e.target.closest('#editRequest')) {
            window.location.href =
                BASE_URL + '/request/edit_request.php?id=' + selectedRequestId;
        }



            if (e.target.closest('#deleteRequest')) {

                if (confirm("Are you sure you want to delete this request? This action cannot be undone.")) {

                    window.location.href =
                        BASE_URL + '/request/delete_request.php?id=' + selectedRequestId;

                }

            }
                    if (e.target.closest('#printRequest')) {
            window.open(
                BASE_URL + '/request/print_request.php?lmr_no=' + selectedLmrNo,
                '_blank'
            );
        }
        const statusItem = e.target.closest('.context-menu-item');
        if (statusItem) {
            updateRequestStatus(selectedRequestId, statusItem.dataset.status);
        }

        contextMenu.style.display = 'none';
    });
});

function updateRequestStatus(requestId, status) {
    fetch(BASE_URL + '/request/update_status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `request_id=${requestId}&status=${status.charAt(0).toUpperCase()+status.slice(1)}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) location.reload();
        else alert('Update failed');
    })
    .catch(() => alert('Network error'));
}
</script>


</body>
</html>
<?php $conn->close(); ?>
