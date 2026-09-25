<?php
include 'includes/auth.php';
include 'includes/db.php';

$user_id = $_SESSION['user_id'];

$sql = "
SELECT 
    t.*,
    u.fullname AS assigned_admin,
    r.rating
FROM ticket_tb t
LEFT JOIN user_tb u 
    ON t.assigned_to = u.user_id 
    AND u.user_type = 'admin'
LEFT JOIN ticket_ratings r
    ON t.ticket_id = r.ticket_id
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

    <td><?= date('m-d-Y', strtotime($ticket['date_created'])) ?></td>
    <td><?= date('h:i A', strtotime($ticket['date_created'])) ?></td>
    <td>
        <?php if(!empty($ticket['rating'])): ?>
            <?php for($i=1; $i<=5; $i++): ?>
                <?php if($i <= $ticket['rating']): ?>
                    <i class="fa-solid fa-star text-warning"></i>
                <?php else: ?>
                    <i class="fa-regular fa-star text-muted"></i>
                <?php endif; ?>
            <?php endfor; ?>
        <?php else: ?>
            <span class="text-muted">Not Rated</span>
        <?php endif; ?>
    </td>
</tr>

<?php endwhile; else: ?>

<tr>
    <td colspan="7" class="text-center">No tickets created yet.</td>
</tr>

<?php endif; ?>