<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../auth/auth.php';
include '../db/db.php';

$currentUserId = $_SESSION['user_id'] ?? null;

$ticket_id = $_GET['ticket_id'] ?? null;
if (!$ticket_id) {
    header("Location: ../tickets.php");
    exit;
}

/* Fetch ticket */
$sql = "SELECT 
            t.*,
            u.fullname AS reporter,
            a.fullname AS assignee
        FROM ticket_tb t
        LEFT JOIN user_tb u ON t.user_id = u.user_id
        LEFT JOIN user_tb a ON t.assigned_to = a.user_id
        WHERE t.ticket_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $ticket_id);
$stmt->execute();
$ticket = $stmt->get_result()->fetch_assoc();

if (!$ticket) {
    echo "<h4 class='m-5'>Ticket not found</h4>";
    exit;
}

/* Permission */
$finalStatuses = ['canceled', 'resolved', 'closed'];
$canAssignTicket = !in_array($ticket['status'], $finalStatuses);

$canEditStatus = (
    $ticket['assigned_to'] == $currentUserId
    && !in_array($ticket['status'], $finalStatuses)
);


/* Fetch chat */
$msgSql = "SELECT * FROM ticket_messages WHERE ticket_id = ? ORDER BY created_at ASC";
$msgStmt = $conn->prepare($msgSql);
$msgStmt->bind_param("i", $ticket_id);
$msgStmt->execute();
$messages = $msgStmt->get_result();

/* STATUS WORKFLOW */
$statusFlow = [
    'waiting for support' => ['waiting for support','waiting for customer','in progress','escalated','pending','canceled','resolved'],
    'pending' => ['pending','waiting for support','in progress','canceled','resolved'],
    'waiting for customer' => ['waiting for customer','waiting for support','escalated','canceled','resolved'],
    'in progress' => ['in progress','pending','canceled','resolved'],
    'escalated' => ['escalated','in progress'],
    'canceled' => ['canceled','closed'],
    'resolved' => ['resolved','closed'],
    'closed' => ['closed']
];

$currentStatus   = $ticket['status'];
$allowedStatuses = $statusFlow[$currentStatus] ?? [$currentStatus];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Ticket <?= htmlspecialchars($ticket['ticket_number']) ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="../asset/css/main.css" rel="stylesheet">
<link href="../asset/css/menu.css" rel="stylesheet">

