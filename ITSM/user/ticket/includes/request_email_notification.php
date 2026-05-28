<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../../../PHPMailer/Exception.php';
require __DIR__ . '/../../../PHPMailer/PHPMailer.php';
require __DIR__ . '/../../../PHPMailer/SMTP.php';

if (!isset($conn, $lmr_no, $user_id, $validItems)) {
    return;
}

/* =========================================
   GET USER DETAILS
========================================= */
$stmt = $conn->prepare("
    SELECT fullname, email, department, company
    FROM user_tb
    WHERE user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();

$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    return;
}

$fullname   = $user['fullname'];
$userEmail  = $user['email'];
$department = $user['department'];
$company    = $user['company'];

/* =========================================
   BUILD ITEM TABLE
========================================= */
$itemRows = '';

foreach ($validItems as $itemData) {

    $itemRows .= "
    <tr>
        <td>{$itemData['item']}</td>
        <td>{$itemData['desc']}</td>
        <td>{$itemData['qty']}</td>
        <td>{$itemData['uom']}</td>
        <td>{$itemData['date_needed']}</td>
        <td>{$itemData['status']}</td>
    </tr>
    ";
}

/* =========================================
   EMAIL BODY
========================================= */
$emailBody = "
Hello {$fullname}, <br><br>

Your material request has been successfully submitted.<br><br>

<b>LMR No:</b> {$lmr_no}<br>
<b>Date Submitted:</b> " . date('m-d-Y h:i A') . "<br><br>

<b>Requestor:</b> {$fullname}<br>
<b>Department:</b> {$department}<br>
<b>Company:</b> {$company}<br><br>

<table border='1' cellpadding='8' cellspacing='0' width='100%'>
    <thead>
        <tr>
            <th>Item</th>
            <th>Description</th>
            <th>Qty</th>
            <th>UoM</th>
            <th>Date Needed</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        {$itemRows}
    </tbody>
</table>

<br><br>

Your request is now pending for processing.<br><br>

Regards,<br>
IT Support Team
";

/* =========================================
   SEND EMAIL
========================================= */
try {

    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'koppelit101424@gmail.com';
    $mail->Password   = 'eymk qyiv awbw wvxb';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->setFrom('koppelit101424@gmail.com', 'IT Support');

    // REQUESTOR
    $mail->addAddress($userEmail, $fullname);

    // IT GROUP
    $mail->addCC('itticketing@koppel.ph','itsupervisor@koppel.ph');

    $mail->isHTML(true);

    $mail->Subject = "LMR Request Submitted - {$lmr_no}";
    $mail->Body    = $emailBody;

    $mail->send();

} catch (Exception $e) {

    error_log("LMR Email Error: " . $mail->ErrorInfo);

}
?>