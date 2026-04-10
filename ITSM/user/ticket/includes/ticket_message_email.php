<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../../../PHPMailer/Exception.php';
require __DIR__ . '/../../../PHPMailer/PHPMailer.php';
require __DIR__ . '/../../../PHPMailer/SMTP.php';
// REQUIRED VARIABLES BEFORE INCLUDING:
// $ticketId, $ticketMessage, $senderId, $senderRole, $conn

if (!isset($ticketId, $ticketMessage, $senderId, $senderRole, $conn)) {
    error_log("ticket_message_email.php missing required variables");
    return;
}

// GET TICKET INFO
$stmt = $conn->prepare("
    SELECT t.ticket_id, t.ticket_number, t.subject, t.priority, u.fullname, u.email
    FROM ticket_tb t
    JOIN user_tb u ON t.user_id = u.user_id
    WHERE t.ticket_id = ?
");
$stmt->bind_param("i", $ticketId);
$stmt->execute();
$ticket = $stmt->get_result()->fetch_assoc();
if (!$ticket) return;

$id     = $ticket['ticket_id'];
$userEmail     = $ticket['email'];
$fullname      = $ticket['fullname'];
$ticketNumber  = $ticket['ticket_number'];
$subject       = $ticket['subject'];
$priority      = ucfirst($ticket['priority']);

// BUILD EMAIL BODY
$updateMessage = ($senderRole === 'admin') 
    ? "Admin commented on the ticket:<br><br><b>{$ticketMessage}</b>"
    : "$fullname commented on the ticket:<br><br><b>{$ticketMessage}</b>";

$emailBody = "
Hello Admin,<br><br>
{$updateMessage}<br><br>

<a href='http://115.88.1.63/koppel-itsm/ITSM/admin/index.php?page=ticket/view_ticket&ticket_id=$id'><button>Click here to reply</button> </a> <br><br>

Regards,<br>
IT Support Team
";

// SEND EMAIL
try {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'koppelit101424@gmail.com';
    $mail->Password   = 'eymk qyiv awbw wvxb'; // App password
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    $mail->setFrom('koppelit101424@gmail.com', 'IT Support');
    $mail->addAddress('itticketing@koppel.ph');
    // $mail->addCC($userEmail, $fullname);

    $mail->isHTML(true);
    $mail->Subject = "[UPDATE] Ticket {$ticketNumber} - {$subject} ({$priority})";
    $mail->Body    = $emailBody;

    $mail->send();
} catch (Exception $e) {
    error_log("Mail Error (ticket_message_email.php): " . $mail->ErrorInfo);
}
