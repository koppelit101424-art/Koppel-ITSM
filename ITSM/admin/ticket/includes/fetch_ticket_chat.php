<?php
session_start();
include __DIR__ . '/../../../includes/auth.php';
include __DIR__ . '/../../../includes/db.php';

$ticket_id = $_GET['ticket_id'] ?? 0;

$stmt = $conn->prepare("SELECT * FROM ticket_messages WHERE ticket_id = ? ORDER BY created_at ASC");
$stmt->bind_param("i", $ticket_id);
$stmt->execute();
$messages = $stmt->get_result();

while($msg = $messages->fetch_assoc()):
?>
<div class="chat-msg <?= $msg['sender_role'] === 'admin' ? 'chat-admin' : 'chat-user' ?>">
    <div><?= nl2br(htmlspecialchars($msg['message'])) ?></div>
</div>
<?php endwhile; ?>
