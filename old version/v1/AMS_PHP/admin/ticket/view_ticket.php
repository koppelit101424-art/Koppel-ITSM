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
        /* Fetch attachments */
        $attachStmt = $conn->prepare("
            SELECT file_name, file_path
            FROM ticket_attachments
            WHERE ticket_id = ?
        ");
        $attachStmt->bind_param("i", $ticket_id);
        $attachStmt->execute();
        $attachments = $attachStmt->get_result();

        if (!$ticket) {
            echo "<h4 class='m-5'>Ticket not found</h4>";
            exit;
        }

        /* Permission */
        $finalStatuses = ['closed'];
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
            'canceled' => ['canceled','closed', 'reopened'],
            'resolved' => ['resolved','closed', 'reopened'],
            'reopened' => ['reopened','waiting for support','waiting for customer','in progress','escalated','pending','canceled','resolved'],
            'closed' => ['closed']
        ];

        $currentStatus   = $ticket['status'];
        $allowedStatuses = $statusFlow[$currentStatus] ?? [$currentStatus];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<!-- <title>Ticket <?= htmlspecialchars($ticket['ticket_number']) ?></title> -->
<title>ITSM - View Tickets</title>
<link rel="icon" type="image/x-icon" href="../asset/img/Koppel_bip.ico">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="../asset/css/main.css" rel="stylesheet">
<link href="../asset/css/menu.css" rel="stylesheet">

