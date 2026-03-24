<?php
include __DIR__ . '/../../../includes/auth.php';
include __DIR__ . '/../../../includes/db.php';

$ticket_id = $_POST['ticket_id'] ?? null;
$status    = $_POST['status'] ?? null;
$changed_by = $_SESSION['user_id'] ?? 0;

if ($ticket_id && $status) {

    // Fetch old status safely
    $stmt = $conn->prepare("SELECT status FROM ticket_tb WHERE ticket_id = ?");
    $stmt->bind_param("i", $ticket_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $old = $result->fetch_assoc();
    $stmt->close();

    $old_status = $old['status'] ?? '';

    // Only continue if status actually changed
    if ($old_status !== $status) {

        // Update ticket
        $stmt = $conn->prepare("UPDATE ticket_tb SET status = ? WHERE ticket_id = ?");
        $stmt->bind_param("si", $status, $ticket_id);
        $stmt->execute();
        $stmt->close();

        // Insert log
        $stmt = $conn->prepare("INSERT INTO ticket_logs 
            (ticket_id, changed_by, action_type, field_name, old_value, new_value, created_at, is_public) 
            VALUES (?, ?, 'update', 'status', ?, ?, NOW(), 1)");
        $stmt->bind_param("iiss", $ticket_id, $changed_by, $old_status, $status);

        if (!$stmt->execute()) {
            die("Log Insert Error: " . $stmt->error);
        }

        $stmt->close();
    }
}

$conn->close();