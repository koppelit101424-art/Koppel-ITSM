<?php
include 'auth/auth.php';
include 'db/db.php';

// Generate base URL dynamically
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$scriptPath = dirname($_SERVER['SCRIPT_NAME']);
$basePath = rtrim($scriptPath, '/\\');
$baseUrl = "$protocol://$host$basePath";

// Fetch all tickets
$sql = "
SELECT 
    t.*,
    u.fullname AS user_fullname,
    a.fullname AS assigned_admin
FROM ticket_tb t
LEFT JOIN user_tb u ON t.user_id = u.user_id
LEFT JOIN user_tb a 
    ON t.assigned_to = a.user_id 
    AND a.user_type = 'admin'
ORDER BY t.ticket_id DESC
";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Tickets</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="asset/css/main.css" rel="stylesheet">
<link href="asset/css/menu.css" rel="stylesheet">
<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">

<style>
.table-hover tbody tr:hover {
    background-color: #f1f1f1;
    cursor: pointer;
}

/* PRIORITY COLORS */
.badge-priority-highest { background-color: #dc3545; }
.badge-priority-high { background-color: #fd7e14; }
.badge-priority-medium { background-color: #ffc107; color: #000; }
.badge-priority-low { background-color: #0d6efd; }
.badge-priority-lowest { background-color: #6ea8fe; }

/* STATUS COLORS */
.badge-status-waiting-for-support,
.badge-status-waiting-for-customer,
.badge-status-in-progress,
.badge-status-escalated,
.badge-status-pending {
    background-color: #0d6efd;
}

.badge-status-resolved,
.badge-status-canceled,
.badge-status-closed {
    background-color: #198754;
}
</style>
</head>

<body>

<div class="sidebar col-md-2" id="sidebar" style="height: 100vh; width: 250px;">
    <?php include 'sidebar.php'; ?>
</div>

<div class="main-content" id="mainContent">
<?php include 'header.php'; ?>

<div class="card">
<div class="card-header d-flex justify-content-between">
    <span>All Tickets</span>
</div>

<div class="card-body table-responsive">

<!-- STATUS FILTER BUTTONS -->
<div class="d-flex flex-wrap gap-2 mb-3">
    <button class="btn btn-outline-primary status-filter active" data-status="">All</button>

    <button class="btn btn-outline-primary status-filter" data-status="waiting for support">
        Waiting for Support
    </button>

    <button class="btn btn-outline-primary status-filter" data-status="waiting for customer">
        Waiting for Customer
    </button>

    <button class="btn btn-outline-primary status-filter" data-status="in progress">
        In Progress
    </button>

    <button class="btn btn-outline-primary status-filter" data-status="pending">
        Pending
    </button>

    <button class="btn btn-outline-primary status-filter" data-status="escalated">
        Escalated
    </button>

    <button class="btn btn-outline-primary status-filter" data-status="resolved">
        Resolved
    </button>

    <button class="btn btn-outline-primary status-filter" data-status="canceled">
        Canceled
    </button>

    <button class="btn btn-outline-primary status-filter" data-status="closed">
        Closed
    </button>
</div>

<table id="ticketsTable" class="table table-hover table-striped">
<thead>
<tr>
    <th>Ticket Number</th>
    <th>Priority</th>
    <th>User</th>
    <th>Category</th>
    <th>Subject</th>
    <th>Assigned To</th>
    <th>Status</th>
    <th>Date Created</th>
</tr>
</thead>

<tbody>
<?php while ($ticket = $result->fetch_assoc()): ?>
<tr class="ticket-row"
    data-ticket-id="<?= $ticket['ticket_id'] ?>"
    data-status="<?= strtolower($ticket['status']) ?>">

    <td><?= htmlspecialchars($ticket['ticket_number']) ?></td>

    <td>
        <span class="badge badge-priority-<?= strtolower($ticket['priority'] ?? 'medium') ?>">
            <?= ucfirst($ticket['priority'] ?? 'Medium') ?>
        </span>
    </td>

    <td><?= htmlspecialchars($ticket['user_fullname']) ?></td>
    <td><?= ucfirst($ticket['ticket_category']) ?></td>
    <td><?= htmlspecialchars($ticket['subject']) ?></td>
    <td><?= htmlspecialchars($ticket['assigned_admin'] ?? 'Unassigned') ?></td>

    <td>
        <span class="badge badge-status-<?= strtolower(str_replace(' ', '-', $ticket['status'])) ?>">
            <?= strtoupper($ticket['status']) ?>
        </span>
    </td>

    <td><?= date('m-d-Y', strtotime($ticket['created_at'])) ?></td>
</tr>
<?php endwhile; ?>
</tbody>
</table>

</div>
</div>
</div>

<script>const BASE_URL = '<?= $baseUrl ?>';</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function () {

    const table = $('#ticketsTable').DataTable({
        pageLength: 10,
        order: [[0, "desc"]]
    });

    // STATUS FILTER LOGIC
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

    // ROW CLICK → VIEW TICKET
    $('#ticketsTable tbody').on('click', 'tr', function () {
        const ticketId = $(this).data('ticket-id');
        if (ticketId) {
            window.location.href = BASE_URL + '/ticket/view_ticket.php?ticket_id=' + ticketId;
        }
    });

});
</script>

</body>
</html>

<?php $conn->close(); ?>