<!-- <style>
.chat-box { height:400px; overflow-y:auto; background:#f8f9fa; padding:10px; border-radius:6px; }
.chat-msg { display:flex; margin-bottom:8px; max-width:35%; }
.chat-msg .bubble { padding:10px 15px; border-radius:20px; word-wrap:break-word; font-size:0.95rem; }
.chat-user { margin-left:auto; }
.chat-user .bubble { background-color:#0d6efd; color:#fff; border-bottom-right-radius:0; }
.chat-admin { margin-right:auto; }
.chat-admin .bubble { background-color:#e4e6eb; color:#000; border-bottom-left-radius:0; }
.chat-time { font-size:0.7rem; color:#666; margin-top:2px; text-align:right; }
</style> -->
<style>
    .chat-box {
    height:400px;
    overflow-y:auto;
    background:#f8f9fa;
    padding:10px;
    border-radius:6px;
}

.chat-msg {
    display:flex;
    margin-bottom:8px;
    width:100%;
}

.chat-msg .bubble {
    padding:10px 15px;
    border-radius:20px;
    word-wrap:break-word;
    font-size:0.95rem;
    max-width:65%;   /* controls bubble width properly */
}

.chat-user {
    justify-content:flex-end;
}

.chat-user .bubble {
    background-color:#0d6efd;
    color:#fff;
    border-bottom-right-radius:0;
}

.chat-admin {
    justify-content:flex-start;
}

.chat-admin .bubble {
    background-color:#e4e6eb;
    color:#000;
    border-bottom-left-radius:0;
}

.chat-time {
    font-size:0.7rem;
    color:#666;
    margin-top:2px;
    text-align:right;
}
</style>

</head>

<body>
<div class="main-content d-flex">
<?php include 'ticket_sidebar.php'; ?>

<div class="content flex-grow-1">

<div class="card">
<div class="card-header d-flex justify-content-between">
<h5><?= htmlspecialchars($ticket['ticket_number']) ?> - <?= htmlspecialchars($ticket['subject']) ?></h5>
<a href="#" onclick="window.history.back(); return false;" class="btn btn-secondary btn-sm">Back</a>
</div>

<div class="card-body">
<div class="row g-3">

<!-- LEFT -->
<div class="col-md-8">
<label class="fw-bold">Subject Details</label>
<div class="mb-3"><?= htmlspecialchars($ticket['subject_details']) ?></div>

<!-- <label class="fw-bold">Issue Description</label>
<div class="mb-4"><?= nl2br(htmlspecialchars($ticket['issue'])) ?></div> -->

<label class="fw-bold">Issue Description</label>
<div class="mb-3"><?= nl2br(htmlspecialchars($ticket['issue'])) ?></div>

<label class="fw-bold">Attachments</label>
<div class="mb-4">

<?php if ($attachments->num_rows > 0): ?>

    <?php while($file = $attachments->fetch_assoc()): ?>

        <div class="d-flex align-items-center mb-2">

            <!-- Preview Button -->
        <button 
            class="btn btn-sm btn-outline-secondary me-2 preview-file"
            data-file="<?= htmlspecialchars($file['file_path']) ?>"
            data-name="<?= htmlspecialchars($file['file_name']) ?>"
        ><i class="fa-solid fa-eye"></i>
            <?= htmlspecialchars($file['file_name']) ?>
        </button>

            <!-- Download Button -->
            <a href="<?= htmlspecialchars($file['file_path']) ?>" 
               download
               class="btn btn-sm btn-outline-primary">
                Download
            </a>

        </div>

    <?php endwhile; ?>

<?php else: ?>

    <span class="text-muted">No attachment</span>

<?php endif; ?>

</div>

<!-- ================= TABS ================= -->
<ul class="nav nav-tabs mb-2">
    <li class="nav-item">
        <button class="nav-link " data-bs-toggle="tab" data-bs-target="#chatTab">Conversation</button>
    </li>
    <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#activityTab">Activity Log</button>
    </li>
</ul>

<div class="tab-content border border-top-0 rounded-bottom p-3 bg-light">

<!-- ===== CHAT TAB ===== -->
<div class="tab-pane  fade" id="chatTab">

    <div class="chat-box mb-2" id="chatBox">
        <?php while($msg = $messages->fetch_assoc()): ?>
        <div class="chat-msg <?= ($msg['sender_id']==$currentUserId) ? 'chat-user' : 'chat-admin' ?>">
            <div class="bubble">
                <?= nl2br(htmlspecialchars($msg['message'])) ?>
                <div class="chat-time"><?= date("M d, Y h:i A", strtotime($msg['created_at'])) ?></div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>


    <form id="chatForm">
        <input type="hidden" name="ticket_id" value="<?= $ticket_id ?>">
        <!-- <textarea name="chatMessage"  class="form-control mb-2" rows="2" required></textarea> -->
         <textarea id="chatMessage" name="chatMessage" class="form-control mb-2" rows="2" required></textarea>

        <button class="btn btn-primary btn-sm">Send</button>
    </form>



</div>

<!-- ===== ACTIVITY TAB ===== -->
<div class="tab-pane show active" id="activityTab">

<div id="activityLog" style="max-height:400px; overflow-y:auto;">
    
<?php
$role = $_SESSION['user_type'];

if ($role === 'admin') {
    $logStmt = $conn->prepare("
        SELECT l.*, u.fullname
        FROM ticket_logs l
        JOIN user_tb u ON l.changed_by = u.user_id
        WHERE l.ticket_id = ?
        ORDER BY l.created_at DESC
    ");
} else {
    $logStmt = $conn->prepare("
        SELECT l.*, u.fullname
        FROM ticket_logs l
        JOIN user_tb u ON l.changed_by = u.user_id
        WHERE l.ticket_id = ?
        AND l.is_public = 1
        ORDER BY l.created_at DESC
    ");
}

$logStmt->bind_param("i", $ticket_id);
$logStmt->execute();
$logs = $logStmt->get_result();
  if ($logs->num_rows === 0): 
        echo '<div class="text-center text-muted">No Activity Log Available.</div>';
    endif; 
while ($log = $logs->fetch_assoc()):
?>
<div class="mb-3 p-2 border rounded bg-white">
    <small class="text-muted"><?= date("M d, Y h:i A", strtotime($log['created_at'])) ?></small><br>

    <?php if ($log['action_type'] === 'assign'): ?>
        Ticket assigned to <strong><?= htmlspecialchars($log['fullname']) ?></strong>
        <!-- <?= htmlspecialchars($log['new_value']) ?> -->
    <?php elseif ($log['action_type'] === 'escalated'): ?>
            <strong><?= htmlspecialchars($log['fullname']) ?></strong>
        escalated the ticket
    <?php else: ?>
            <strong><?= htmlspecialchars($log['fullname']) ?></strong>
        updated <b><?= htmlspecialchars($log['field_name']) ?></b>
        from <span class="text-danger"><?= htmlspecialchars($log['old_value']) ?></span>
        to <span class="text-success"><?= htmlspecialchars($log['new_value']) ?></span>
    <?php endif; ?>

    <?php if (!empty($log['comment'])): ?>
        <div class="mt-2 p-2 rounded <?= $log['is_public'] ? 'bg-light' : 'bg-warning-subtle border border-warning' ?>">
            <small class="fw-bold"><?= $log['is_public'] ? 'Comment:' : 'Internal Note:' ?></small><br>
            <?= nl2br(htmlspecialchars($log['comment'])) ?>
        </div>
    <?php endif; ?>
   

</div>

<?php endwhile; ?>
</div>

</div>
</div>
</div>
<!-- RIGHT -->
<div class="col-md-4">

<label class="fw-bold">Assignee</label>
<div class="mb-3" id="assigneeContainer">
<?php if ($ticket['assigned_to'] == 1 && $canAssignTicket): ?>


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
<?php foreach(['low','medium','high', 'highest'] as $p): ?>
<option value="<?= $p ?>" <?= $ticket['priority']===$p?'selected':'' ?>><?= ucfirst($p) ?></option>
<?php endforeach; ?>
</select>

<label class="fw-bold">Urgency</label>
<select class="form-select mb-3 live-update" data-field="urgency">
<?php foreach(['low','medium','high' ,'critical'] as $u): ?>
<option value="<?= $u ?>" <?= ($ticket['urgency']??'medium')===$u?'selected':'' ?>><?= ucfirst($u) ?></option>
<?php endforeach; ?>
</select>

<label class="fw-bold">Impact</label>
<select class="form-select mb-3 live-update" data-field="impact">
<?php foreach(['individual','department','organization','extensive'] as $i): ?>
<option value="<?= $i ?>" <?= ($ticket['impact']??'moderate')===$i?'selected':'' ?>><?= ucfirst($i) ?></option>
<?php endforeach; ?>
</select>

<!-- <label class="fw-bold">Pending Reason</label>
<select class="form-select live-update" data-field="pending_reason">
<?php foreach(['None','Waiting for user','Waiting for vendor','Internal review'] as $r): ?>
<option value="<?= $r ?>" <?= ($ticket['pending_reason']??'None')===$r?'selected':'' ?>><?= $r ?></option>
<?php endforeach; ?>
</select> -->


    <a href="../request/add_request.php?ticket_id=<?= $ticket_id ?>" class="btn btn-primary">
        Create LMR
    </a>

</div>
</div>
</div>
</div>

</div>
</div>
</div>
 <?php include 'image_modal.php'?>
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
    <input class="form-check-input" type="checkbox" id="publicComment">
    <label class="form-check-label" >Visible to ticket sender</label>
    </div>
    </div>

    <div class="modal-footer">
    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
    <button class="btn btn-primary" id="confirmStatusChange">Confirm</button>
    </div>

    </div>
    </div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const canEditStatus = <?= $canEditStatus ? 'true' : 'false' ?>;
    const statusFlow = <?= json_encode($statusFlow) ?>;

    let previousStatusValue = document.getElementById('statusSelect')?.value || null;
    let pendingStatusSelect = null;

    /* Live update fields */
    document.querySelectorAll('.live-update').forEach(el => {

        if (!canEditStatus) {
            el.disabled = true;
        }

        el.addEventListener('change', () => {

            if (!canEditStatus) return;

            /* ===== STATUS FIELD (WITH MODAL REQUIRED) ===== */
            if (el.dataset.field === 'status') {

                pendingStatusSelect = el;
                document.getElementById('newStatusValue').value = el.value;

                const modal = new bootstrap.Modal(document.getElementById('statusModal'));
                modal.show();

                return; // stop default update
            }

            /* ===== OTHER FIELDS (NORMAL UPDATE) ===== */
            // fetch('update_ticket_field.php', {
            //     method: 'POST',
            //     headers: {'Content-Type': 'application/json'},
            //     body: JSON.stringify({
            //         ticket_id: <?= $ticket_id ?>,
            //         field: el.dataset.field,
            //         value: el.value
            //     })
            // });
            fetch('update_ticket_field.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            ticket_id: <?= $ticket_id ?>,
            field: el.dataset.field,
            value: el.value
        })
    })
    .then(() => reloadActivityLog());

        });
    });

    /* Assign to me */ const assignBtn = document.getElementById('assignToMeBtn'); if (assignBtn) { assignBtn.addEventListener('click', () => { fetch('assign_ticket.php', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({ ticket_id: <?= $ticket_id ?> }) }) .then(res => res.json()) .then(data => { if (data.success) { document.getElementById('assigneeContainer').innerHTML = data.assignee_name; reloadActivityLog(); document.querySelectorAll('.live-update').forEach(el => { el.disabled = false; }); } }); }); }

    /* ===== CONFIRM STATUS CHANGE ===== */
    document.getElementById('confirmStatusChange').addEventListener('click', () => {

        const newStatus = document.getElementById('newStatusValue').value;
        const comment   = document.getElementById('statusComment').value.trim();
        const isPublic  = document.getElementById('publicComment').checked ? 1 : 0;

        if (!comment) {
            alert("Comment is required.");
            return;
        }

        fetch('update_ticket_field.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                ticket_id: <?= $ticket_id ?>,
                field: 'status',
                value: newStatus,
                comment: comment,
                is_public: isPublic
            })
        })
        .then(res => res.json())
        .then(data => {

            if (data.success) {

                reloadActivityLog();
                const select = document.getElementById('statusSelect');
                select.innerHTML = '';

                statusFlow[newStatus].forEach(s => {
                    const opt = document.createElement('option');
                    opt.value = s;
                    opt.textContent = s.charAt(0).toUpperCase() + s.slice(1);
                    if (s === newStatus) opt.selected = true;
                    select.appendChild(opt);
                });

                previousStatusValue = newStatus;

                document.getElementById('statusComment').value = '';

                bootstrap.Modal.getInstance(
                    document.getElementById('statusModal')
                ).hide();
            }
        });
    });


    /* If modal closed without confirm → revert dropdown */
    document.getElementById('statusModal').addEventListener('hidden.bs.modal', () => {
        const select = document.getElementById('statusSelect');
        if (previousStatusValue && select.value !== previousStatusValue) {
            select.value = previousStatusValue;
        }
    });


    function reloadActivityLog() {
        fetch('fetch_ticket_logs.php?ticket_id=<?= $ticket_id ?>')
            .then(res => res.text())
            .then(html => {
                document.getElementById('activityLog').innerHTML = html;
            });
    }
