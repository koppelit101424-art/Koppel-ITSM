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
<html>
<head>
<title>Admin Tickets</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">

<style>
.table-hover tbody tr:hover {
    background-color: #f1f1f1;
    cursor: pointer;
}
</style>
</head>

<body>

<div class="container mt-4">

<div class="card">
<div class="card-header">Tickets</div>
<div class="card-body table-responsive">

<table id="ticketsTable" class="table table-hover table-striped">
<thead>
<tr>
    <th>Ticket #</th>
    <th>User</th>
    <th>Category</th>
    <th>Impact</th>
    <th>Priority</th>
    <th>Subject</th>
    <th>Assigned To</th>
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
    <td><?= ucfirst($ticket['priority']) ?></td>
    <td><?= htmlspecialchars($ticket['subject']) ?></td>

    <!-- ASSIGNED TO DROPDOWN -->
    <td onclick="event.stopPropagation();">
        <select class="form-select form-select-sm assign-admin"
                data-ticket-id="<?= $ticket['ticket_id'] ?>">

            <option value="">Unassigned</option>

            <?php foreach ($admins as $admin): ?>
                <option value="<?= $admin['user_id'] ?>"
                    <?= ($ticket['assigned_to'] == $admin['user_id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($admin['fullname']) ?>
                </option>
            <?php endforeach; ?>

        </select>
    </td>

    <td><?= strtoupper($ticket['status']) ?></td>
    <td><?= date('m-d-Y', strtotime($ticket['date_created'])) ?></td>

</tr>
<?php endwhile; ?>
</tbody>
</table>

</div>
</div>
</div>

<script>
const BASE_URL = '<?= $baseUrl ?>';
</script>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function () {

    $('#ticketsTable').DataTable({
        pageLength: 10,
        order: [[0, "desc"]]
    });

    // Row click (view ticket)
    $('#ticketsTable tbody').on('click', 'tr', function () {
        const ticketId = $(this).data('ticket-id');
        if (ticketId) {
            window.location.href = BASE_URL + '/ticket/view_ticket.php?ticket_id=' + ticketId;
        }
    });

    // Reassign ticket via AJAX
    $(document).on('change', '.assign-admin', function () {

        let ticketId = $(this).data('ticket-id');
        let adminId = $(this).val();

        $.ajax({
            url: BASE_URL + '/ticket/reassign_ticket.php',
            type: 'POST',
            data: {
                ticket_id: ticketId,
                admin_id: adminId
            },
            success: function () {
                console.log('Ticket reassigned successfully');
            },
            error: function () {
                alert('Error updating assignment');
            }
        });

    });

});
</script>

</body>
</html>

<?php $conn->close(); ?>