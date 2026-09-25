<?php
include 'includes/auth.php';
include 'includes/db.php';
header('Content-Type: application/json');


date_default_timezone_set('Asia/Manila');

if(isset($_GET['ajax']) && $_GET['ajax'] === 'send_message'){

    $data = json_decode(file_get_contents('php://input'), true);

    $ticket_id = $data['ticket_id'] ?? null;
    $message   = trim($data['message'] ?? '');
    $senderId  = $_SESSION['user_id'];
    $senderRole = $_SESSION['user_type'] ?? 'user';

    if(!$ticket_id || !$message){
        echo json_encode(['success'=>false,'error'=>'Ticket ID or message missing']);
        exit;
    }

    // INSERT MESSAGE
    $stmt = $conn->prepare("
        INSERT INTO ticket_messages (ticket_id, sender_id, sender_role, message, created_at)
        VALUES (?, ?, ?, ?, NOW())
    ");
    $stmt->bind_param("iiss", $ticket_id, $senderId, $senderRole, $message);

    if(!$stmt->execute()){
        echo json_encode(['success'=>false, 'error'=>$stmt->error]);
        exit;
    }

    /* ===========================
       TRIGGER EMAIL AFTER INSERT
    ============================ */
    $ticketMessage = $message;
    $ticketId      = $ticket_id;
    $senderId      = $senderId;      // ✅ pass sender ID
    $senderRole    = $senderRole;    // ✅ pass role
    // Include the email script
    include __DIR__ . '/../includes/ticket_message_email.php';

    echo json_encode(['success'=>true]);
    $conn->close();
}