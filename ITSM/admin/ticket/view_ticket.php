<?php
    ini_set('display_errors', 1);
    error_reporting(E_ALL);

    include __DIR__ . '/../../includes/auth.php';
    include __DIR__ . '/../../includes/db.php';

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

/* --- Fetch dynamic SLA hours based on ticket priority --- */
    $slaStmt = $conn->prepare("
        SELECT resolution_minutes 
        FROM sla_settings 
        WHERE priority = ?
        LIMIT 1
    ");
    $slaStmt->bind_param("s", $ticket['priority']);
    $slaStmt->execute();
    $slaResult = $slaStmt->get_result();
    $slaRow = $slaResult->fetch_assoc();

    // Fetch business hours (assume only 1 row for simplicity)
    $bhStmt = $conn->prepare("SELECT start_time, end_time FROM business_hours LIMIT 1");
    $bhStmt->execute();
    $bhResult = $bhStmt->get_result();
    $bhRow = $bhResult->fetch_assoc();

    $businessStart = $bhRow['start_time'] ?? '07:30:00';
    $businessEnd   = $bhRow['end_time'] ?? '18:00:00';

    // Convert minutes to hours
    $slaHours = isset($slaRow['resolution_minutes']) ? ($slaRow['resolution_minutes'] / 60) : 48;
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
    

    $ticketClosedTime = null;

    if ($ticket['status'] === 'closed') {
        $logStmt = $conn->prepare("
            SELECT created_at 
            FROM ticket_logs 
            WHERE ticket_id = ? 
            AND field_name = 'status' 
            AND new_value = 'closed'
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        $logStmt->bind_param("i", $ticket_id);
        $logStmt->execute();
        $logResult = $logStmt->get_result();
        if ($row = $logResult->fetch_assoc()) {
            $ticketClosedTime = new DateTime($row['created_at']);
        }
    }

    date_default_timezone_set('Asia/Manila');

    $ticketCreated = new DateTime($ticket['date_created']);
    $deadline = clone $ticketCreated;
    $deadline->modify("+{$slaHours} hours");
    

    // Use closed time if ticket is closed
    $now = $ticketClosedTime ?? new DateTime();

    $remainingSeconds = $deadline->getTimestamp() - $now->getTimestamp();

    if ($remainingSeconds > 0) {
        $hours = floor($remainingSeconds / 3600);
        $minutes = floor(($remainingSeconds % 3600) / 60);
        $seconds = $remainingSeconds % 60;
        $remainingHMS = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
        $slaStatus = '';
    } else {
        $remainingHMS = "00:00:00";
        $slaStatus = '';
    }
// HOLIDAYS
    $holidayDates = [];

    $holidayStmt = $conn->prepare("SELECT holiday_date FROM holidays");
    $holidayStmt->execute();
    $holidayResult = $holidayStmt->get_result();

    while ($row = $holidayResult->fetch_assoc()) {
        $holidayDates[] = $row['holiday_date'];
    }
?>
<?php
$priorityColor = match(strtolower($ticket['priority'])) {
    'low' => 'bg-primary',
    'medium' => 'bg-warning',
    'high' => 'bg-danger',
    'highest' => 'bg-maroon',
    default => 'bg-secondary'
};
?>

<!-- $slaStatus = '<span class="badge bg-success">SLA Met</span>'; -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="bootstrap.bundle.min.js"></script>
<style>
 /* ===== CHAT CONTAINER ===== */
#chatBox{
    height:400px;
    overflow-y:auto;
    background:#f8f9fa !important;
    padding:15px;
    border-radius:8px;
    border:1px solid #dee2e6;
    scroll-behavior:smooth;
}

/* ===== MESSAGE ROW ===== */
.chat-msg{
    display:flex;
    margin-bottom:10px;
    width:100%;
}

/* ===== BUBBLE ===== */
.chat-msg .bubble{
    padding:10px 14px;
    border-radius:16px;
    word-wrap:break-word;
    font-size:0.9rem;
    max-width:65%;
    line-height:1.4;
}

/* ===== USER MESSAGE (RIGHT) ===== */
.chat-user{
    justify-content:flex-end;
}

.chat-user .bubble{
    background:#0d6efd !important;
    color:#fff !important;
    border-bottom-right-radius:4px;
}

/* ===== ADMIN MESSAGE (LEFT) ===== */
.chat-admin{
    justify-content:flex-start;
}

.chat-admin .bubble{
    background:#e9ecef !important;
    color:#000 !important;
    border-bottom-left-radius:4px;
}

/* ===== TIME ===== */
.chat-time{
    font-size:11px;
    color:#6c757d;
    margin-top:4px;
    text-align:right;
}

/* ===== CHAT INPUT ===== */
#chatForm{
    margin-top:10px;
}

