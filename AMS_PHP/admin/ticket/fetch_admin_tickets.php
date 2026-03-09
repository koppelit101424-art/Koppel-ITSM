<?php
include '../auth/auth.php';
include '../db/db.php';

$sql = "
SELECT 
    t.*,
    u.fullname AS requested_by
FROM ticket_tb t
LEFT JOIN user_tb u 
    ON t.user_id = u.user_id
ORDER BY t.ticket_id DESC
";

$result = $conn->query($sql);

if ($result->num_rows > 0):
while ($ticket = $result->fetch_assoc()):
?>

<tr class="ticket-row" data-ticket-id="<?= $ticket['ticket_id'] ?>">
    <td><?= htmlspecialchars($ticket['ticket_number']) ?></td>

    <!-- PRIORITY -->
    <td>
        <span class="badge badge-priority-<?= strtolower($ticket['priority'] ?? 'medium') ?>">
            <?= ucfirst($ticket['priority'] ?? 'Medium') ?>
        </span>
    </td>

    <td><?= ucfirst($ticket['ticket_category']) ?></td>
    <td><?= htmlspecialchars($ticket['subject']) ?></td>

    <td><?= htmlspecialchars($ticket['assigned_admin'] ?? 'Unassigned') ?></td>

    <!-- STATUS -->
    <td>
        <span class="badge badge-status-<?= strtolower(str_replace(' ', '-', $ticket['status'])) ?>">
            <?= ucfirst($ticket['status']) ?>
        </span>
    </td>

    <td><?= date('m-d-Y', strtotime($ticket['created_at'] ?? $ticket['date_created'])) ?></td>
</tr>

<?php endwhile; else: ?>

<tr>
    <td colspan="7" class="text-center">No tickets found.</td>
</tr>

<?php endif; ?>