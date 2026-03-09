<?php
include '../auth/auth.php';
include '../db/db.php';
$data = json_decode(file_get_contents('php://input'), true);
$ticket_id = $data['ticket_id'] ?? null;
$message = trim($data['message'] ?? '');
$sender_id = $_SESSION['user_id'];

if ($ticket_id && $message) {
    $stmt = $conn->prepare("INSERT INTO ticket_messages (ticket_id,sender_id,message,sender_role,created_at) VALUES (?,?,?,?,NOW())");
    $role = $_SESSION['user_type'] ?? 'user';
    $stmt->bind_param("iiss",$ticket_id,$sender_id,$message,$role);
    $stmt->execute();
    echo json_encode(['success'=>true]);
} else {
    echo json_encode(['success'=>false]);
}
?>
