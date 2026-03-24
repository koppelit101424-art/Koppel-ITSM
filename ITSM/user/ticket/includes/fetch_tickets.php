<?php
include 'includes/auth.php';
include 'includes/db.php';

$user_id = $_SESSION['user_id'];

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

if ($tickets->num_rows > 0):
while ($ticket = $tickets->fetch_assoc()):
?>

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

    <td>
        <span class="badge badge-status-<?= strtolower(str_replace(' ', '-', $ticket['status'])) ?>" style="width: 100%;" >
            <?= ucfirst($ticket['status']) ?>
        </span>
    </td>

    <td><?= date('m-d-Y', strtotime($ticket['created_at'] ?? $ticket['date_created'])) ?></td>
</tr>

<?php endwhile; else: ?>

<tr>
    <td colspan="7" class="text-center">No tickets created yet.</td>
</tr>

<?php endif; ?>