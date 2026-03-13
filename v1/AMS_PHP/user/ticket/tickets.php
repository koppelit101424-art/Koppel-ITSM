<?php
include '../auth/auth.php';
include '../db/db.php';
include '../../config/config.php';

/* Logged-in user */
$user_id = $_SESSION['user_id'];

/* Fetch tickets */
$sql = "
SELECT 
    t.*,
    u.fullname AS assigned_admin
FROM ticket_tb t
LEFT JOIN user_tb u 
    ON t.assigned_to = u.user_id 
    AND u.user_type = 'admin'
WHERE t.user_id = ?
ORDER BY t.ticket_id DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$tickets = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Tickets</title>
<link rel="icon" href="../asset/img/Koppel_bip.ico">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="../asset/css/main.css" rel="stylesheet">
<link href="../asset/css/menu.css" rel="stylesheet">

<style>
/* REQUEST BUTTONS */
.request-btn {
    border: 1px solid #dee2e6;
    background-color: #ffffff;
    transition: all 0.2s ease-in-out;
    border-radius: 10px;
}
.request-btn:hover {
    background-color: #f8f9fa;
    transform: translateY(-2px);
    box-shadow: 0 6px 14px rgba(0,0,0,0.08);
}
.request-btn.incident i { color: #dc3545; }
.request-btn.service i  { color: #0d6efd; }
.request-btn.change i   { color: #198754; }
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
.badge-status-escalated {
    background-color: #0d6efd;
}.badge-status-pending {
    background-color: #ffc107;
}
.badge-status-canceled,
.badge-status-closed {
    background-color: grey;
}
.badge-status-resolved{
    background-color: #198754;
}

.status-waiting-for-support { background-color: #0d6efd; color: white; }
.status-waiting-for-customer { background-color: #0d6efd; color: white; }
.status-in-progress { background-color: #0d6efd; color: white; }
.status-pending { background-color: #ffc107; color: white; }
.status-escalated { background-color: #0d6efd; color: white; }
.status-resolved { background-color: #198754; color: white; }
.status-canceled { background-color: grey; color: white; }
.status-closed { background-color: grey; color: white; }
.status-reopened { background-color: #dc3545; color: white; }
</style>
</head>

<body>

<div class="main-content d-flex" id="mainContent">
<?php include '../sidebar.php'; ?>

<div class="content flex-grow-1">
<div class="dashboard-header"><?php include '../header.php'; ?></div>

<!-- REQUEST TYPES -->
<div class="row g-3 my-4">
    <div class="col-md-4">
        <button class="btn w-100 p-3 text-start request-btn incident"
            onclick="location.href='add.php?type=incident'">
            <h6 class="mb-1 fw-bold">
                <i class="fas fa-exclamation-circle me-2"></i> Incident Request
            </h6>
            <small class="fst-italic text-muted">
                (Ex. No internet, can’t dial outside call, can’t print, etc.)
            </small>
        </button>
    </div>

    <div class="col-md-4">
        <button class="btn w-100 p-3 text-start request-btn service"
            onclick="location.href='add.php?type=service'">
            <h6 class="mb-1 fw-bold">
                <i class="fas fa-cogs me-2"></i> Service Request
            </h6>
            <small class="fst-italic text-muted">
                (Password reset, software install, access requests, etc.)
            </small>
        </button>
    </div>

    <div class="col-md-4">
        <button class="btn w-100 p-3 text-start request-btn change"
            onclick="location.href='add.php?type=change'">
            <h6 class="mb-1 fw-bold">
                <i class="fas fa-random me-2"></i> Change Request
            </h6>
            <small class="fst-italic text-muted">
                (System/process/hardware changes, upgrades, etc.)
            </small>
        </button>
    </div>
</div>

<!-- TICKETS TABLE -->
<div class="card mt-4">
<div class="card-header">
    My Tickets
</div>

<div class="card-body table-responsive">
<table class="table table-hover" id="ticketsTable">
<thead>
<tr>
    <th>Ticket Number</th>
    <th>Priority</th>
    <th>Category</th>
    <th>Subject</th>
    <th>Assigned To</th>
    <th>Status</th>
    <th>Date Created</th>
</tr>
</thead>
<tbody id="ticketsBody">

<?php if ($tickets->num_rows > 0): ?>
<?php while ($ticket = $tickets->fetch_assoc()): ?>
<tr class="ticket-row" data-ticket-id="<?= $ticket['ticket_id'] ?>">
    <td><?= htmlspecialchars($ticket['ticket_number']) ?></td>

    <!-- PRIORITY -->
    <td>
        <span class="badge badge-priority-<?= strtolower($ticket['priority'] ?? 'medium') ?>">
            <?= ucfirst($ticket['priority'] ?? 'Medium') ?>
        </span>
    </td>

    <td><?= ucfirst($ticket['ticket_category']) ?></td>
    <td><?= htmlspecialchars($ticket['subject']) ?></td>

    <td><?= htmlspecialchars($ticket['assigned_admin'] ?? 'Unassigned') ?></td>

    <!-- STATUS -->
    <td>
        <span class="badge badge-status-<?= strtolower(str_replace(' ', '-', $ticket['status'])) ?>">
            <?= ucfirst($ticket['status']) ?>
        </span>
    </td>

    <td><?= date('m-d-Y', strtotime($ticket['created_at'] ?? $ticket['date_created'])) ?></td>
</tr>
<?php endwhile; ?>
<?php else: ?>
<tr>
    <td colspan="7" class="text-center">No tickets created yet.</td>
</tr>
<?php endif; ?>

</tbody>
</table>
</div>
</div>

</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".ticket-row").forEach(row => {
        row.addEventListener("click", () => {
            const ticketId = row.dataset.ticketId;
            window.location.href = `view.php?ticket_id=${ticketId}`;
        });
    });
});

</script>
<script>
function loadTickets() {
    fetch("fetch_tickets.php")
        .then(response => response.text())
        .then(data => {
            document.getElementById("ticketsBody").innerHTML = data;

            // Re-attach click event after refresh
            document.querySelectorAll(".ticket-row").forEach(row => {
                row.addEventListener("click", () => {
                    const ticketId = row.dataset.ticketId;
                    window.location.href = `view.php?ticket_id=${ticketId}`;
                });
            });
        });
}

// Load immediately
loadTickets();

// Refresh every 2 seconds
setInterval(loadTickets, 2000);
</script>
</body>
</html>