#chatMessage{
    resize:none;
    border-radius:6px;
}

/* ===== SEND BUTTON ALIGNMENT ===== */
/* #chatForm .d-flex{
    align-items:flex-end;
} */

/* ===== SCROLLBAR ===== */
#chatBox::-webkit-scrollbar{
    width:6px;
}

#chatBox::-webkit-scrollbar-thumb{
    background:#cbd5e1;
    border-radius:10px;
}
.bg-maroon {
    background-color: #800000 !important; /* maroon */
}
.file-name-ellipsis {
    display: inline-block;
    max-width: 160px; /* adjust depending on your layout */
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    vertical-align: middle;
}
</style>
<style>
 /* From Uiverse.io by boryanakrasteva */ 
@-webkit-keyframes honeycomb {
  0%,
  20%,
  80%,
  100% {
    opacity: 0;
    -webkit-transform: scale(0);
    transform: scale(0);
  }

  30%,
  70% {
    opacity: 1;
    -webkit-transform: scale(1);
    transform: scale(1);
  }
}

@keyframes honeycomb {
  0%,
  20%,
  80%,
  100% {
    opacity: 0;
    -webkit-transform: scale(0);
    transform: scale(0);
  }

  30%,
  70% {
    opacity: 1;
    -webkit-transform: scale(1);
    transform: scale(1);
  }
}

.honeycomb {
  height: 24px;
  position: relative;
  width: 24px;
}

.honeycomb div {
  -webkit-animation: honeycomb 2.1s infinite backwards;
  animation: honeycomb 2.1s infinite backwards;
  background: #5c84f0;
  height: 12px;
  margin-top: 6px;
  position: absolute;
  width: 24px;
}

.honeycomb div:after, .honeycomb div:before {
  content: '';
  border-left: 12px solid transparent;
  border-right: 12px solid transparent;
  position: absolute;
  left: 0;
  right: 0;
}

.honeycomb div:after {
  top: -6px;
  border-bottom: 6px solid #5c84f0;
}

.honeycomb div:before {
  bottom: -6px;
  border-top: 6px solid #5c84f0;
}

.honeycomb div:nth-child(1) {
  -webkit-animation-delay: 0s;
  animation-delay: 0s;
  left: -28px;
  top: 0;
}

.honeycomb div:nth-child(2) {
  -webkit-animation-delay: 0.1s;
  animation-delay: 0.1s;
  left: -14px;
  top: 22px;
}

.honeycomb div:nth-child(3) {
  -webkit-animation-delay: 0.2s;
  animation-delay: 0.2s;
  left: 14px;
  top: 22px;
}

.honeycomb div:nth-child(4) {
  -webkit-animation-delay: 0.3s;
  animation-delay: 0.3s;
  left: 28px;
  top: 0;
}

.honeycomb div:nth-child(5) {
  -webkit-animation-delay: 0.4s;
  animation-delay: 0.4s;
  left: 14px;
  top: -22px;
}

.honeycomb div:nth-child(6) {
  -webkit-animation-delay: 0.5s;
  animation-delay: 0.5s;
  left: -14px;
  top: -22px;
}