</script>
<script>

    /* ===== CHAT SEND FUNCTION ===== */
    const chatForm = document.getElementById('chatForm');
    const chatBox = document.querySelector('chatBox');
    // const chatBox = document.getElementById('chatBox');

    // const chatBox = document.querySelector('#chatBox');
    function scrollChatToBottom() {
        chatBox.scrollTop = chatBox.scrollHeight;
    }

    // Auto-refresh chat every 5 seconds
    setInterval(() => {
        fetch('fetch_ticket_chat.php?ticket_id=<?= $ticket_id ?>')
            .then(res => res.text())
            .then(html => {
                chatBox.innerHTML = html;
                scrollChatToBottom();
            });
    }, 5000);

    chatForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const message = chatForm.querySelector('textarea[name="chatMessage"]').value.trim();
        if (!message) return;

        fetch('send_message.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ ticket_id: <?= $ticket_id ?>, message })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Reload the chat completely after saving
                reloadChat();

                // Clear textarea
                chatForm.querySelector('textarea[name="chatMessage"]').value = '';
            } else {
                alert('Failed to send message');
            }
        });
    });


    /* ===== AUTO-REFRESH CHAT ===== */
    function reloadChat() {
        fetch('fetch_ticket_chat.php?ticket_id=<?= $ticket_id ?>')
            .then(res => res.text())
            .then(html => {
                chatBox.scrollTop = chatBox.scrollHeight;
                 chatBox.innerHTML = html;
            });
    }

    // attachment
    document.addEventListener("DOMContentLoaded", function () {

    document.querySelectorAll('.preview-file').forEach(btn => {

        btn.addEventListener('click', function () {

            const file = this.dataset.file;
            const name = this.dataset.name;

            const modal = new bootstrap.Modal(document.getElementById('attachmentModal'));

            document.getElementById('attachmentTitle').textContent = name;

            const img = document.getElementById('previewImage');
            const frame = document.getElementById('previewFrame');
            const fallback = document.getElementById('previewFallback');

            img.classList.add('d-none');
            frame.classList.add('d-none');
            fallback.classList.add('d-none');

            img.src = "";
            frame.src = "";

            const ext = file.split('.').pop().toLowerCase();

            if (['jpg','jpeg','png','gif','webp'].includes(ext)) {

                img.src = file;
                img.classList.remove('d-none');

            } else if (['pdf','txt'].includes(ext)) {

                frame.src = file;
                frame.classList.remove('d-none');

            } else {

                document.getElementById('downloadFallback').href = file;
                fallback.classList.remove('d-none');

            }

            modal.show();

        });

    });

});

</script>

</body>
</html>
<?php $conn->close(); ?>
