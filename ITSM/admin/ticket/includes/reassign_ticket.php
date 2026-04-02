<?php
include __DIR__ . '/../../../includes/auth.php';
include __DIR__ . '/../../../includes/db.php';

$ticket_id = $_POST['ticket_id'] ?? null;
$admin_id  = $_POST['admin_id'] ?? null;
$changed_by = $_SESSION['user_id'] ?? 0;


/* =========================================
   EMAIL FUNCTION (DO NOT CHANGE STRUCTURE)
========================================= */
function sendTicketEmail($conn, $ticket_id, $field, $old_value, $new_value, $comment, $type) {

    $stmt = $conn->prepare("
        SELECT 
            t.ticket_number,
            t.subject,
            t.priority,
            t.ticket_category,
            u.fullname,
            u.email
        FROM ticket_tb t
        JOIN user_tb u ON t.user_id = u.user_id
        WHERE t.ticket_id = ?
    ");
    $stmt->bind_param("i", $ticket_id);
    $stmt->execute();
    $ticket = $stmt->get_result()->fetch_assoc();

    if (!$ticket) return;

    // REQUIRED VARIABLES for email file
    $ticket_number   = $ticket['ticket_number'];
    $subject         = $ticket['subject'];
    $priority        = $ticket['priority'];
    $ticket_category = $ticket['ticket_category'];
    $fullname        = $ticket['fullname'];
    $userEmail       = $ticket['email'];

    $email_type      = $type;
    $email_comment   = $comment;

    // ALSO PASS THESE (IMPORTANT FIX)
    $field      = $field;
    $old_value  = $old_value;
    $new_value  = $new_value;

    include __DIR__ . '/../crud/ticket_status_email.php';
}


/* =========================================
   MAIN LOGIC
========================================= */
if ($ticket_id) {

    // CURRENT DATA
    $current = $conn->query("SELECT assigned_to, status FROM ticket_tb WHERE ticket_id = $ticket_id")->fetch_assoc();

    $was_unassigned = empty($current['assigned_to']) || $current['assigned_to'] == 0;
    $old_assigned_id = $current['assigned_to'] ?? null;
    $old_status      = $current['status'] ?? '';

    // OLD NAME
    if ($old_assigned_id) {
        $old_name = $conn->query("SELECT fullname FROM user_tb WHERE user_id = $old_assigned_id")->fetch_assoc()['fullname'];
    } else {
        $old_name = 'Unassigned';
    }

    /* =========================================
       DETERMINE NEW ASSIGNMENT
    ========================================= */
    if ($admin_id === "" || $admin_id == 0) {

        // UNASSIGN
        $stmt = $conn->prepare("
            UPDATE ticket_tb 
            SET assigned_to = NULL, status = 'waiting for support' 
            WHERE ticket_id = ?
        ");
        $stmt->bind_param("i", $ticket_id);

        $new_status = 'waiting for support';
        $new_name   = 'Unassigned';

    } else {

        // GET NEW NAME
        $new_name = $conn->query("SELECT fullname FROM user_tb WHERE user_id = $admin_id")->fetch_assoc()['fullname'];

        if ($was_unassigned) {
            // ASSIGN FIRST TIME
            $stmt = $conn->prepare("
                UPDATE ticket_tb 
                SET assigned_to = ?, status = 'in progress' 
                WHERE ticket_id = ?
            ");
            $stmt->bind_param("ii", $admin_id, $ticket_id);

            $new_status = 'in progress';

        } else {
            // REASSIGN ONLY
            $stmt = $conn->prepare("
                UPDATE ticket_tb 
                SET assigned_to = ? 
                WHERE ticket_id = ?
            ");
            $stmt->bind_param("ii", $admin_id, $ticket_id);

            $new_status = $old_status;
        }
    }

    $stmt->execute();
    $stmt->close();


    /* =========================================
       LOG + EMAIL: ASSIGNMENT CHANGE
    ========================================= */
    if ($old_name != $new_name) {

        $action_text = "$old_name transferred the ticket to $new_name";

        $stmt = $conn->prepare("
            INSERT INTO ticket_logs 
            (ticket_id, changed_by, action_type, field_name, old_value, new_value, comment, created_at, is_public) 
            VALUES (?, ?, 'update', 'assigned_to', ?, ?, ?, NOW(), 1)
        ");

        $stmt->bind_param("iisss", $ticket_id, $changed_by, $old_name, $new_name, $action_text);
        $stmt->execute();
        $stmt->close();

        // ✅ SEND EMAIL (FIXED)
        sendTicketEmail(
            $conn,
            $ticket_id,
            'assigned_to',
            $old_name,
            $new_name,
            $action_text,
            'update'
        );
    }


    /* =========================================
       LOG + EMAIL: STATUS CHANGE
    ========================================= */
    if ($old_status != $new_status) {

        $stmt = $conn->prepare("
            INSERT INTO ticket_logs 
            (ticket_id, changed_by, action_type, field_name, old_value, new_value, created_at, is_public) 
            VALUES (?, ?, 'update', 'status', ?, ?, NOW(), 1)
        ");

        $stmt->bind_param("iiss", $ticket_id, $changed_by, $old_status, $new_status);
        $stmt->execute();
        $stmt->close();

        // ✅ SEND EMAIL (FIXED)
        sendTicketEmail(
            $conn,
            $ticket_id,
            'status',
            $old_status,
            $new_status,
            '',
            'update'
        );
    }

}

$conn->close();