<style>
.chat-box { height:260px; overflow-y:auto; background:#f8f9fa; padding:10px; border-radius:6px; }
.chat-msg { margin-bottom:8px; max-width:75%; }
.chat-user { background:#0d6efd; color:#fff; margin-left:auto; }
.chat-admin { background:#e9ecef; }
.chat-msg div { padding:8px; border-radius:6px; }
</style>
</head>

<body>
<div class="main-content d-flex">
<?php include 'sidebar.php'; ?>

<div class="content flex-grow-1">
<div class="container-fluid px-4 mt-4">

<div class="card">
<div class="card-header d-flex justify-content-between">
<h5>Ticket Details - <?= htmlspecialchars($ticket['ticket_number']) ?></h5>
<a href="../tickets.php" class="btn btn-secondary btn-sm">Back</a>
</div>

<div class="card-body">
<div class="row g-3">

<!-- LEFT -->
<div class="col-md-8">
<label class="fw-bold">Subject</label>
<div class="mb-3"><?= htmlspecialchars($ticket['subject']) ?></div>

<label class="fw-bold">Issue Description</label>
<div class="mb-4"><?= nl2br(htmlspecialchars($ticket['issue'])) ?></div>

<label class="fw-bold">Conversation</label>
<div class="chat-box mb-2">
<?php while($msg = $messages->fetch_assoc()): ?>
<div class="chat-msg <?= $msg['sender_role'] === 'admin' ? 'chat-admin' : 'chat-user' ?>">
<div><?= nl2br(htmlspecialchars($msg['message'])) ?></div>
</div>
<?php endwhile; ?>
</div>

<form method="post" action="send_message.php">
<input type="hidden" name="ticket_id" value="<?= $ticket_id ?>">
<textarea name="message" class="form-control mb-2" rows="2" required></textarea>
<button class="btn btn-primary btn-sm">Send</button>
</form>

<label class="fw-bold mt-4">Activity Log</label>
<div class="border rounded p-3 bg-light" style="max-height:250px; overflow-y:auto;">

<?php

$role = $_SESSION['user_type']; // must be 'admin' or 'user'

if ($role === 'admin') {

    // Admin sees ALL logs
    $logStmt = $conn->prepare("
        SELECT l.*, u.fullname
        FROM ticket_logs l
        JOIN user_tb u ON l.changed_by = u.user_id
        WHERE l.ticket_id = ?
        ORDER BY l.created_at ASC
    ");

} else {

    // User sees ONLY public logs
    $logStmt = $conn->prepare("
        SELECT l.*, u.fullname
        FROM ticket_logs l
        JOIN user_tb u ON l.changed_by = u.user_id
        WHERE l.ticket_id = ?
        AND l.is_public = 1
        ORDER BY l.created_at ASC
    ");
}

$logStmt->bind_param("i", $ticket_id);
$logStmt->execute();
$logs = $logStmt->get_result();

while ($log = $logs->fetch_assoc()):
?>

<div class="mb-3 p-2 border rounded bg-white">

    <small class="text-muted">
        <?= date("M d, Y h:i A", strtotime($log['created_at'])) ?>
    </small><br>

    <strong><?= htmlspecialchars($log['fullname']) ?></strong>

    <?php if ($log['action_type'] === 'assign'): ?>
        assigned ticket to <?= htmlspecialchars($log['new_value']) ?>

    <?php elseif ($log['action_type'] === 'escalated'): ?>
        escalated the ticket

    <?php else: ?>
        updated <b><?= htmlspecialchars($log['field_name']) ?></b>
        from <span class="text-danger"><?= htmlspecialchars($log['old_value']) ?></span>
        to <span class="text-success"><?= htmlspecialchars($log['new_value']) ?></span>
    <?php endif; ?>

    <?php if (!empty($log['comment'])): ?>
        <div class="mt-2 p-2 rounded 
            <?= $log['is_public'] ? 'bg-light' : 'bg-warning-subtle border border-warning' ?>">

            <small class="fw-bold">
                <?= $log['is_public'] ? 'Comment:' : 'Internal Note:' ?>
            </small><br>

            <?= nl2br(htmlspecialchars($log['comment'])) ?>
        </div>
    <?php endif; ?>

</div>

<?php endwhile; ?>


</div>

</div>

<!-- RIGHT -->
<div class="col-md-4">

<label class="fw-bold">Assignee</label>
<div class="mb-3" id="assigneeContainer">
<?php if ($ticket['assigned_to'] == 445 && $canAssignTicket): ?>


<button class="btn btn-link p-0 fw-bold text-primary" id="assignToMeBtn">
    Assign to me
</button>
<?php else: ?>
<?= htmlspecialchars($ticket['assignee'] ?? 'Unassigned') ?>
<?php endif; ?>
</div>

<label class="fw-bold">Sender</label>
<div class="mb-3"><?= htmlspecialchars($ticket['reporter']) ?></div>

<label class="fw-bold">Category</label>
<select class="form-select mb-3 live-update" data-field="ticket_category">
<?php foreach(['incident','service','change'] as $c): ?>
<option value="<?= $c ?>" <?= $ticket['ticket_category']===$c?'selected':'' ?>><?= ucfirst($c) ?></option>
<?php endforeach; ?>
</select>

<label class="fw-bold">Status</label>
<select class="form-select mb-3 live-update"
        data-field="status"
        id="statusSelect"
        <?= !$canEditStatus ? 'disabled' : '' ?>>
<?php foreach ($allowedStatuses as $s): ?>
<option value="<?= $s ?>" <?= $currentStatus === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
<?php endforeach; ?>
</select>

<label class="fw-bold">Priority</label>
<select class="form-select mb-3 live-update" data-field="priority">
<?php foreach(['high','medium','low'] as $p): ?>
<option value="<?= $p ?>" <?= $ticket['priority']===$p?'selected':'' ?>><?= ucfirst($p) ?></option>
<?php endforeach; ?>
</select>

<label class="fw-bold">Urgency</label>
<select class="form-select mb-3 live-update" data-field="urgency">
<?php foreach(['low','medium','high'] as $u): ?>
<option value="<?= $u ?>" <?= ($ticket['urgency']??'medium')===$u?'selected':'' ?>><?= ucfirst($u) ?></option>
<?php endforeach; ?>
</select>

<label class="fw-bold">Impact</label>
<select class="form-select mb-3 live-update" data-field="impact">
<?php foreach(['low','moderate','high'] as $i): ?>
<option value="<?= $i ?>" <?= ($ticket['impact']??'moderate')===$i?'selected':'' ?>><?= ucfirst($i) ?></option>
<?php endforeach; ?>
</select>

<label class="fw-bold">Pending Reason</label>
<select class="form-select live-update" data-field="pending_reason">
<?php foreach(['None','Waiting for user','Waiting for vendor','Internal review'] as $r): ?>
<option value="<?= $r ?>" <?= ($ticket['pending_reason']??'None')===$r?'selected':'' ?>><?= $r ?></option>
<?php endforeach; ?>
</select>

</div>
</div>
</div>
</div>

</div>
</div>
</div>

    <!-- STATUS MODAL -->
    <div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
    <div class="modal-content">

    <div class="modal-header">
    <h5 class="modal-title">Change Status</h5>
    <button class="btn-close" data-bs-dismiss="modal"></button>
    </div>

    <div class="modal-body">
    <input type="hidden" id="newStatusValue">

    <label>Reason</label>
    <textarea id="statusComment" class="form-control mb-3" rows="3"></textarea>

    <div class="form-check">
    <input class="form-check-input" type="checkbox" id="publicComment" checked>
    <label class="form-check-label">Visible to ticket sender</label>
    </div>
    </div>

    <div class="modal-footer">
    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
    <button class="btn btn-primary" id="confirmStatusChange">Confirm</button>
    </div>

    </div>
    </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
const canEditStatus = <?= $canEditStatus ? 'true' : 'false' ?>;
const ticketId = <?= $ticket_id ?>;
const statusSelect = document.getElementById('statusSelect');
const modal = new bootstrap.Modal(document.getElementById('statusModal'));
let previousStatus = statusSelect ? statusSelect.value : null;

if (statusSelect && canEditStatus) {

statusSelect.addEventListener('change', function() {

const newStatus = this.value;

document.getElementById('newStatusValue').value = newStatus;
document.getElementById('statusComment').value = '';
document.getElementById('publicComment').checked = true;

this.value = previousStatus;
modal.show();
});

document.getElementById('confirmStatusChange').addEventListener('click', function() {

const newStatus = document.getElementById('newStatusValue').value;
const comment = document.getElementById('statusComment').value.trim();
const isPublic = document.getElementById('publicComment').checked ? 1 : 0;

if (!comment) {
alert("Reason is required.");
return;
}

fetch('update_ticket_field.php', {
method:'POST',
headers:{'Content-Type':'application/json'},
body:JSON.stringify({
ticket_id:ticketId,
field:'status',
value:newStatus,
comment:comment,
is_public:isPublic
})
})
.then(res=>res.json())
.then(data=>{
if(data.success) location.reload();
});
});
}
</script>
</body>
</html>
<?php $conn->close(); ?>
