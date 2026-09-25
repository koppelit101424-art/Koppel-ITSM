<?php
include __DIR__ . '/../../../includes/auth.php';
include __DIR__ . '/../../../includes/db.php';
$data = json_decode(file_get_contents("php://input"), true);

function sendTicketEmail($conn, $ticket_id, $field, $old_value, $new_value, $comment, $type) {

    // GET ticket + user
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

    if (!$ticket) return; // safety

    // PASS VARIABLES
    $ticket_number   = $ticket['ticket_number'];
    $subject         = $ticket['subject'];
    $priority        = $ticket['priority'];
    $ticket_category = $ticket['ticket_category'];
    $fullname        = $ticket['fullname'];
    $userEmail       = $ticket['email'];

    $email_type      = $type;
    $email_comment   = $comment;

    include __DIR__ . '/../crud/ticket_status_email.php';
}

$ticket_id = $data['ticket_id'] ?? null;
$field     = $data['field'] ?? null;
$comment   = $data['comment'] ?? null;
$is_public = $data['is_public'] ?? 1;
$changed_by = $_SESSION['user_id'];

/* =========================
   ✅ HANDLE COMMENT ONLY FIRST
========================= */
if ($field === 'comment_only') {

    if (!$ticket_id || !$comment) {
        http_response_code(400);
        exit;
    }

    $stmt = $conn->prepare("
        INSERT INTO ticket_logs 
        (ticket_id, changed_by, action_type, field_name, old_value, new_value, comment, is_public)
        VALUES (?, ?, 'comment', 'comment', '', '', ?, ?)
    ");

    $stmt->bind_param("iisi", $ticket_id, $changed_by, $comment, $is_public);
    $stmt->execute();

    /* ✅ SEND EMAIL */
    sendTicketEmail($conn, $ticket_id, null, null, null, $comment, 'comment');

    echo json_encode(['success' => true]);
    exit;
}

/* =========================
   ✅ NORMAL FIELD UPDATE
========================= */
if (!isset($ticket_id, $field, $data['value'])) {
    http_response_code(400);
    exit;
}

/* ALLOWED FIELDS */
$allowed = [
    'status',
    'priority',
    'urgency',
    'impact',
    'pending_reason',
    'ticket_category'
];

if (!in_array($field, $allowed)) {
    http_response_code(403);
    exit;
}

$new_value = $data['value'];

/* Get old value */
$stmt = $conn->prepare("SELECT $field FROM ticket_tb WHERE ticket_id = ?");
$stmt->bind_param("i", $ticket_id);
$stmt->execute();
$old_value = $stmt->get_result()->fetch_assoc()[$field] ?? null;

/* Update */
$update = $conn->prepare("UPDATE ticket_tb SET $field = ? WHERE ticket_id = ?");
$update->bind_param("si", $new_value, $ticket_id);
$update->execute();

/* Action */
$action_type = ($field === 'status' && $new_value === 'escalated')
    ? 'escalated'
    : 'update';

/* Insert log */
$stmt = $conn->prepare("
    INSERT INTO ticket_logs
    (ticket_id, changed_by, action_type, field_name, old_value, new_value, comment, is_public)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "iisssssi",
    $ticket_id,
    $changed_by,
    $action_type,
    $field,
    $old_value,
    $new_value,
    $comment,
    $is_public
);

$stmt->execute();

/* ✅ SEND EMAIL */
sendTicketEmail($conn, $ticket_id, $field, $old_value, $new_value, $comment, 'update');

echo json_encode(["success" => true]);