<?php
include 'includes/auth.php';
include 'includes/db.php';

$currentUserId = $_SESSION['user_id'] ?? null;
$currentUserType = $_SESSION['user_type'] ?? 'user';

$ticket_id = $_GET['ticket_id'] ?? null;


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

?>
  
<!-- RIGHT COLUMN -->

<p><strong>Assignee:</strong> <?= htmlspecialchars($ticket['assignee'] ?? 'Unassigned') ?></p>
<p><strong>Sender:</strong> <?= htmlspecialchars($ticket['reporter']) ?></p>
<p><strong>Category:</strong> <?= ucfirst($ticket['ticket_category']) ?></p>
<p><strong>Status:</strong> <?= ucfirst($ticket['status']) ?></p>
<p><strong>Priority:</strong> <?= ucfirst($ticket['priority']) ?></p>
<p><strong>Urgency:</strong> <?= ucfirst($ticket['urgency'] ?? 'Medium') ?></p>
<p><strong>Impact:</strong> <?= ucfirst($ticket['impact'] ?? 'Moderate') ?></p>
