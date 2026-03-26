<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
date_default_timezone_set('Asia/Manila');

require __DIR__ . '/../../../PHPMailer/Exception.php';
require __DIR__ . '/../../../PHPMailer/PHPMailer.php';
require __DIR__ . '/../../../PHPMailer/SMTP.php';
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'koppelit101424@gmail.com';
    $mail->Password   = 'eymk qyiv awbw wvxb'; // not your real password
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    $mail->setFrom('koppelit101424@gmail.com', 'IT Support');
    $mail->addAddress($user['email'], $user['fullname']);
    $mail->addCC('itticketing@koppel.ph');

    $mail->isHTML(true);
    $mail->Subject = "$ticket_number {$subject} (". ucfirst($_POST['priority']).")";

    $mail->Body = "
    Hello {$user['fullname']}, <br><br>

    A new IT support ticket has been submitted and acknowledged.<br><br>

    Ticket #: <strong>{$ticket_number}</strong><br>
    Submitted At: <strong>" . date('m-d-y h:i A') . " </strong><br><br>

    Name: <strong>{$user['fullname']}</strong><br>
    Email: <strong>{$user['email']}</strong><br>
    Department: <strong>{$user['department']}</strong><br>
    Company: <strong>{$user['company']}</strong><br><br>

    Priority:<strong>" . ucfirst($_POST['priority']) . "</strong><br>
    Category: <strong>" . ucfirst($_POST['ticket_category']) . "</strong><br><br>

    Subject: <strong>{$subject}</strong><br>
    Description:<strong> {$_POST['issue']}</strong><br><br>
    

    Your request has been logged and assigned a ticket number.<br>
    The IT team will review your concern and get back to you as soon as possible.<br><br>

    Please keep this ticket number for reference.
    ";

    $mail->send();

} catch (Exception $e) {
    // optional: log error instead of stopping system
}
?>