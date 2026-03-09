<?php
include '../auth/auth.php';
include '../db/db.php';

$ticket_id = $_GET['ticket_id'] ?? null;
if (!$ticket_id) exit;

$role = $_SESSION['user_type'];

if ($role === 'admin') {
    $sql = "
        SELECT l.*, u.fullname
        FROM ticket_logs l
        JOIN user_tb u ON l.changed_by = u.user_id
        WHERE l.ticket_id = ?
        ORDER BY l.created_at DESC
    ";
} else {
    $sql = "
        SELECT l.*, u.fullname
        FROM ticket_logs l
        JOIN user_tb u ON l.changed_by = u.user_id
        WHERE l.ticket_id = ?
        AND l.is_public = 1
        ORDER BY l.created_at DESC
    ";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $ticket_id);
$stmt->execute();
$logs = $stmt->get_result();

while ($log = $logs->fetch_assoc()):
?>
<div class="mb-3 p-2 border rounded bg-white">

    <small class="text-muted">
        <?= date("M d, Y h:i A", strtotime($log['created_at'])) ?>
    </small><br>



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
