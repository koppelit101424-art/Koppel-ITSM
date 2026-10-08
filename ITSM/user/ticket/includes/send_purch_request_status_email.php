<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


/*
|--------------------------------------------------------------------------
| PHPMailer
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../../../PHPMailer/Exception.php';
require_once __DIR__ . '/../../../PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../../../PHPMailer/SMTP.php';


/**
 * Send purchasing request status notification.
 *
 * Email is sent ONLY when the NEW status is:
 *
 * - pending
 * - rejected
 * - goods received
 * - closed
 *
 * Email recipients:
 *
 * TO:
 * - Requestor
 *
 * CC:
 * - Assigned Purchaser
 * - IT Ticketing
 * - IT Supervisor
 *
 * @param mysqli $conn
 * @param int     $requestId
 * @param int     $requestorId
 * @param int     $purchaserId
 * @param string  $newStatus
 * @param array   $changes
 * @param string  $comment
 *
 * @return bool
 */
function sendPurchRequestStatusEmail(
    $conn,
    $requestId,
    $requestorId,
    $newStatus,
    $changes = [],
    $comment = '',
    $purchaserId = 0
)
 {

    /*
    |--------------------------------------------------------------------------
    | BASIC VALIDATION
    |--------------------------------------------------------------------------
    */

    $requestId   = (int)$requestId;
    $requestorId = (int)$requestorId;
    $purchaserId = (int)$purchaserId;

    $newStatus = strtolower(
        trim($newStatus)
    );


    if ($requestId <= 0) {

        error_log(
            'Purchasing Email: Invalid request ID.'
        );

        return false;
    }


    if ($requestorId <= 0) {

        error_log(
            "Purchasing Email: Invalid requestor ID for request {$requestId}."
        );

        return false;
    }


    /*
    |--------------------------------------------------------------------------
    | ONLY SEND FOR THESE STATUSES
    |--------------------------------------------------------------------------
    */

    $allowedEmailStatuses = [
        'pending',
        'rejected',
        'goods received',
        'final po approved',
        'closed'
    ];


    if (!in_array(
        $newStatus,
        $allowedEmailStatuses,
        true
    )) {

        error_log(
            "Purchasing Email: Status '{$newStatus}' does not require email."
        );

        return false;
    }


    /*
    |--------------------------------------------------------------------------
    | GET LMR NUMBER
    |--------------------------------------------------------------------------
    |
    | Request ID is used internally only to retrieve
    | the actual LMR Number.
    |
    */

    $requestStmt = $conn->prepare("
        SELECT
            lmr_no
        FROM purch_request_tb
        WHERE request_id = ?
        LIMIT 1
    ");


    if (!$requestStmt) {

        error_log(
            'Purchasing Email: LMR query prepare failed: ' .
            $conn->error
        );

        return false;
    }


    $requestStmt->bind_param(
        "i",
        $requestId
    );


    if (!$requestStmt->execute()) {

        error_log(
            'Purchasing Email: LMR query execute failed: ' .
            $requestStmt->error
        );

        $requestStmt->close();

        return false;
    }


    $requestResult =
        $requestStmt->get_result();


    $requestData =
        $requestResult->fetch_assoc();


    $requestStmt->close();


    if (!$requestData) {

        error_log(
            "Purchasing Email: Request #{$requestId} not found."
        );

        return false;
    }


    /*
    |--------------------------------------------------------------------------
    | LMR NUMBER
    |--------------------------------------------------------------------------
    */

    $lmrNo = trim(
        $requestData['lmr_no'] ?? ''
    );


    if ($lmrNo === '') {

        /*
         * Fallback only for safety.
         */

        $lmrNo =
            'LMR-' . $requestId;
    }


    /*
    |--------------------------------------------------------------------------
    | GET REQUESTOR DETAILS
    |--------------------------------------------------------------------------
    */

    $stmt = $conn->prepare("
        SELECT
            fullname,
            email,
            department,
            company
        FROM user_tb
        WHERE user_id = ?
        LIMIT 1
    ");


    if (!$stmt) {

        error_log(
            'Purchasing Email: Requestor query failed: ' .
            $conn->error
        );

        return false;
    }


    $stmt->bind_param(
        "i",
        $requestorId
    );


    if (!$stmt->execute()) {

        error_log(
            'Purchasing Email: Requestor query execute failed: ' .
            $stmt->error
        );

        $stmt->close();

        return false;
    }


    $result =
        $stmt->get_result();


    $user =
        $result->fetch_assoc();


    $stmt->close();


    /*
    |--------------------------------------------------------------------------
    | REQUESTOR NOT FOUND
    |--------------------------------------------------------------------------
    */

    if (!$user) {

        error_log(
            "Purchasing Email: Requestor not found. User ID: {$requestorId}"
        );

        return false;
    }


    /*
    |--------------------------------------------------------------------------
    | REQUESTOR DETAILS
    |--------------------------------------------------------------------------
    */

    $fullname =
        trim($user['fullname'] ?? '');

    $userEmail =
        trim($user['email'] ?? '');

    $department =
        trim($user['department'] ?? '');

    $company =
        trim($user['company'] ?? '');


    /*
    |--------------------------------------------------------------------------
    | REQUESTOR EMAIL VALIDATION
    |--------------------------------------------------------------------------
    */

    if ($userEmail === '') {

        error_log(
            "Purchasing Email: Requestor {$requestorId} does not have an email."
        );

        return false;
    }


    if (!filter_var(
        $userEmail,
        FILTER_VALIDATE_EMAIL
    )) {

        error_log(
            "Purchasing Email: Invalid requestor email: {$userEmail}"
        );

        return false;
    }


    /*
    |--------------------------------------------------------------------------
    | GET PURCHASER DETAILS
    |--------------------------------------------------------------------------
    |
    | purchaser_id comes from purch_request_tb.
    |
    */

    $purchaserEmail = '';
    $purchaserName  = '';


    if ($purchaserId > 1) {

        $purchaserStmt = $conn->prepare("
            SELECT
                fullname,
                email
            FROM user_tb
            WHERE user_id = ?
            LIMIT 1
        ");


        if (!$purchaserStmt) {

            /*
             * Do not stop the entire email just because
             * purchaser lookup failed.
             *
             * The requestor can still receive the email.
             */

            error_log(
                'Purchasing Email: Purchaser query prepare failed: ' .
                $conn->error
            );

        } else {

            $purchaserStmt->bind_param(
                "i",
                $purchaserId
            );


            if (!$purchaserStmt->execute()) {

                error_log(
                    'Purchasing Email: Purchaser query execute failed: ' .
                    $purchaserStmt->error
                );

            } else {

                $purchaserResult =
                    $purchaserStmt->get_result();


                $purchaser =
                    $purchaserResult->fetch_assoc();


                if ($purchaser) {

                    $purchaserName =
                        trim(
                            $purchaser['fullname'] ?? ''
                        );


                    $purchaserEmail =
                        trim(
                            $purchaser['email'] ?? ''
                        );
                }
            }


            $purchaserStmt->close();
        }
    }


    /*
    |--------------------------------------------------------------------------
    | PURCHASER EMAIL VALIDATION
    |--------------------------------------------------------------------------
    */

    $validPurchaserEmail = false;


    if (
        $purchaserEmail !== '' &&
        filter_var(
            $purchaserEmail,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        $validPurchaserEmail = true;
    }


    if (
        $purchaserId > 1 &&
        !$validPurchaserEmail
    ) {

        error_log(
            "Purchasing Email: Purchaser {$purchaserId} " .
            "has no valid email address. Purchaser will not be CC'd."
        );
    }


    /*
    |--------------------------------------------------------------------------
    | STATUS LABEL
    |--------------------------------------------------------------------------
    */

    $statusLabel =
        ucwords($newStatus);


    /*
    |--------------------------------------------------------------------------
    | SAFE VALUES
    |--------------------------------------------------------------------------
    */

    $safeLmrNo =
        htmlspecialchars(
            $lmrNo,
            ENT_QUOTES,
            'UTF-8'
        );


    $safeFullname =
        htmlspecialchars(
            $fullname,
            ENT_QUOTES,
            'UTF-8'
        );


    $safeDepartment =
        htmlspecialchars(
            $department,
            ENT_QUOTES,
            'UTF-8'
        );


    $safeCompany =
        htmlspecialchars(
            $company,
            ENT_QUOTES,
            'UTF-8'
        );


    $safeStatus =
        htmlspecialchars(
            $statusLabel,
            ENT_QUOTES,
            'UTF-8'
        );


    /*
    |--------------------------------------------------------------------------
    | BUILD CHANGE TABLE
    |--------------------------------------------------------------------------
    */

    $changeRows = '';


    foreach ($changes as $field => $change) {

        /*
         * Make sure the change is an array.
         */

        if (!is_array($change)) {
            continue;
        }


        $fieldLabel =
            ucwords(
                str_replace(
                    '_',
                    ' ',
                    $field
                )
            );


        $oldValue =
            htmlspecialchars(
                (string)($change['old'] ?? ''),
                ENT_QUOTES,
                'UTF-8'
            );


        $newValue =
            htmlspecialchars(
                (string)($change['new'] ?? ''),
                ENT_QUOTES,
                'UTF-8'
            );


        /*
         * Display blank values as N/A.
         */

        if ($oldValue === '') {
            $oldValue = 'N/A';
        }


        if ($newValue === '') {
            $newValue = 'N/A';
        }


        $changeRows .= "
            <tr>

                <td style='
                    padding:10px;
                    border:1px solid #ddd;
                    font-weight:bold;
                    vertical-align:top;
                '>
                    {$fieldLabel}
                </td>

                <td style='
                    padding:10px;
                    border:1px solid #ddd;
                    vertical-align:top;
                '>
                    {$oldValue}
                </td>

                <td style='
                    padding:10px;
                    border:1px solid #ddd;
                    vertical-align:top;
                '>
                    {$newValue}
                </td>

            </tr>
        ";
    }


    /*
    |--------------------------------------------------------------------------
    | NO FIELD CHANGES
    |--------------------------------------------------------------------------
    */

    if ($changeRows === '') {

        $changeRows = "
            <tr>

                <td
                    colspan='3'
                    style='
                        padding:10px;
                        border:1px solid #ddd;
                        text-align:center;
                        color:#666;
                    '
                >
                    No other field changes.
                </td>

            </tr>
        ";
    }


    /*
    |--------------------------------------------------------------------------
    | COMMENT
    |--------------------------------------------------------------------------
    */

    $commentHtml = '';
/*
|--------------------------------------------------------------------------
| GOODS RECEIVED BUTTON
|--------------------------------------------------------------------------
*/

$goodsReceivedButton = '';

if ($newStatus === 'goods received') {

    $confirmationUrl =
        'https://115.88.1.63/Koppel-ITSM/ITSM/user/index.php'
        . '?page=ticket/view_purch_request'
        . '&request_id=' . urlencode($requestId);

    $safeConfirmationUrl =
        htmlspecialchars(
            $confirmationUrl,
            ENT_QUOTES,
            'UTF-8'
        );

    $goodsReceivedButton = "

        <div style='
            margin:25px 0;
            padding:20px;
            background:#f0f8f4;
            border:1px solid #b7dfc8;
            border-radius:6px;
            text-align:center;
        '>

            <p style='
                margin-top:0;
                font-size:15px;
                font-weight:bold;
                color:#198754;
            '>
                Item Marked as Goods Received
            </p>

            <p style='
                color:#555;
                line-height:1.5;
            '>
                Please click the button below to review your
                purchasing request and confirm that you received
                the item and that it is in good condition.
            </p>

            <a href='{$safeConfirmationUrl}'
               style='
                    display:inline-block;
                    padding:12px 24px;
                    background:#198754;
                    color:#ffffff;
                    text-decoration:none;
                    border-radius:5px;
                    font-weight:bold;
                    font-size:14px;
               '>
                Confirm Goods Received
            </a>

        </div>

    ";
}

    if (trim($comment) !== '') {

        $safeComment =
            nl2br(
                htmlspecialchars(
                    $comment,
                    ENT_QUOTES,
                    'UTF-8'
                )
            );


        $commentHtml = "

            <div style='margin-top:20px;'>

                <p>
                    <b>Comment</b>
                </p>

                <div style='
                    padding:12px;
                    background:#f5f5f5;
                    border-left:4px solid #555;
                    line-height:1.5;
                '>
                    {$safeComment}
                </div>

            </div>

        ";
    }


    /*
    |--------------------------------------------------------------------------
    | STATUS COLOR
    |--------------------------------------------------------------------------
    */

    $statusColor = '#1a73e8';


    switch ($newStatus) {

        case 'pending':
            $statusColor = '#f39c12';
            break;

        case 'rejected':
            $statusColor = '#dc3545';
            break;

        case 'goods received':
            $statusColor = '#198754';
            break;

        case 'final po approved':
            $statusColor = '#198754';
            break;

        case 'closed':
            $statusColor = '#6c757d';
            break;
    }


    /*
    |--------------------------------------------------------------------------
    | EMAIL BODY
    |--------------------------------------------------------------------------
    */

    $emailBody = "

<!DOCTYPE html>

<html>

<head>

    <meta charset='UTF-8'>

    <title>
        {$safeLmrNo} - {$safeStatus}
    </title>

</head>


<body>

<div style='
    font-family:Arial,Helvetica,sans-serif;
    font-size:14px;
    color:#333;
    max-width:800px;
    margin:0 auto;
    padding:20px;
'>


    <!-- HEADER -->

    <div style='
        background:#1f4e78;
        color:#ffffff;
        padding:18px;
        border-radius:6px 6px 0 0;
    '>

        <h2 style='
            margin:0;
            font-size:20px;
        '>
            Purchasing Request Update
        </h2>

    </div>


    <!-- CONTENT -->

    <div style='
        border:1px solid #ddd;
        border-top:none;
        padding:25px;
    '>


        <p>
            Hello {$safeFullname},
        </p>


        <p>
            Your Local Material Request has been updated.
        </p>


        <!-- REQUEST SUMMARY -->

        <div style='
            background:#f5f7fa;
            padding:15px;
            border-radius:5px;
            margin:20px 0;
        '>

            <table
                cellpadding='4'
                cellspacing='0'
                width='100%'
                style='border-collapse:collapse;'
            >

                <tr>

                    <td style='width:150px;'>
                        <b>LMR Number:</b>
                    </td>

                    <td>
                        {$safeLmrNo}
                    </td>

                </tr>


                <tr>

                    <td>
                        <b>Date Updated:</b>
                    </td>

                    <td>
                        " . date('m-d-Y h:i A') . "
                    </td>

                </tr>


                <tr>

                    <td>
                        <b>Requestor:</b>
                    </td>

                    <td>
                        {$safeFullname}
                    </td>

                </tr>


                <tr>

                    <td>
                        <b>Department:</b>
                    </td>

                    <td>
                        {$safeDepartment}
                    </td>

                </tr>


                <tr>

                    <td>
                        <b>Company:</b>
                    </td>

                    <td>
                        {$safeCompany}
                    </td>

                </tr>


                <tr>

                    <td>
                        <b>New Status:</b>
                    </td>

                    <td>

                        <span style='
                            display:inline-block;
                            padding:5px 10px;
                            background:{$statusColor};
                            color:#ffffff;
                            border-radius:4px;
                            font-weight:bold;
                        '>
                            {$safeStatus}
                        </span>

                    </td>

                </tr>

            </table>

        </div>


        <!-- CHANGES -->

        <p>
            <b>Changes Made</b>
        </p>


        <table
            cellpadding='0'
            cellspacing='0'
            width='100%'
            style='
                border-collapse:collapse;
                margin-bottom:20px;
            '
        >

            <thead>

                <tr style='
                    background:#f1f1f1;
                '>

                    <th style='
                        padding:10px;
                        border:1px solid #ddd;
                        text-align:left;
                    '>
                        Field
                    </th>


                    <th style='
                        padding:10px;
                        border:1px solid #ddd;
                        text-align:left;
                    '>
                        Previous Value
                    </th>


                    <th style='
                        padding:10px;
                        border:1px solid #ddd;
                        text-align:left;
                    '>
                        New Value
                    </th>

                </tr>

            </thead>


            <tbody>

                {$changeRows}

            </tbody>

        </table>


        {$commentHtml}

        {$goodsReceivedButton}


        <br>

        <p style='
            color:#666;
            font-size:13px;
        '>

            This is an automated notification from the
            LMR &amp; Purchasing Request System.

        </p>


        <p>

            Regards,<br>

            <b>IT Support Team</b>

        </p>


    </div>


</div>

</body>

</html>
";


    /*
    |--------------------------------------------------------------------------
    | SEND EMAIL
    |--------------------------------------------------------------------------
    */

    try {

        $mail =
            new PHPMailer(true);


        /*
        |--------------------------------------------------------------------------
        | SMTP DEBUG
        |--------------------------------------------------------------------------
        |
        | Keep this at 0 normally.
        |
        | Change to 2 temporarily if you need SMTP debugging.
        |
        */

        $mail->SMTPDebug = 0;


        $mail->Debugoutput =
            function ($str, $level) {

                error_log(
                    "PHPMailer SMTP [{$level}]: {$str}"
                );
            };


        /*
        |--------------------------------------------------------------------------
        | SMTP SETTINGS
        |--------------------------------------------------------------------------
        */

        $mail->isSMTP();

        $mail->Host =
            'smtp.gmail.com';

        $mail->SMTPAuth =
            true;

        $mail->Username =
            'koppelit101424@gmail.com';


        /*
         * IMPORTANT:
         *
         * Put your 16-character Gmail APP PASSWORD here.
         *
         * Example:
         *
         * $mail->Password = 'abcdabcdabcdabcd';
         *
         * Do NOT use your normal Gmail password.
         */

        $mail->Password =
            'qmol klsu mdqb itpk';


        $mail->SMTPSecure =
            PHPMailer::ENCRYPTION_STARTTLS;

        $mail->Port =
            587;


        /*
        |--------------------------------------------------------------------------
        | ENCODING
        |--------------------------------------------------------------------------
        */

        $mail->CharSet =
            'UTF-8';


        /*
        |--------------------------------------------------------------------------
        | FROM
        |--------------------------------------------------------------------------
        */

        $mail->setFrom(
            'koppelit101424@gmail.com',
            'IT Support'
        );


        /*
        |--------------------------------------------------------------------------
        | REQUESTOR - TO
        |--------------------------------------------------------------------------
        */

        $mail->addAddress(
            $userEmail,
            $fullname
        );


        /*
        |--------------------------------------------------------------------------
        | PURCHASER - CC
        |--------------------------------------------------------------------------
        |
        | The assigned purchaser is added as CC.
        |
        | We check that:
        |
        | 1. purchaser_id is valid
        | 2. purchaser has a valid email
        | 3. purchaser is not already the requestor
        |
        */

        if (
            $validPurchaserEmail &&
            strcasecmp(
                $purchaserEmail,
                $userEmail
            ) !== 0
        ) {

            $mail->addCC(
                $purchaserEmail,
                $purchaserName
            );

            error_log(
                "Purchasing Email: Purchaser CC added: " .
                "{$purchaserEmail}"
            );
        }


        /*
        |--------------------------------------------------------------------------
        | IT GROUP - CC
        |--------------------------------------------------------------------------
        */

        $mail->addCC(
            'itticketing@koppel.ph',
            'IT Ticketing'
        );


        $mail->addCC(
            'itsupervisor@koppel.ph',
            'IT Supervisor'
        );


        /*
        |--------------------------------------------------------------------------
        | HTML EMAIL
        |--------------------------------------------------------------------------
        */

        $mail->isHTML(true);


        /*
        |--------------------------------------------------------------------------
        | SUBJECT
        |--------------------------------------------------------------------------
        |
        | Example:
        |
        | LMR-2026-00125 - Pending
        |
        */

        $mail->Subject =
            "{$lmrNo} - {$statusLabel}";


        /*
        |--------------------------------------------------------------------------
        | BODY
        |--------------------------------------------------------------------------
        */

        $mail->Body =
            $emailBody;


        /*
        |--------------------------------------------------------------------------
        | PLAIN TEXT VERSION
        |--------------------------------------------------------------------------
        */

        $plainChanges = '';


        foreach ($changes as $field => $change) {

            if (!is_array($change)) {
                continue;
            }


            $fieldLabel =
                ucwords(
                    str_replace(
                        '_',
                        ' ',
                        $field
                    )
                );


            $old =
                (string)($change['old'] ?? '');


            $new =
                (string)($change['new'] ?? '');


            if ($old === '') {
                $old = 'N/A';
            }


            if ($new === '') {
                $new = 'N/A';
            }


            $plainChanges .=
                "{$fieldLabel}: {$old} -> {$new}\n";
        }


        if ($plainChanges === '') {

            $plainChanges =
                "No other field changes.\n";
        }


        $mail->AltBody =
            "Hello {$fullname},\n\n" .

            "Your purchasing request has been updated.\n\n" .

            "LMR Number: {$lmrNo}\n" .

            "Date Updated: " .
            date('m-d-Y h:i A') .
            "\n" .

            "Requestor: {$fullname}\n" .

            "Department: {$department}\n" .

            "Company: {$company}\n" .

            "New Status: {$statusLabel}\n\n" .

            "Changes Made:\n" .

            $plainChanges .

            (
                trim($comment) !== ''
                    ? "\nComment:\n{$comment}\n"
                    : ''
            ) .

            "\nRegards,\n" .

            "IT Support Team";


        /*
        |--------------------------------------------------------------------------
        | SEND
        |--------------------------------------------------------------------------
        */

        $mail->send();


        error_log(
            "Purchasing Email: Successfully sent " .
            "LMR {$lmrNo} ({$statusLabel}) " .
            "to {$userEmail}" .
            (
                $validPurchaserEmail
                    ? " | CC Purchaser: {$purchaserEmail}"
                    : " | No purchaser CC"
            )
        );


        return true;


    } catch (Exception $e) {

        error_log(
            "Purchasing Request Email Error: " .
            $e->getMessage()
        );


        error_log(
            "PHPMailer ErrorInfo: " .
            ($mail->ErrorInfo ?? 'Unknown')
        );


        return false;
    }
}
