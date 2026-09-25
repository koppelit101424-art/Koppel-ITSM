<?php
include __DIR__ . '/../../../includes/auth.php';
include __DIR__ . '/../../../includes/db.php';

$ticket_id  = $_POST['ticket_id'] ?? null;
$status     = $_POST['status'] ?? null;
$changed_by = $_SESSION['user_id'] ?? 0;


/* =========================================
   EMAIL FUNCTION (REUSE YOUR SYSTEM)
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

    // REQUIRED VARIABLES FOR EMAIL TEMPLATE
    $ticket_number   = $ticket['ticket_number'];
    $subject         = $ticket['subject'];
    $priority        = $ticket['priority'];
    $ticket_category = $ticket['ticket_category'];
    $fullname        = $ticket['fullname'];
    $userEmail       = $ticket['email'];

    $email_type    = $type;
    $email_comment = $comment;

    // IMPORTANT VARIABLES (FIX)
    $field     = $field;
    $old_value = $old_value;
    $new_value = $new_value;

    include __DIR__ . '/../crud/ticket_status_email.php';
}


/* =========================================
   MAIN STATUS UPDATE
========================================= */
if ($ticket_id && $status) {

    // GET OLD STATUS
    $stmt = $conn->prepare("SELECT status FROM ticket_tb WHERE ticket_id = ?");
    $stmt->bind_param("i", $ticket_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $old = $result->fetch_assoc();
    $stmt->close();

    $old_status = $old['status'] ?? '';

    // ONLY IF CHANGED
    if ($old_status !== $status) {

        /* =========================
           UPDATE STATUS
        ========================== */
        $stmt = $conn->prepare("UPDATE ticket_tb SET status = ? WHERE ticket_id = ?");
        $stmt->bind_param("si", $status, $ticket_id);
        $stmt->execute();
        $stmt->close();

        /* =========================
           INSERT LOG
        ========================== */
        $stmt = $conn->prepare("
            INSERT INTO ticket_logs 
            (ticket_id, changed_by, action_type, field_name, old_value, new_value, created_at, is_public) 
            VALUES (?, ?, 'update', 'status', ?, ?, NOW(), 1)
        ");

        $stmt->bind_param("iiss", $ticket_id, $changed_by, $old_status, $status);

        if (!$stmt->execute()) {
            die("Log Insert Error: " . $stmt->error);
        }

        $stmt->close();

        /* =========================
           SEND EMAIL ✅
        ========================== */
        sendTicketEmail(
            $conn,
            $ticket_id,
            'status',
            $old_status,
            $status,
            '',
            'update'
        );
    }
}

$conn->close();