<?php
include '../auth/auth.php';  // starts session + validates login
include '../db/db.php';

$ticket_id = $_POST['ticket_id'] ?? null;
$admin_id  = $_POST['admin_id'] ?? null;
$changed_by = $_SESSION['user_id'] ?? 0; // logged-in admin/user

if ($ticket_id) {

    // Fetch current assignment and status
    $current = $conn->query("SELECT assigned_to, status FROM ticket_tb WHERE ticket_id = $ticket_id")->fetch_assoc();
    $was_unassigned = empty($current['assigned_to']) || $current['assigned_to'] == 0;
    $old_assigned_id = $current['assigned_to'] ?? null;
    $old_status      = $current['status'] ?? '';

    // Get old assigned name
    if ($old_assigned_id) {
        $old_name = $conn->query("SELECT fullname FROM user_tb WHERE user_id = $old_assigned_id")->fetch_assoc()['fullname'];
    } else {
        $old_name = 'Unassigned';
    }

    // Determine new assigned and status
    if ($admin_id === "" || $admin_id == 0) {
        // Unassigned → status = 'waiting for support'
        $stmt = $conn->prepare("UPDATE ticket_tb SET assigned_to = NULL, status = 'waiting for support' WHERE ticket_id = ?");
        $stmt->bind_param("i", $ticket_id);
        $new_status = 'waiting for support';
        $new_name = 'Unassigned';
    } else {
        // Get new assigned name
        $new_name = $conn->query("SELECT fullname FROM user_tb WHERE user_id = $admin_id")->fetch_assoc()['fullname'];

        if ($was_unassigned) {
            // Previously unassigned → assign and mark as in progress
            $stmt = $conn->prepare("UPDATE ticket_tb SET assigned_to = ?, status = 'in progress' WHERE ticket_id = ?");
            $stmt->bind_param("ii", $admin_id, $ticket_id);
            $new_status = 'in progress';
        } else {
            // Already assigned → just update the admin
            $stmt = $conn->prepare("UPDATE ticket_tb SET assigned_to = ? WHERE ticket_id = ?");
            $stmt->bind_param("ii", $admin_id, $ticket_id);
            $new_status = $old_status; // status unchanged
        }
    }

    $stmt->execute();
    $stmt->close();

    // Log assignment change (if changed)
// Log assignment change (if changed)
if ($old_name != $new_name) {
    $action_text = "$old_name transferred the ticket to $new_name";

    $stmt = $conn->prepare("INSERT INTO ticket_logs 
        (ticket_id, changed_by, action_type, field_name, old_value, new_value, comment, created_at, is_public) 
        VALUES (?, ?, 'update', 'assigned_to', ?, ?, ?, NOW(), 1)");
    $stmt->bind_param("iisss", $ticket_id, $changed_by, $old_name, $new_name, $action_text);
    $stmt->execute();
    $stmt->close();
}
    // Log status change (if changed)
    if ($old_status != $new_status) {
        $stmt = $conn->prepare("INSERT INTO ticket_logs 
            (ticket_id, changed_by, action_type, field_name, old_value, new_value, created_at, is_public) 
            VALUES (?, ?, 'update', 'status', ?, ?, NOW(), 1)");
        $stmt->bind_param("iiss", $ticket_id, $changed_by, $old_status, $new_status);
        $stmt->execute();
        $stmt->close();
    }

}

$conn->close();