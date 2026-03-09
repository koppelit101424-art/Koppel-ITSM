<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../auth/auth.php';
include '../db/db.php';
include '../../config/config.php';

$currentUserId = $_SESSION['user_id'] ?? null;
$currentUserType = $_SESSION['user_type'] ?? 'user';

$ticket_id = $_GET['ticket_id'] ?? null;
if (!$ticket_id) {
    header("Location: tickets.php");
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
$canEditStatus = (
    $ticket['assigned_to'] == $currentUserId
    && !in_array($ticket['status'], $finalStatuses)
);

/* Fetch chat messages */
$msgSql = "SELECT * FROM ticket_messages WHERE ticket_id = ? ORDER BY created_at ASC";
$msgStmt = $conn->prepare($msgSql);
$msgStmt->bind_param("i", $ticket_id);
$msgStmt->execute();
$messages = $msgStmt->get_result();

/* Status workflow */
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

$currentStatus   = $ticket['status'];
$allowedStatuses = $statusFlow[$currentStatus] ?? [$currentStatus];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Tickets</title>
<link rel="icon" href="../asset/img/Koppel_bip.ico">
<!-- <title>Ticket <?= htmlspecialchars($ticket['ticket_number']) ?></title> -->
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="../asset/css/main.css" rel="stylesheet">
<link href="../asset/css/menu.css" rel="stylesheet">

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
p{
    font-size: large;
}
</style>

</head>
<body>

<div class="main-content d-flex">
<?php include '../sidebar.php'; ?>

<div class="content flex-grow-1">
<div class="card">
<div class="card-header d-flex justify-content-between align-items-center">
<h5><?= htmlspecialchars($ticket['ticket_number']) ?> - <?= htmlspecialchars($ticket['subject']) ?></h5>
<a href="tickets.php" class="btn btn-secondary btn-sm">Back</a>
</div>

<div class="card-body">
<div class="row g-3">

<!-- LEFT COLUMN -->
<div class="col-md-8">
<label class="fw-bold">Subject</label>
<div class="mb-3"><?= nl2br(htmlspecialchars($ticket['subject_details'])) ?></div>

<label class="fw-bold">Issue Description</label>
<div class="mb-4"><?= nl2br(htmlspecialchars($ticket['issue'])) ?></div>

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

<!-- Tabs -->
<ul class="nav nav-tabs mb-2">
    <li class="nav-item">
        <button class="nav-link " data-bs-toggle="tab" data-bs-target="#chatTab">Conversation</button>
    </li>
    <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#activityTab">Activity Log</button>
    </li>
</ul>

<div class="tab-content border border-top-0 rounded-bottom p-3 bg-light">

<!-- CHAT TAB -->
<div class="tab-pane fade" id="chatTab">
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
        <textarea id="chatMessage" name="chatMessage" class="form-control mb-2" rows="2" required placeholder="Type your message..."></textarea>
        <button class="btn btn-primary btn-sm">Send</button>
    </form>
</div>

<!-- ACTIVITY LOG TAB -->
<div class="tab-pane show active" id="activityTab">
    <div id="activityLog" style="max-height:400px; overflow-y:auto;">
        <?php
        $comment = '';

        $logStmt = $conn->prepare("SELECT l.*, u.fullname FROM ticket_logs l JOIN user_tb u ON l.changed_by=u.user_id WHERE l.ticket_id=? ORDER BY l.created_at  DESC");

        $logStmt->bind_param("i", $ticket_id);
        $logStmt->execute();
        $logs = $logStmt->get_result();
        if ($logs->num_rows === 0) echo '<div class="text-center text-muted">No Activity Log Available.</div>';
        while ($log = $logs->fetch_assoc()):
        ?>
        <div class="mb-3 p-2 border rounded bg-white">
            <small class="text-muted"><?= date("M d, Y h:i A", strtotime($log['created_at'])) ?></small><br>
            <?php if ($log['action_type']==='assign'): ?>
                Ticket assigned to <strong><?= htmlspecialchars($log['fullname']) ?></strong>
            <?php else: ?>
                <strong><?= htmlspecialchars($log['fullname']) ?></strong> updated <b><?= htmlspecialchars($log['field_name']) ?></b> from
                <span class="text-danger"><?= htmlspecialchars($log['old_value']) ?></span> to
                <span class="text-success"><?= htmlspecialchars($log['new_value']) ?></span>
            <?php endif; $comment = $log['comment'];?>
                <div class="mt-2 p-2 rounded <?= $log['is_public'] ? 'bg-light' : '' ?>">
                    <?php if ($log['is_public']): ?>
                         <?php if(!empty($log['comment']))  ?> 
                        
                       <?php echo $comment; ?>
                    <?php endif; ?>
                </div>
      
        </div>
        <?php endwhile; ?>
    </div>
</div>
</div>
</div>

<!-- RIGHT COLUMN -->
<div class="col-md-4" id="logDetails">
<p><strong>Assignee:</strong> <?= htmlspecialchars($ticket['assignee'] ?? 'Unassigned') ?></p>
<p><strong>Sender:</strong> <?= htmlspecialchars($ticket['reporter']) ?></p>
<p><strong>Category:</strong> <?= ucfirst($ticket['ticket_category']) ?></p>
<p><strong>Status:</strong> <?= ucfirst($ticket['status']) ?></p>
<p><strong>Priority:</strong> <?= ucfirst($ticket['priority']) ?></p>
<p><strong>Urgency:</strong> <?= ucfirst($ticket['urgency'] ?? 'Medium') ?></p>
<p><strong>Impact:</strong> <?= ucfirst($ticket['impact'] ?? 'Moderate') ?></p>
<?php if($ticket['status'] !== 'closed'): ?>
    <button id="closeTicketBtn" class="btn btn-secondary btn-sm mt-2 w-100">
        Close Ticket
    </button>
<?php endif; ?>
</div>

</div>
</div>
</div>
</div>
 <?php include 'image_modal.php'?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const chatForm = document.getElementById('chatForm');
    const chatBox = document.getElementById('chatBox');
    const activityLog = document.getElementById('activityLog');
    const logDetails = document.getElementById('logDetails');

    function scrollChatToBottom() {
        chatBox.scrollTop = chatBox.scrollHeight;
    }

    // Scroll on page load
    scrollChatToBottom();
    activityLogs();
    // Send chat message
    chatForm.addEventListener('submit', e => {
        e.preventDefault();
        const message = chatForm.querySelector('textarea[name="chatMessage"]').value.trim();
        if (!message) return;
        fetch('send_message.php', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({ ticket_id: <?= $ticket_id ?>, message })
        })
        .then(res=>res.json())
        .then(data=>{
            if(data.success){
                chatForm.querySelector('textarea[name="chatMessage"]').value='';
                reloadChat();
            }else{
                alert('Failed to send message');
            }
        });
    });

    // Reload chat
    function reloadChat(){
        fetch('fetch_ticket_chat.php?ticket_id=<?= $ticket_id ?>')
            .then(res=>res.text())
            .then(html=>{
                chatBox.innerHTML = html;
                scrollChatToBottom();
            });
    }

    // logDetails();

    function activityLogs(){
        fetch('fetch_ticket_logs.php?ticket_id=<?= $ticket_id ?>')
            .then(res=>res.text())
            .then(html=>{
             activityLog.innerHTML = html;
            });
    }
    // function logDetails(){
    //     fetch('fetch_log_details.php?ticket_id=<?= $ticket_id ?>')
    //         .then(res=>res.text())
    //         .then(html=>{
    //          logDetails.innerHTML = html;
    //         });
    // }

    // Auto-refresh every 2 seconds
    setInterval(activityLogs, 2000);
    // setInterval(logDetails, 2000);
    setInterval(reloadChat, 2000);
    fetch('update_ticket_status.php', {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded'},
    body: new URLSearchParams({
        ticket_id: <?= $ticket_id ?>,
        status: 'closed'
    })
})
.then(res => res.json())
.then(data => {
    if(data.success){
        alert(data.message);
        location.reload();
    } else {
        alert(data.message);
    }
});
const closeBtn = document.getElementById('closeTicketBtn');

if(closeBtn){
    closeBtn.addEventListener('click', function(){

        if(!confirm("Are you sure you want to close this ticket?")){
            return;
        }

        fetch('update_ticket_status.php', {
            method: 'POST',
            headers: {'Content-Type':'application/x-www-form-urlencoded'},
            body: new URLSearchParams({
                ticket_id: <?= $ticket_id ?>,
                status: 'closed'
            })
        })
        .then(res => res.text())
        .then(() => {
            alert("Ticket closed successfully.");
            location.reload();
        })
        .catch(() => {
            alert("Failed to close ticket.");
        });

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
