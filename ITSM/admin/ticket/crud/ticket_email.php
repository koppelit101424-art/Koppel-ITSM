<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
date_default_timezone_set('Asia/Manila');

$user_id = $_POST['user_id'] ?? null;

$fullname = '';
$userEmail = '';
$department = '';
$company = '';

if ($user_id) {
    $stmt = $conn->prepare("SELECT fullname, email, department, company FROM user_tb WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $fullname = $row['fullname'];
        $userEmail = $row['email'];
        $department = $row['department'];
        $company = $row['company'];
    }
}

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
    $mail->addAddress($userEmail, $fullname);
    $mail->addCC('itticketing@koppel.ph');

    $mail->isHTML(true);
    $mail->Subject = "$ticket_number - {$subject} (". ucfirst($_POST['priority']).")";

    $mail->Body = "
    Hello {$fullname}, <br><br>

    A new IT support ticket has been submitted and acknowledged.<br><br>

    Ticket #: <strong>{$ticket_number}</strong><br>
    Submitted At: <strong>" . date('m-d-y h:i A') . " </strong><br><br>

    Name: <strong>{$fullname}</strong><br>
    Email: <strong>{$userEmail}</strong><br>
    Department: <strong>{$department}</strong><br>
    Company: <strong>{$company}</strong><br><br>

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