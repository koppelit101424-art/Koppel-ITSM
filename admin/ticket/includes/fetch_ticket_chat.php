<?php
include __DIR__ . '/../../../includes/auth.php';
include __DIR__ . '/../../../includes/db.php';

$ticket_id = $_GET['ticket_id'] ?? 0;

$stmt = $conn->prepare("SELECT * FROM ticket_messages WHERE ticket_id = ? ORDER BY created_at ASC");
$stmt->bind_param("i", $ticket_id);
$stmt->execute();
$messages = $stmt->get_result();

while($msg = $messages->fetch_assoc()): ?>
 <div class="chat-msg <?= ($msg['sender_role'] === 'user') ? 'chat-admin' : 'chat-user' ?>">
    
    <div class="bubble">
        <?= nl2br(htmlspecialchars($msg['message'])) ?>

        <div class="chat-time">
            <?= date("M d, Y h:i A", strtotime($msg['created_at'])) ?>
        </div>
    </div>

</div>
<?php endwhile; ?>