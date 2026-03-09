<?php
include 'auth/auth.php';
include 'db/db.php';

// Generate base URL dynamically
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$scriptPath = dirname($_SERVER['SCRIPT_NAME']);
$basePath = rtrim($scriptPath, '/\\');
$baseUrl = "$protocol://$host$basePath";

// Logged in admin ID
$adminId = $_SESSION['user_id'];

/* ==============================
   FETCH ADMIN USERS ONLY
============================== */
$admins = [];
$adminQuery = "
    SELECT user_id, fullname 
    FROM user_tb 
    WHERE user_type = 'admin'
    ORDER BY fullname ASC
";
$adminResult = $conn->query($adminQuery);

if ($adminResult && $adminResult->num_rows > 0) {
    while ($row = $adminResult->fetch_assoc()) {
        $admins[] = $row;
    }
}

/* ==============================
   FETCH TICKETS
============================== */
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
<title>ITSM - Tickets</title>
<link rel="icon" type="image/x-icon" href="asset/img/Koppel_bip.ico">
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
.badge-priority-highest { background-color: #8b0a17; }
.badge-priority-high { background-color: #dc3545; }
.badge-priority-medium { background-color: #ffc107; }
.badge-priority-low { background-color: #0d6efd; }
.badge-priority-lowest { background-color: #6ea8fe; }

/* STATUS COLORS */
.badge-status-reopened,
.badge-status-waiting-for-support,
.badge-status-waiting-for-customer,
.badge-status-in-progress,
.badge-status-escalated,
.badge-status-pending {
    background-color: #0d6efd;
}


.badge-status-canceled,
.badge-status-closed {
    background-color: grey;
}
.badge-status-resolved{
    background-color: #198754;
}
.select-unassigned {
    background-color: white !important;
      
}
.status-waiting-for-support { background-color: #0d6efd; color: white; }
.status-waiting-for-customer { background-color: #0d6efd; color: white; }
.status-in-progress { background-color: #0d6efd; color: white; }
.status-pending { background-color: #ffc107; color: white; }
.status-escalated { background-color: #0d6efd; color: white; }

.status-resolved { background-color: #198754; color: white; }
.status-canceled { background-color: grey; color: white; }
.status-closed { background-color: grey; color: white; }
.status-reopened { background-color: #0d6efd; color: white; }
</style>
</head>

<body>

<div class="sidebar col-md-2" id="sidebar" style="height: 100vh; width: 250px;">
    <?php include 'sidebar.php'; ?>
</div>

<div class="main-content" id="mainContent">
<?php include 'header.php'; ?>

<div class="card">
<div class="card-header">
    <span>Tickets</span>
    <!-- <span><a href="ticket/add.php" class="btn btn-sm btn-primary me-2">
    <i class="fas fa-plus me-1"></i> Add Tickets
</a></span>      -->
</div>

<div class="card-body table-responsive">

<!-- STATUS FILTER BUTTONS -->
<!-- <div class="d-flex flex-wrap gap-2 mb-3">
    <button class="btn btn-outline-primary status-filter active" data-status="">All</button>
    <button class="btn btn-outline-primary status-filter" data-status="waiting for support">Waiting for Support</button>
    <button class="btn btn-outline-primary status-filter" data-status="waiting for customer">Waiting for Customer</button>
    <button class="btn btn-outline-primary status-filter" data-status="in progress">In Progress</button>
    <button class="btn btn-outline-primary status-filter" data-status="pending">Pending</button>
    <button class="btn btn-outline-primary status-filter" data-status="escalated">Escalated</button>
    <button class="btn btn-outline-primary status-filter" data-status="resolved">Resolved</button>
    <button class="btn btn-outline-primary status-filter" data-status="canceled">Canceled</button>
    <button class="btn btn-outline-primary status-filter" data-status="closed">Closed</button>
</div> -->

<!-- SMART MULTI FILTER -->
<!-- <div class="card mb-3">
<div class="card-body"> -->
<div class="row g-2">

   <div class="col-md-2">
    <!--     <label class="form-label">View</label> -->
         <select id="assignedFilter" class="form-select">
            <option value="assigned">Assigned Tickets</option>
            <option value="all" selected>All Tickets</option>
        </select>
    </div> 



    <div class="col-md-2">
        <!-- <label class="form-label">User</label> -->
        <input type="text" id="userFilter" class="form-control" placeholder="User">
    </div>

    <div class="col-md-3">
        <!-- <label class="form-label">Subject</label> -->
        <input type="text" id="subjectFilter" class="form-control" placeholder="Subject">
    </div>
        <div class="col-md-2">
        <!-- <label class="form-label">Priority</label> -->
        <select id="priorityFilter" class="form-select">
            <option value="">Select Priority</option>
            <option value="highest">Highest</option>
            <option value="high">High</option>
            <option value="medium">Medium</option>
            <option value="low">Low</option>
            <option value="lowest">Lowest</option>
        </select>
    </div>

    <div class="col-md-2">
        <!-- <label class="form-label">Status</label> -->
        <select id="statusSelectFilter" class="form-select">
            <option value="">Select Status</option>
            <option value="waiting for support">Waiting for Support</option>
            <option value="waiting for customer">Waiting for Customer</option>
            <option value="in progress">In Progress</option>
            <option value="pending">Pending</option>
            <option value="escalated">Escalated</option>
            <option value="resolved">Resolved</option>
            <option value="canceled">Canceled</option>
            <option value="closed">Closed</option>
        </select>
    </div>

    <div class="col-md-1 d-grid">
        <!-- <label class="form-label">&nbsp;</label> -->
        <button id="clearFilters" class="btn btn-secondary">
            <!-- <i class="fa fa-xmark"></i> -->
             Reset
        </button>
    </div>

</div><br>
<!-- </div>
</div> -->

<table id="ticketsTable" class="table table-hover table-striped">
<thead>
<tr>
    <th>Ticket #</th>
    <th>User</th>
    <th>Category</th>
    <th>Impact</th>
    <th>Priority</th>
    <th>Subject</th>
    <th style="width: 135px;">Assigned To</th>
    <th style="width: 160px;">Status</th>
    <th>Date Created</th>
</tr>
</thead>
<!-- id="adminTicketsBody" -->
<tbody >
<?php while ($ticket = $result->fetch_assoc()): ?>
<tr
    data-ticket-id="<?= $ticket['ticket_id'] ?>"
    data-status="<?= strtolower($ticket['status']) ?>"
    data-assigned="<?= $ticket['assigned_to'] == $adminId ? 'yes' : 'no' ?>">

    <td><?= htmlspecialchars($ticket['ticket_number']) ?></td>
    <td><?= htmlspecialchars($ticket['user_fullname']) ?></td>
    <td><?= ucfirst($ticket['ticket_category']) ?></td>
    <td><?= ucfirst($ticket['impact']) ?></td>
    <td>
        <span class="badge badge-priority-<?= strtolower($ticket['priority']) ?>">
            <?= ucfirst($ticket['priority']) ?>
        </span>
    </td>



    <td><?= htmlspecialchars($ticket['subject']) ?></td>
    <!-- ASSIGNED TO DROPDOWN -->
<td onclick="event.stopPropagation();" style="width:135px;">

    <select class="form-select form-select-sm assign-admin 
            <?= empty($ticket['assigned_to']) ? 'select-unassigned' : '' ?>"
            data-ticket-id="<?= $ticket['ticket_id'] ?>"
            data-current="<?= $ticket['assigned_to'] ?>">

        <?php foreach ($admins as $admin): ?>
            <option value="<?= $admin['user_id'] ?>" 
                <?= ($ticket['assigned_to'] == $admin['user_id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($admin['fullname']) ?>
            </option>
        <?php endforeach; ?>

    </select>

</td>

<?php
// Define the workflow
$statusFlow = [
    'waiting for support' => ['waiting for support','waiting for customer','in progress','escalated','pending','canceled','resolved'],
    'pending' => ['pending','waiting for support','in progress','canceled','resolved'],
    'waiting for customer' => ['waiting for customer','waiting for support','escalated','canceled','resolved'],
    'in progress' => ['in progress','pending','canceled','resolved'],
    'escalated' => ['escalated','in progress'],
    'canceled' => ['canceled','closed', 'reopened'],
    'resolved' => ['resolved','closed', 'reopened'],
    'reopened' => ['reopened','waiting for support','waiting for customer','in progress','escalated','pending','canceled','resolved'],
    'closed' => ['closed']
];

$currentStatus = strtolower($ticket['status']); // current status of this ticket
$allowedStatuses = $statusFlow[$currentStatus] ?? [$currentStatus]; // fallback to current status only
?>

<td class="status-cell" onclick="event.stopPropagation();" style="width:150px;">
    <select class="form-select form-select-sm status-select status-<?= str_replace(' ', '-', $currentStatus) ?>"
            data-ticket-id="<?= $ticket['ticket_id'] ?>"
            data-current="<?= $currentStatus ?>">
        <?php foreach ($allowedStatuses as $statusOption): ?>
            <option value="<?= $statusOption ?>" <?= $statusOption == $currentStatus ? 'selected' : '' ?>>
                <?= ucwords($statusOption) ?>
            </option>
        <?php endforeach; ?>
    </select>
</td>

    <td><?= date('m-d-Y', strtotime($ticket['date_created'])) ?></td>
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

        $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {

            const row = table.row(dataIndex).node();

            if ($('#assignedFilter').val() === 'assigned' &&
                row.getAttribute('data-assigned') !== 'yes') return false;

            const statusBtn = $('.status-filter.active').data('status');
            if (statusBtn && row.getAttribute('data-status') !== statusBtn) return false;

            if ($('#priorityFilter').val() &&
                !data[4].toLowerCase().includes($('#priorityFilter').val())) return false;

            if ($('#userFilter').val() &&
                !data[1].toLowerCase().includes($('#userFilter').val().toLowerCase())) return false;

            if ($('#subjectFilter').val() &&
                !data[5].toLowerCase().includes($('#subjectFilter').val().toLowerCase())) return false;

            if ($('#statusSelectFilter').val() &&
                !data[7].toLowerCase().includes($('#statusSelectFilter').val())) return false;

            return true;
        });

        $('#assignedFilter, #priorityFilter, #userFilter, #subjectFilter, #statusSelectFilter')
            .on('change keyup', () => table.draw());

        $('.status-filter').on('click', function () {
            $('.status-filter').removeClass('active');
            $(this).addClass('active');
            table.draw();
        });

        $('#clearFilters').on('click', function () {
            $('#priorityFilter, #userFilter, #subjectFilter, #statusSelectFilter').val('');
            $('.status-filter').removeClass('active');
            $('.status-filter[data-status=""]').addClass('active');
            table.draw();
        });

        $('#ticketsTable tbody').on('click', 'tr', function () {
            const ticketId = $(this).data('ticket-id');
            if (ticketId) {
                window.location.href = BASE_URL + '/ticket/view_ticket.php?ticket_id=' + ticketId;
            }
        });

        table.draw(); // initial → My Tickets
    });
</script>
<script>
$(document).ready(function () {

    // Row click (view ticket)
    $('#ticketsTable tbody').on('click', 'tr', function () {
        const ticketId = $(this).data('ticket-id');
        if (ticketId) {
            window.location.href = BASE_URL + '/ticket/view_ticket.php?ticket_id=' + ticketId;
        }
    });

    // Reassign ticket via AJAX
    $(document).on('change', '.assign-admin', function (e) {

        e.stopPropagation();

        let select = $(this);
        let ticketId = select.data('ticket-id');
        let currentValue = select.data('current') ?? '';
        let newValue = select.val();

        if (currentValue == newValue) return; // nothing changed

        let confirmChange = confirm("Are you sure you want to change the assigned admin?");
        if (!confirmChange) {
            select.val(currentValue); // revert selection
            return;
        }

        $.ajax({
            url: BASE_URL + '/ticket/reassign_ticket.php',
            type: 'POST',
            data: {
                ticket_id: ticketId,
                admin_id: newValue
            },
            success: function () {

                // Update select current value
                select.data('current', newValue);

                // Update background color for unassigned
                if (newValue === "" || newValue == "0") {
                    select.addClass('select-unassigned');
                } else {
                    select.removeClass('select-unassigned');
                }

                // Automatically update Status column instantly
                let row = select.closest('tr');
                let statusCell = row.find('td.status-cell');

                // Determine new status
                let statusValue = (newValue === "" || newValue == "0") ? 'waiting for support' : 'in progress';
                row.attr('data-status', statusValue); // update row data-status

                // Update the status dropdown in the table cell
                let statusSelect = statusCell.find('.status-select');
                statusSelect.val(statusValue);
                statusSelect.data('current', statusValue);

                // Optionally update badge background instantly (if using badges in place of select)
                // let className = 'badge-status-' + statusValue.replace(/ /g, '-');
                // statusCell.html('<span class="badge ' + className + '">' + statusValue.toUpperCase() + '</span>');

                // Refresh the DataTable row so filters and sorting are correct
                // $('#ticketsTable').DataTable().row(row).invalidate().draw(false);
                location.reload();
            },
            error: function () {
                alert('Failed to update assignment.');
                select.val(currentValue);
            }
        });

    });

});
</script>
<script>
$(document).on('change', '.status-select', function (e) {
    e.stopPropagation();

    let select = $(this);
    let ticketId = select.data('ticket-id');
    let currentValue = select.data('current');
    let newValue = select.val();

    if (currentValue === newValue) return; // no change

    let confirmChange = confirm("Are you sure you want to change the ticket status?");
    if (!confirmChange) {
        select.val(currentValue);
        return;
    }

    $.ajax({
        url: BASE_URL + '/ticket/update_ticket_status.php', // endpoint to update DB
        type: 'POST',
        data: {
            ticket_id: ticketId,
            status: newValue
        },
        success: function () {
            // Update stored current value
            select.data('current', newValue);

            // Update background color based on status
            // First remove old status classes
            select.removeClass(function(index, className) {
                return (className.match(/(^|\s)status-\S+/g) || []).join(' ');
            });
            // Add new status class
            select.addClass('status-' + newValue.toLowerCase().replace(/ /g, '-'));

            // Optionally update the row's data-status attribute
            select.closest('tr').attr('data-status', newValue.toLowerCase());

            // Optional: reload the row completely (uncomment if needed)
            // location.reload(); // full page reload
            // OR reload only the table data via DataTables API if using DT
            $('#ticketsTable').DataTable().row(select.closest('tr')).invalidate().draw(false);
        },
        error: function () {
            alert('Failed to update status.');
            select.val(currentValue);
        }
    });
});
</script>
<script>
function loadAdminTickets() {
    fetch("ticket/fetch_admin_tickets.php")
        .then(response => response.text())
        .then(data => {
            document.getElementById("adminTicketsBody").innerHTML = data;

            // Re-attach click event
            document.querySelectorAll(".ticket-row").forEach(row => {
                row.addEventListener("click", () => {
                    const ticketId = row.dataset.ticketId;
                    window.location.href = `view_ticket.php?ticket_id=${ticketId}`;
                });
            });
        });
}

// Load immediately
loadAdminTickets();

// Refresh every 2 seconds
setInterval(loadAdminTickets, 2000);
</script>
</body>
</html>

<?php $conn->close(); ?>
