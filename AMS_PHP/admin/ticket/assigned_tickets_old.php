<?php
include '../auth/auth.php';
include '../db/db.php';

// Generate base URL dynamically
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$scriptPath = dirname($_SERVER['SCRIPT_NAME']);
$basePath = rtrim($scriptPath, '/\\');
$baseUrl = "$protocol://$host$basePath";

// Logged in admin ID
$adminId = $_SESSION['user_id'];

// ✅ Fetch ONLY tickets assigned to logged in admin
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
WHERE t.assigned_to = ?
ORDER BY t.ticket_id DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $adminId);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Assigned Tickets</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="../asset/css/main.css" rel="stylesheet">
<link href="../asset/css/menu.css" rel="stylesheet">
<link href="sidebar.css" rel="stylesheet">
<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">

<style>
.table-hover tbody tr:hover {
    background-color: #f1f1f1;
    cursor: pointer;
}

/* PRIORITY COLORS */
.badge-priority-highest { background-color: #dc3545; }
.badge-priority-high { background-color: #fd7e14; }
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
<?php include 'header2.php'; ?>
    <div class="card">
        <div class="card-header">
            My Assigned Tickets
        </div>

        <div class="card-body table-responsive">

            <div class="row g-2">

                <div class="col-md-2">
                    <input type="text" id="userFilter" class="form-control" placeholder="User">
                </div>

                <div class="col-md-3">
                    <input type="text" id="subjectFilter" class="form-control" placeholder="Subject">
                </div>

                <div class="col-md-2">
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
                    <button id="clearFilters" class="btn btn-secondary">Reset</button>
                </div>

            </div>
            <br>

            <table id="ticketsTable" class="table table-hover table-striped">
                <thead>
                <tr>
                    <th>Ticket #</th>
                    <th>User</th>
                    <th>Category</th>
                    <th>Impact</th>
                    <th>Priority</th>
                    <th>Subject</th>
                    <th>Status</th>
                    <th>Date Created</th>
                </tr>
                </thead>

                <tbody>
                <?php while ($ticket = $result->fetch_assoc()): ?>
                <tr data-ticket-id="<?= $ticket['ticket_id'] ?>">

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

                    <?php
                    $statusFlow = [
                        'waiting for support' => ['waiting for support','waiting for customer','in progress','escalated','pending','canceled','resolved'],
                        'pending' => ['pending','waiting for support','in progress','canceled','resolved'],
                        'waiting for customer' => ['waiting for customer','waiting for support','escalated','canceled','resolved'],
                        'in progress' => ['in progress','pending','canceled','resolved'],
                        'escalated' => ['escalated','in progress'],
                        'canceled' => ['canceled','closed','reopened'],
                        'resolved' => ['resolved','closed','reopened'],
                        'reopened' => ['reopened','waiting for support','waiting for customer','in progress','escalated','pending','canceled','resolved'],
                        'closed' => ['closed']
                    ];

                    $currentStatus = strtolower($ticket['status']);
                    $allowedStatuses = $statusFlow[$currentStatus] ?? [$currentStatus];
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

    $.fn.dataTable.ext.search.push(function (settings, data) {

        if ($('#priorityFilter').val() &&
            !data[4].toLowerCase().includes($('#priorityFilter').val())) return false;

        if ($('#userFilter').val() &&
            !data[1].toLowerCase().includes($('#userFilter').val().toLowerCase())) return false;

        if ($('#subjectFilter').val() &&
            !data[5].toLowerCase().includes($('#subjectFilter').val().toLowerCase())) return false;

        if ($('#statusSelectFilter').val() &&
            !data[6].toLowerCase().includes($('#statusSelectFilter').val())) return false;

        return true;
    });

    $('#priorityFilter, #userFilter, #subjectFilter, #statusSelectFilter')
        .on('change keyup', () => table.draw());

    $('#clearFilters').on('click', function () {
        $('#priorityFilter, #userFilter, #subjectFilter, #statusSelectFilter').val('');
        table.draw();
    });

    $('#ticketsTable tbody').on('click', 'tr', function () {
        const ticketId = $(this).data('ticket-id');
        if (ticketId) {
            window.location.href = BASE_URL + '/view_ticket.php?ticket_id=' + ticketId;
        }
    });

});
</script>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
