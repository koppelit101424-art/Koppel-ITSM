<?php
include '../auth/auth.php';
include '../db/db.php';
$ticket_id = $_GET['ticket_id'] ?? 0;
$currentUserId = $_SESSION['user_id'] ?? 0;
$messages = $conn->query("SELECT * FROM ticket_messages WHERE ticket_id=$ticket_id ORDER BY created_at ASC");
while($msg = $messages->fetch_assoc()):
?>
<div class="chat-msg <?= ($msg['sender_id']==$currentUserId)?'chat-user':'chat-admin' ?>">
    <div class="bubble">
        <?= nl2br(htmlspecialchars($msg['message'])) ?>
        <div class="chat-time"><?= date("M d, Y h:i A", strtotime($msg['created_at'])) ?></div>
    </div>
</div>
<?php endwhile; ?>
