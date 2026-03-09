<?php
include '../auth/auth.php';
include '../db/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

/* =========================
   BASIC INPUT
========================= */

$ticket_id  = isset($_POST['ticket_id']) ? (int) $_POST['ticket_id'] : 0;
$status     = strtolower(trim($_POST['status'] ?? ''));
$changed_by = $_SESSION['user_id'] ?? 0;

if (!$ticket_id || !$status || !$changed_by) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

/* =========================
   GET USER ROLE
========================= */

$stmtUser = $conn->prepare("SELECT user_type FROM user_tb WHERE user_id = ?");
$stmtUser->bind_param("i", $changed_by);
$stmtUser->execute();
$userResult = $stmtUser->get_result();

if ($userResult->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit;
}

$userData  = $userResult->fetch_assoc();
$user_type = $userData['user_type'];
$stmtUser->close();

/* =========================
   ROLE-BASED ALLOWED STATUS
========================= */

if ($user_type === 'admin') {

    $allowedStatuses = [
        'waiting for support',
        'waiting for customer',
        'in progress',
        'escalated',
        'pending',
        'canceled',
        'resolved',
        'reopened',
        'closed'
    ];

} else {

    // Normal user can ONLY close ticket
    $allowedStatuses = ['closed'];
}

if (!in_array($status, $allowedStatuses)) {
    echo json_encode([
        'success' => false,
        'message' => 'You are not allowed to set this status'
    ]);
    exit;
}

/* =========================
   FETCH CURRENT TICKET
========================= */

$stmt = $conn->prepare("SELECT status, user_id FROM ticket_tb WHERE ticket_id = ?");
$stmt->bind_param("i", $ticket_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Ticket not found']);
    exit;
}

$ticket     = $result->fetch_assoc();
$old_status = strtolower($ticket['status']);
$owner_id   = $ticket['user_id'];
$stmt->close();

/* =========================
   SECURITY CHECKS
========================= */

// Prevent update if already closed
if ($old_status === 'closed') {
    echo json_encode(['success' => false, 'message' => 'Ticket is already closed']);
    exit;
}

// If normal user, ensure they own the ticket
if ($user_type !== 'admin' && $owner_id != $changed_by) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized action']);
    exit;
}

// If no actual change
if ($old_status === $status) {
    echo json_encode(['success' => true, 'message' => 'No changes made']);
    exit;
}

/* =========================
   UPDATE STATUS
========================= */

$stmt = $conn->prepare("
    UPDATE ticket_tb 
    SET status = ?, date_updated = NOW() 
    WHERE ticket_id = ?
");

$stmt->bind_param("si", $status, $ticket_id);

if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'message' => 'Failed to update ticket']);
    exit;
}

$stmt->close();

/* =========================
   INSERT ACTIVITY LOG
========================= */

$stmt = $conn->prepare("
    INSERT INTO ticket_logs 
    (ticket_id, changed_by, action_type, field_name, old_value, new_value, created_at, is_public) 
    VALUES (?, ?, 'update', 'status', ?, ?, NOW(), 1)
");

$stmt->bind_param("iiss", $ticket_id, $changed_by, $old_status, $status);

if (!$stmt->execute()) {
    echo json_encode([
        'success' => false,
        'message' => 'Status updated but log failed'
    ]);
    exit;
}

$stmt->close();

/* =========================
   SUCCESS RESPONSE
========================= */

echo json_encode([
    'success' => true,
    'message' => 'Ticket status updated successfully'
]);

$conn->close();