.honeycomb div:nth-child(7) {
  -webkit-animation-delay: 0.6s;
  animation-delay: 0.6s;
  left: 0;
  top: 0;
}
#statusLoader {
    display: none;
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(255,255,255,0.3);
    z-index: 2000;
    justify-content: center;
    align-items: center;
}
</style>
<!-- HEADER -->
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between text-white <?= $priorityColor ?>">
        <!-- LEFT: Ticket Info -->
        <h5 class="mb-0">
        <?= htmlspecialchars($ticket['ticket_number']) ?> - <?= htmlspecialchars($ticket['subject']) ?> (<?= strtoupper($ticket['priority']) ?>)
        </h5>

        <!-- CENTER: Remaining Time + SLA Status -->
        <div class="d-flex align-items-center">
            <label class="fw-bold me-2 mb-0">Remaining Time:</label>
            <div id="remainingHours" data-deadline="<?= $deadline->format('Y-m-d H:i:s') ?>">
                <?= $remainingHMS ?>
            </div>
            <div class="ms-3">
                <?= $slaStatus ?>
            </div>
        </div>

        <!-- RIGHT: Back Button -->
        <a href="#" onclick="window.history.back(); return false;" class="btn btn-secondary btn-sm">
        Back
        </a>
    </div>

    <div class="card-body">
        <div class="row g-3">
            <!-- LEFT --> 
            <div class="col-md-4">
                <div class="row">
                    <div class="col">
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

                        <label class="fw-bold">Subject Details</label>
                        <div class="mb-3"><?= htmlspecialchars($ticket['subject_details']) ?></div>

                        <label class="fw-bold">Attachments</label>
                        <div class="mb-4">
                            <?php if ($attachments->num_rows > 0): ?>

                                <?php while($file = $attachments->fetch_assoc()): ?>

                                    <div class="d-flex align-items-center mb-2">

                                        <!-- Preview Button -->
                                    <button 
                                        class="btn btn-sm btn-outline-secondary me-2 preview-file"
                                        data-file="../<?= htmlspecialchars($file['file_path']) ?>"
                                        data-name="<?= htmlspecialchars($file['file_name']) ?>">
                                        <i class="fa-solid fa-eye"></i>
                                            <span class="file-name-ellipsis">
                                                <?= htmlspecialchars($file['file_name']) ?>
                                            </span>
                                    </button>

                                        <!-- Download Button -->
                                        <a href="../<?= htmlspecialchars($file['file_path']) ?>" 
                                        download
                                        class="btn btn-sm btn-outline-primary"><i class="fa-regular fa-download"></i>
                                        </a>

                                    </div>

                                <?php endwhile; ?>

                            <?php else: ?>

                                <span class="text-muted">No attachment</span>

                            <?php endif; ?>

                        </div>
                        <?php if (!empty($ticket['ticket_category']) && strtolower($ticket['ticket_category']) === 'material'): ?>
                            <a href="?page=ticket/crud/add_request&ticket_id=<?= $ticket_id ?>" class="btn btn-primary">
                                Create LMR
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="col">
                    <label class="fw-bold">Category</label>
                    <select class="form-select mb-3 live-update" data-field="ticket_category">
                    <?php foreach(['incident','service','change', 'material' ] as $c): ?>
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
                    </div>
                </div>
                    <label class="fw-bold">Issue Description</label>
                    <div class="mb-3"><?= nl2br(htmlspecialchars($ticket['issue'])) ?></div>
            </div>
            <!-- RIGHT -->
            <div class="col-md-8">
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
                    <form id="chatForm" action="?ajax=send_message">
                        <div class="d-flex gap-2">
                            <textarea 
                            id="chatMessage"
                            name="chatMessage"
                            class="form-control"
                            rows="3"
                            placeholder="Type a message..."
                            required
                            ></textarea>

                            <button class="btn btn-primary">Send</button>`
                        </div>
                    </form>
                </div>  

                <!-- ===== ACTIVITY TAB ===== -->
                <div class="tab-pane show active" id="activityTab">
                    <div id="activityLog" style="height:400px; overflow-y:auto;">     
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
                                <?php 
                                $role = $_SESSION['user_type'];
                                if ($log['is_public'] || $role === 'admin'): 
                                ?> 
                                <?php if ($log['field_name'] !== 'comment_only'): ?>
                                        <strong><?= htmlspecialchars($log['fullname']) ?></strong>
                                        updated <b><?= htmlspecialchars($log['field_name']) ?></b>
                                
                                        from <span class="text-danger"><?= htmlspecialchars($log['old_value']) ?></span>
                                        to <span class="text-success"><?= htmlspecialchars($log['new_value']) ?></span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php if (!empty($log['comment'])): ?>
                                <div class="mt-2 p-2 rounded <?= $log['is_public'] ? 'bg-light' : 'bg-warning-subtle border border-warning' ?>">
                                    <small class="fw-bold"><?= $log['is_public'] ? 'Comment:' : 'Internal Note:' ?></small><br>
                                    <?= nl2br(htmlspecialchars($log['comment'])) ?>
                                </div>
                            <?php endif; ?>
                        </div><?php endwhile; ?>
                    </div> 
                            <div class="d-flex gap-2">
                                <textarea id="activityComment" class="form-control" rows="3"></textarea>
                                <button class="btn btn-primary" id="sendLogComment">Send</button>
                            </div>
                    </div>          
                    
                </div>
            </div>

            </div>
        </div>
    </div>
</div>
<?php include 'includes/image_modal.php'?>

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
    </div></div>
 </div>
 <!-- laoder -->
  <!-- STATUS LOADER -->
<div id="statusLoader">
    <div class="honeycomb">
        <div></div><div></div><div></div>
        <div></div><div></div><div></div>
        <div></div>
    </div>
</div>

<!-- EDIT STATUS -->
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
            const loader = document.getElementById('statusLoader');
            if (!canEditStatus) return;

            /* ===== STATUS FIELD (WITH MODAL REQUIRED) ===== */
            if (el.dataset.field === 'status') {

                pendingStatusSelect = el;
                document.getElementById('newStatusValue').value = el.value;

                const modal = new bootstrap.Modal(document.getElementById('statusModal'));
                modal.show();

                return; // stop default update
            }


        fetch('?page=ticket/includes/update_ticket_field', {
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
    // Trigger Confirm on Enter inside modal
    document.getElementById('statusModal').addEventListener('keydown', function (e) {
        // Check if Enter is pressed without Shift (Shift+Enter for new line)
        if (e.key === "Enter" && !e.shiftKey) {
            e.preventDefault(); // prevent new line
            document.getElementById('confirmStatusChange').click();
        }
    });
    /* Assign to me */ const assignBtn = document.getElementById('assignToMeBtn'); if (assignBtn) { assignBtn.addEventListener('click', () => { fetch('assign_ticket.php', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({ ticket_id: <?= $ticket_id ?> }) }) .then(res => res.json()) .then(data => { if (data.success) { document.getElementById('assigneeContainer').innerHTML = data.assignee_name; reloadActivityLog(); document.querySelectorAll('.live-update').forEach(el => { el.disabled = false; }); } }); }); }

    /* ===== CONFIRM STATUS CHANGE ===== */
    document.getElementById('confirmStatusChange').addEventListener('click', () => {
        location.reload();
        const loader = document.getElementById('statusLoader');
        const newStatus = document.getElementById('newStatusValue').value;
        const comment   = document.getElementById('statusComment').value.trim();
        const isPublic  = document.getElementById('publicComment').checked ? 1 : 0;

        if (!comment) {
            alert("Comment is required.");
            return;
        }
        // ✅ SHOW LOADER
        loader.style.display = 'flex';

        fetch('?page=ticket/includes/update_ticket_field', {
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
        })
        .finally(() => {

        // ✅ HIDE LOADER AFTER EVERYTHING
        loader.style.display = 'none';

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
        fetch('?ajax=fetch_ticket_logs&ticket_id=<?= $ticket_id ?>')
            .then(res => res.text())
            .then(html => {
                document.getElementById('activityLog').innerHTML = html;
            });
    }
</script>
<!-- SEND MESSAGE -->
<script>
    /* ===== ELEMENTS ===== */
    const chatForm = document.getElementById('chatForm');
    const chatBox = document.querySelector('#chatBox');
    const chatTextarea = document.getElementById('chatMessage');
    const activityLog = document.getElementById('activityLog');


    /* ===== SCROLL CHAT ===== */
    function scrollChatToBottom() {
        chatBox.scrollTop = chatBox.scrollHeight;
    }


    /* ===== INITIAL LOAD ===== */
    scrollChatToBottom();
    reloadChat();
    reloadActivityLogs();


    /* ===== SEND MESSAGE ===== */
    chatForm.addEventListener('submit', function(e) {

        e.preventDefault();

        const message = chatTextarea.value.trim();
        if (!message) return;

        fetch('?ajax=send_message', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                ticket_id: <?= $ticket_id ?>,
                message: message
            })
        })
        .then(res => res.json())
        .then(data => {

            if (data.success) {

                chatTextarea.value = "";

                reloadChat();

            } else {
                alert('Failed to send message');
            }

        })
        // .catch(() => alert('Error sending message'));

    });

    /* ===== RELOAD CHAT ===== */
    function reloadChat() {

        fetch('?ajax=fetch_ticket_chat&ticket_id=<?= $ticket_id ?>')
            .then(res => res.text())
            .then(html => {

                chatBox.innerHTML = html;

                scrollChatToBottom();

            });

    }


    /* ===== RELOAD ACTIVITY LOGS ===== */
    function reloadActivityLogs() {

        fetch('?ajax=fetch_ticket_logs&ticket_id=<?= $ticket_id ?>')
            .then(res => res.text())
            .then(html => {

                activityLog.innerHTML = html;

            });

    }
    /* ===== AUTO REFRESH ===== */
    setInterval(reloadChat, 2000);
    setInterval(reloadActivityLogs, 2000);
</script>
<!-- /* ===== SEND ON ENTER ===== */ -->
<script>
    chatTextarea.addEventListener('keydown', function(e) {

        if (e.key === "Enter" && !e.shiftKey) {
            e.preventDefault();
            chatForm.dispatchEvent(new Event('submit'));
        }

    });

    document.addEventListener("DOMContentLoaded", function () {

        const textarea = document.getElementById('activityComment');
        const button = document.getElementById('sendLogComment');

        if (!textarea || !button) return;

        textarea.addEventListener("keydown", function (e) {

            // ENTER (without Shift) = SEND
            if (e.key === "Enter" && !e.shiftKey) {
                e.preventDefault(); // stop newline
                button.click();     // trigger send button
            }

        });

    });
</script>
<!-- comment logs -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    const btn = document.getElementById('sendLogComment');
    if (!btn) return;

    btn.addEventListener('click', () => {
        const commentEl = document.getElementById('activityComment');
        const comment = commentEl.value.trim();
        if (!comment) {
            alert("Comment is required.");
            return;
        }

        fetch('?page=ticket/includes/update_ticket_field', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                ticket_id: <?= $ticket_id ?>,
                field: 'comment_only',
                comment: comment,
                is_public: 0   // INTERNAL ONLY
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                commentEl.value = "";   // ✅ CLEAR TEXTAREA
                commentEl.focus();      // (optional) keep cursor ready
                reloadActivityLogs();
            } else {
                alert('Failed to add comment');
            }
        });
    });
});
</script>
<!-- ATTACHMENT -->
<script>
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
<!-- TICKET  -->
<script>
    const holidays = <?= json_encode($holidayDates) ?>;
    const isClosed = "<?= $ticket['status'] ?>" === "closed";
    const businessStart = "<?= $businessStart ?>"; // e.g., "07:30:00"
    const businessEnd = "<?= $businessEnd ?>";     // e.g., "18:00:00"
    const remainingEl = document.getElementById('remainingHours');
    if (remainingEl) {
        const deadline = new Date(remainingEl.dataset.deadline);


    function updateRemaining() {
        const statusSelect = document.getElementById('statusSelect');
        const currentStatus = statusSelect ? statusSelect.value.toLowerCase() : '';

        const now = new Date();

        // Format today's date (YYYY-MM-DD)
        const todayStr = now.toISOString().split('T')[0];

        // Check if today is holiday
        const isHoliday = holidays.includes(todayStr);

        // Business hours check
        const [startH, startM, startS] = businessStart.split(':').map(Number);
        const [endH, endM, endS] = businessEnd.split(':').map(Number);

        const startTime = new Date(now);
        startTime.setHours(startH, startM, startS, 0);

        const endTime = new Date(now);
        endTime.setHours(endH, endM, endS, 0);

        const outsideBusinessHours = now < startTime || now > endTime;

        /* =========================
        ✅ PAUSE CONDITIONS
        ========================= */
        if (
            currentStatus === 'closed' ||   // ✅ CLOSED
            currentStatus === 'pending' ||  // existing
            outsideBusinessHours ||         // existing
            isHoliday                       // ✅ HOLIDAY
        ) {
            if (!remainingEl.dataset.isPaused) {
                remainingEl.dataset.paused = remainingEl.textContent;

                let label = " (Paused)";
                if (currentStatus === 'closed') label = " (Closed)";
                if (isHoliday) label = " (Holiday)";

                remainingEl.textContent = remainingEl.dataset.paused + label;
                remainingEl.dataset.isPaused = "true";
            }
            return;
        } else {
            if (remainingEl.dataset.isPaused) {
                remainingEl.dataset.isPaused = "";
            }
        }

        /* =========================
        ⏱ NORMAL COUNTDOWN
        ========================= */
        let diff = Math.floor((deadline - now) / 1000);

        if (diff <= 0) {
            remainingEl.textContent = "00:00:00 (SLA Exceeded)";

            // Auto-close ticket if not closed
            // if (statusSelect && statusSelect.value !== 'closed') {
            //     fetch('?page=ticket/includes/update_ticket_field', {
            //         method: 'POST',
            //         headers: {'Content-Type': 'application/json'},
            //         body: JSON.stringify({
            //             ticket_id: <?= $ticket_id ?>,
            //             field: 'status',
            //             value: 'closed',
            //             comment: 'Ticket Automatically Closed',
            //             is_public: 0
            //         })
            //     })
            //     .then(res => res.json())
            //     .then(data => {
            //         if (data.success) {
            //             statusSelect.value = 'closed';
            //             reloadActivityLog();
            //         }
            //     });
            // }
        } else {
            const hours = Math.floor(diff / 3600);
            const minutes = Math.floor((diff % 3600) / 60);
            const seconds = diff % 60;

            remainingEl.textContent = 
                String(hours).padStart(2, '0') + ":" +
                String(minutes).padStart(2, '0') + ":" +
                String(seconds).padStart(2, '0');

            remainingEl.dataset.paused = remainingEl.textContent;
        }
    }

    updateRemaining();
    setInterval(updateRemaining, 1000);
}
</script>
<?php $conn->close(); ?>
