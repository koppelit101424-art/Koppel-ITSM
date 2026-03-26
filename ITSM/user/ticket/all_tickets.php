<?php
include 'includes/auth.php';
include 'includes/db.php';

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


<!-- REQUEST TYPES -->
<div class="row g-3 mb-2">

    <div class="col-md-4">
        <button class="btn w-100 p-3 text-start request-btn incident shadow-sm rounded"
            onclick="location.href='?page=ticket/includes/add&type=incident'">
            <h6 class="mb-1 fw-bold">
                <i class="fas fa-exclamation-circle me-2"></i> Incident Request
            </h6>
            <small class="fst-italic text-muted">
                (Ex. No internet, can’t dial outside call, can’t print, etc.)
            </small>
        </button>
    </div>

    <div class="col-md-4">
        <button class="btn w-100 p-3 text-start request-btn service shadow-sm rounded"
            onclick="location.href='?page=ticket/includes/add&type=service'">
            <h6 class="mb-1 fw-bold">
                <i class="fas fa-cogs me-2"></i> Service Request
            </h6>
            <small class="fst-italic text-muted">
                (Password reset, software install, access requests, etc.)
            </small>
        </button>
    </div>

    <div class="col-md-4">
        <button class="btn w-100 p-3 text-start request-btn change shadow-sm rounded"
            onclick="location.href='?page=ticket/includes/add&type=change'">
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
<div class="card">
<div class="card-header text-white">
    My Tickets
</div>

<div class="card-body table-responsive">
<table class="table table-hover" id="ticketsTable">
<thead>
<tr>
    <th>Ticket Number</th>
    <th>Category</th>
    <th>Impact</th>
    <th>Priority</th>
    <th>Subject</th>
    <th>Assigned To</th>
    <th style="width: 10%;">Status</th>
    <th>Date</th>
    <th>Time</th>
</tr>
</thead>
<tbody id="ticketsBody">

<?php if ($tickets->num_rows > 0): ?>
<?php while ($ticket = $tickets->fetch_assoc()): ?>
<tr class="ticket-row" data-ticket-id="<?= $ticket['ticket_id'] ?>">
    <td><?= htmlspecialchars($ticket['ticket_number']) ?></td>
    <td><?= ucfirst($ticket['ticket_category']) ?></td>
    <td><?= ucfirst($ticket['impact']) ?></td>
    <!-- PRIORITY -->
    <td>
        <span class="badge badge-priority-<?= strtolower($ticket['priority'] ?? 'medium') ?>">
            <?= ucfirst($ticket['priority'] ?? 'Medium') ?>
        </span>
    </td>


    <td><?= htmlspecialchars($ticket['subject']) ?></td>

    <td><?= htmlspecialchars($ticket['assigned_admin'] ?? 'Unassigned') ?></td>

    <!-- STATUS -->
    <td>
        <span class="badge badge-status-<?= strtolower(str_replace(' ', '-', $ticket['status'])) ?>" style="width: 100%;" >
            <?= ucfirst($ticket['status']) ?>
        </span>
    </td>

    <td><?= date('m-d-Y', strtotime($ticket['date_created'])) ?></td>
    <td><?= date('h:i A', strtotime($ticket['date_created'])) ?></td>
</tr>
<?php endwhile; ?>
<?php else: ?>

<?php endif; ?>

</tbody>
</table>
</div>
</div>
<!-- FLOATING BUTTON -->
<!-- <button id="chat-toggle">💬</button> -->

<!-- CHAT WINDOW -->
<div id="chat-container">
    <div id="chat-header">IT Support Bot</div>
    <div id="chatbox"></div>

    <div id="chat-input-area">
        <input type="text" id="chat-input" placeholder="Type a message...">
        <button id="chat-send">Send</button>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    $(document).ready(function() {
    // Initialize DataTable
    const table = $('#ticketsTable').DataTable({
        pageLength: 10,
        order: [[7, 'desc']], // Order by Date Created descending
    });
    });
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".ticket-row").forEach(row => {
        row.addEventListener("click", () => {
            const ticketId = row.dataset.ticketId;
            window.location.href = `?page=ticket/view&ticket_id=${ticketId}`;
        });
    });
});

</script>
<script>
function loadTickets() {
    fetch("?ajax=fetch_tickets")
        .then(response => response.text())
        .then(data => {
            document.getElementById("ticketsBody").innerHTML = data;

            // Re-attach click event after refresh
            document.querySelectorAll(".ticket-row").forEach(row => {
                row.addEventListener("click", () => {
                    const ticketId = row.dataset.ticketId;
                    window.location.href = `?page=ticket/view&ticket_id=${ticketId}`;
                });
            });
        });
}
// Load immediately
loadTickets();
// Refresh every 2 seconds
setInterval(loadTickets, 2000);
</script>

<script>
const chatbox = document.getElementById("chatbox");

function bot(msg){
    chatbox.innerHTML += `<div class="bot-msg">${msg}</div>`;
    chatbox.scrollTop = chatbox.scrollHeight;
}

function user(msg){
    chatbox.innerHTML += `<div class="user-msg">${msg}</div>`;
}

function sendMessage(){
    let input = document.getElementById("chat-input");
    let msg = input.value.trim();

    if(!msg) return;

    user(msg);
    input.value = "";

    bot("🤖 Analyzing your concern...");

    fetch("ai_ticket.php", {
        method: "POST",
        headers: {"Content-Type": "application/json"},
        body: JSON.stringify({ message: msg })
    })
    .then(res => res.json())
    .then(data => {
        if(data.success){
            bot(`✅ Ticket Created!<br>
                 Ticket No: ${data.ticket_number}<br>
                 <small>${data.detected}</small>`);
        }else{
            bot("❌ Failed to create ticket.");
        }
    });
}

document.getElementById("chat-send").onclick = sendMessage;
document.getElementById("chat-input").addEventListener("keypress", e => {
    if(e.key === "Enter") sendMessage();
});

bot("👋 Hi! Just type your concern and I’ll create a ticket.");

document.addEventListener("DOMContentLoaded", function(){

    document.getElementById("chat-toggle").onclick = function(){
        const chat = document.getElementById("chat-container");
        chat.style.display = chat.style.display === "flex" ? "none" : "flex";
    };

});
</script>