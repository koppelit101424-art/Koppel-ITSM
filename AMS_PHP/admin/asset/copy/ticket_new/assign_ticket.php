<?php
include '../auth/auth.php';
include '../db/db.php';

$data = json_decode(file_get_contents("php://input"), true);

$ticket_id = $data['ticket_id'];
$admin_id  = $_SESSION['user_id'];

/* Get old assignee */
$stmt = $conn->prepare("SELECT assigned_to FROM ticket_tb WHERE ticket_id = ?");
$stmt->bind_param("i", $ticket_id);
$stmt->execute();
$old = $stmt->get_result()->fetch_assoc()['assigned_to'];

/* Assign */
$stmt = $conn->prepare("UPDATE ticket_tb SET assigned_to = ? WHERE ticket_id = ?");
$stmt->bind_param("ii", $admin_id, $ticket_id);
$stmt->execute();

/* Log assignment */
$stmt = $conn->prepare("
    INSERT INTO ticket_logs
    (ticket_id, changed_by, action_type, field_name, old_value, new_value)
    VALUES (?, ?, 'assign', 'assigned_to', ?, ?)
");

$stmt->bind_param(
    "iiss",
    $ticket_id,
    $admin_id,
    $old,
    $admin_id
);

$stmt->execute();

/* Get name */
$stmt = $conn->prepare("SELECT fullname FROM user_tb WHERE user_id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$name = $stmt->get_result()->fetch_assoc()['fullname'];

echo json_encode([
    "success" => true,
    "assignee_name" => $name
]);
