<?php
include '../auth/auth.php';
include '../db/db.php';

$data = json_decode(file_get_contents("php://input"), true);

$ticket_id = $data['ticket_id'];
$admin_id  = $_SESSION['user_id'];

/* ================================
   GET ADMIN NAME
================================ */
$q = $conn->prepare("
    SELECT fullname 
    FROM user_tb 
    WHERE user_id = ?
");
$q->bind_param("i", $admin_id);
$q->execute();
$admin = $q->get_result()->fetch_assoc();

/* ================================
   GET OLD ASSIGNEE (FOR LOG)
================================ */
$oldQ = $conn->prepare("
    SELECT 
        u.fullname 
    FROM ticket_tb t
    LEFT JOIN user_tb u ON t.assigned_to = u.user_id
    WHERE t.ticket_id = ?
");
$oldQ->bind_param("i", $ticket_id);
$oldQ->execute();
$old = $oldQ->get_result()->fetch_assoc();

$old_assignee = $old['fullname'] ?? 'Unassigned';

/* ================================
   ASSIGN TICKET
================================ */
$stmt = $conn->prepare("
    UPDATE ticket_tb 
    SET assigned_to = ?
    WHERE ticket_id = ?
");
$stmt->bind_param("ii", $admin_id, $ticket_id);
$stmt->execute();

/* ================================
   INSERT ACTIVITY LOG  ✅ FIX
================================ */
$log = $conn->prepare("
    INSERT INTO ticket_logs
    (ticket_id, action_type, field_name, old_value, new_value, changed_by, is_public)
    VALUES (?, 'assign', 'assigned_to', ?, ?, ?, 1)
");

$new_assignee = $admin['fullname'];

$log->bind_param(
    "issi",
    $ticket_id,
    $old_assignee,
    $new_assignee,
    $admin_id
);
$log->execute();

/* ================================
   RESPONSE (UNCHANGED)
================================ */
echo json_encode([
    'success' => true,
    'assignee_name' => htmlspecialchars($admin['fullname'])
]);
