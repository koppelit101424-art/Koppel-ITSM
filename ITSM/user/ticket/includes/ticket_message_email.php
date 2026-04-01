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
    SELECT t.ticket_number, t.subject, t.priority, u.fullname, u.email
    FROM ticket_tb t
    JOIN user_tb u ON t.user_id = u.user_id
    WHERE t.ticket_id = ?
");
$stmt->bind_param("i", $ticketId);
$stmt->execute();
$ticket = $stmt->get_result()->fetch_assoc();
if (!$ticket) return;

$userEmail     = $ticket['email'];
$fullname      = $ticket['fullname'];
$ticketNumber  = $ticket['ticket_number'];
$subject       = $ticket['subject'];
$priority      = ucfirst($ticket['priority']);

// BUILD EMAIL BODY
$updateMessage = ($senderRole === 'admin') 
    ? "Admin commented on the ticket:<br><br><b>{$ticketMessage}</b>"
    : "User commented on the ticket:<br><br><b>{$ticketMessage}</b>";

$emailBody = "
Hello {$fullname},<br><br>
{$updateMessage}<br><br>
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
    $mail->Password   = ''; // App password
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    $mail->setFrom('koppelit101424@gmail.com', 'IT Support');
    $mail->addAddress($userEmail, $fullname);
    $mail->addCC('itticketing@koppel.ph');

    $mail->isHTML(true);
    $mail->Subject = "[UPDATE] Ticket {$ticketNumber} - {$subject} ({$priority})";
    $mail->Body    = $emailBody;

    $mail->send();
} catch (Exception $e) {
    error_log("Mail Error (ticket_message_email.php): " . $mail->ErrorInfo);
}