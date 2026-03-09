<?php
session_start();
include '../auth/auth.php';
include '../db/db.php';
header('Content-Type: application/json');

$currentUserId = $_SESSION['user_id'] ?? null;
$role = $_SESSION['user_type'] ?? 'user';

if (!$currentUserId) {
    echo json_encode(['success'=>false, 'error'=>'Not logged in']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$ticket_id = $data['ticket_id'] ?? null;
$message = trim($data['message'] ?? '');

if (!$ticket_id || !$message) {
    echo json_encode(['success'=>false,'error'=>'Ticket ID or message missing']);
    exit;
}

$sender_role = ($role === 'admin') ? 'admin' : 'user';

$stmt = $conn->prepare("INSERT INTO ticket_messages (ticket_id, sender_id, sender_role, message, created_at) VALUES (?, ?, ?, ?, NOW())");
$stmt->bind_param("iiss", $ticket_id, $currentUserId, $sender_role, $message);

if($stmt->execute()){
    echo json_encode(['success'=>true]);
} else {
    echo json_encode(['success'=>false, 'error'=>$stmt->error]);
}
$conn->close();
