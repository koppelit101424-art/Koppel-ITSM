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
    $mail->Password   = 'eymk qyiv awbw wvxb'; // USE APP PASSWORD
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    $mail->setFrom('koppelit101424@gmail.com', 'IT Support');
    $mail->addAddress($userEmail, $fullname);
    $mail->addCC('itticketing@koppel.ph');

    $mail->isHTML(true);
    $mail->Subject = "[UPDATE] on {$ticket_number} - {$subject} (". ucfirst($priority).")";

    // MESSAGE
    if ($email_type === 'comment') {

        $updateMessage = "
            Admin commented on the ticket:<br><br>
            {$email_comment}
        ";

    } else {

        $fieldLabel = ucfirst(str_replace("_", " ", $field));

        $updateMessage = "
            <b>{$fieldLabel}</b> set to <b>{$new_value}</b><br><br>";
        if ($comment && $is_public === 1 ) {
            $updateMessage .= "{$comment}";
        }else{
            $updateMessage .= "";
        }
    }

    $mail->Body = "
    Hello {$fullname},<br><br>

    {$updateMessage}<br><br>

    Regards,<br>
    IT Support Team
    ";

    $mail->send();

} catch (Exception $e) {
    error_log("Mail Error: " . $mail->ErrorInfo);
}