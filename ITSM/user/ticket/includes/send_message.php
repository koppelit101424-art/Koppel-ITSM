<?php
include 'includes/auth.php';
include 'includes/db.php';

/* ==========================
   AJAX HANDLER
========================== */

if(isset($_GET['ajax'])){

    if($_GET['ajax'] === 'send_message'){

        $data = json_decode(file_get_contents('php://input'), true);

        $ticket_id = $data['ticket_id'] ?? null;
        $message   = trim($data['message'] ?? '');
        $sender_id = $_SESSION['user_id'];
        $role      = $_SESSION['user_type'] ?? 'user';

        if($ticket_id && $message){

            $stmt = $conn->prepare("
                INSERT INTO ticket_messages
                (ticket_id, sender_id, message, sender_role, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ");

            $stmt->bind_param("iiss",$ticket_id,$sender_id,$message,$role);
            $stmt->execute();

            echo json_encode(['success'=>true]);
        }else{
            echo json_encode(['success'=>false]);
        }

        exit;
    }

}
?>
