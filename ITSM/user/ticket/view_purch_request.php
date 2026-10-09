<?php

include 'includes/auth.php';
include 'includes/db.php';

$request_id = (int)($_GET['request_id'] ?? 0);

if ($request_id <= 0) {
    echo '<div class="alert alert-danger">Invalid request ID.</div>';
    exit;
}

/*
|--------------------------------------------------------------------------
| ADD MORE ATTACHMENTS
|--------------------------------------------------------------------------
*/

$uploadErrors = [];
$uploadSuccess = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_attachments'])) {

    $uploadRequestId = (int)($_POST['request_id'] ?? 0);

    if ($uploadRequestId <= 0 || $uploadRequestId !== $request_id) {
        $uploadErrors[] = 'Invalid request ID.';
    }

    if (
        !isset($_FILES['new_attachments']) ||
        empty($_FILES['new_attachments']['name'][0])
    ) {
        $uploadErrors[] = 'Please select at least one file.';
    }

    if (empty($uploadErrors)) {

        $files = $_FILES['new_attachments'];

        /*
        |--------------------------------------------------------------------------
        | Maximum 10 files PER ADD ACTION
        |--------------------------------------------------------------------------
        */

        $fileCount = count($files['name']);

        if ($fileCount > 10) {
            $uploadErrors[] = 'You can add a maximum of 10 files at a time.';
        }
    }

    if (empty($uploadErrors)) {

        /*
        |--------------------------------------------------------------------------
        | Upload directory
        |--------------------------------------------------------------------------
        */

        $uploadDir = dirname(__DIR__) . "/uploads/purchasing/";
        $dbDir = "uploads/purchasing/";

        if (!is_dir($uploadDir)) {

            if (!mkdir($uploadDir, 0777, true)) {
                $uploadErrors[] =
                    'Unable to create attachment directory.';
            }
        }
    }

    if (empty($uploadErrors)) {

        /*
        |--------------------------------------------------------------------------
        | Allowed file types
        |--------------------------------------------------------------------------
        |
        | Excel intentionally excluded.
        |
        */

        $allowedExtensions = [
                            'jpg',
                            'jpeg',
                            'png',
                            'pdf',
                            'doc',
                            'docx',
                            'csv',
                            'xls',
                            'xlsx',
                            'xlsm',
                            'xlsb',
                            'xlt',
                            'xltx',
                            'xltm',
                            'ods'
        ];

        $uploadedFiles = [];

        /*
        |--------------------------------------------------------------------------
        | Validate ALL files first
        |--------------------------------------------------------------------------
        */

        for ($i = 0; $i < $fileCount; $i++) {

            if (
                empty($files['name'][$i]) ||
                $files['error'][$i] === UPLOAD_ERR_NO_FILE
            ) {
                continue;
            }

            if ($files['error'][$i] !== UPLOAD_ERR_OK) {

                $uploadErrors[] =
                    'Failed to upload file: ' .
                    $files['name'][$i];

                break;
            }

            $originalName =
                basename($files['name'][$i]);

            $extension =
                strtolower(
                    pathinfo(
                        $originalName,
                        PATHINFO_EXTENSION
                    )
                );

            /*
            |--------------------------------------------------------------------------
            | Reject Excel and other unsupported files
            |--------------------------------------------------------------------------
            */

            if (!in_array($extension, $allowedExtensions, true)) {

                $uploadErrors[] =
                    'Invalid file type: ' .
                    htmlspecialchars($originalName) .
                    '. Excel files are not allowed.';

                break;
            }

            /*
            |--------------------------------------------------------------------------
            | Generate unique filename
            |--------------------------------------------------------------------------
            */

            $newName =
                uniqid('lmr_', true) .
                '_' .
                $i .
                '.' .
                $extension;

            $fullPath =
                $uploadDir . $newName;

            $dbPath =
                $dbDir . $newName;

            $uploadedFiles[] = [
                'original_name' => $originalName,
                'tmp_name'      => $files['tmp_name'][$i],
                'full_path'     => $fullPath,
                'db_path'       => $dbPath
            ];
        }
    }

    /*
    |--------------------------------------------------------------------------
    | SAVE FILES
    |--------------------------------------------------------------------------
    */

    if (empty($uploadErrors) && !empty($uploadedFiles)) {

        $conn->begin_transaction();

        try {

            $attachStmt = $conn->prepare("
                INSERT INTO purch_request_attachments
                (
                    request_id,
                    file_name,
                    file_path
                )
                VALUES (?, ?, ?)
            ");

            if (!$attachStmt) {
                throw new Exception(
                    'Attachment database error: ' .
                    $conn->error
                );
            }

            foreach ($uploadedFiles as $file) {

                /*
                |--------------------------------------------------------------------------
                | Move physical file
                |--------------------------------------------------------------------------
                */

                if (!move_uploaded_file(
                    $file['tmp_name'],
                    $file['full_path']
                )) {

                    throw new Exception(
                        'Failed to save file: ' .
                        $file['original_name']
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Save database record
                |--------------------------------------------------------------------------
                */

                $attachStmt->bind_param(
                    "iss",
                    $request_id,
                    $file['original_name'],
                    $file['db_path']
                );

                if (!$attachStmt->execute()) {

                    throw new Exception(
                        'Failed to save attachment: ' .
                        $file['original_name']
                    );
                }
            }

            $attachStmt->close();

            $conn->commit();

            $uploadSuccess =
                count($uploadedFiles) .
                ' attachment(s) added successfully.';

        } catch (Exception $e) {

            $conn->rollback();

            /*
            |--------------------------------------------------------------------------
            | Delete files that were already moved if DB insert fails
            |--------------------------------------------------------------------------
            */

            foreach ($uploadedFiles as $file) {

                if (file_exists($file['full_path'])) {
                    @unlink($file['full_path']);
                }
            }

            $uploadErrors[] = $e->getMessage();
        }
    }
}
/*
|--------------------------------------------------------------------------
| STEP 1: GET LMR NUMBER FROM SELECTED REQUEST
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT lmr_no
    FROM purch_request_tb
    WHERE request_id = ?
    LIMIT 1
");

$stmt->bind_param("i", $request_id);
$stmt->execute();

$result = $stmt->get_result();
$selectedRequest = $result->fetch_assoc();

$stmt->close();

if (!$selectedRequest) {
    echo '<div class="alert alert-danger">Request not found.</div>';
    exit;
}

$lmr_no = trim($selectedRequest['lmr_no']);

function formatStatus($status)
{
    $status = trim((string)$status);

    if ($status === '') {
        return 'N/A';
    }

    if (
        strtolower($status) === 'n/a' ||
        strtolower($status) === 'na'
    ) {
        return 'N/A';
    }

    $status = strtolower($status);

    $status = ucfirst($status);

    $status = preg_replace(
        '/\bpo\b/i',
        'PO',
        $status
    );

    return $status;
}
/*
|--------------------------------------------------------------------------
| STEP 2: GET ALL REQUESTS WITH THE SAME LMR NO
|--------------------------------------------------------------------------
|
| One LMR can contain multiple items.
|
*/

$stmt = $conn->prepare("
    SELECT
        r.request_id,
        r.lmr_no,
        r.user_id,
        r.requestor,
        u.fullname,
        u.company,
        r.department,
        r.item,
        r.category_id,
        rc.category_name,
        r.description,
        r.quantity,
        r.UoM,
        r.date_needed,
        r.remarks,
        r.status,
        r.order_status,
        r.priority,
        r.po_no,
        r.date_created,
        r.date_updated,
        r.purchaser_id,
        p.fullname AS purchaser_name
    FROM purch_request_tb r

    LEFT JOIN user_tb u
        ON r.user_id = u.user_id

    LEFT JOIN user_tb p
        ON r.purchaser_id = p.user_id

    LEFT JOIN request_category_tb rc
        ON r.category_id = rc.category_id

    WHERE r.lmr_no = ?

    ORDER BY r.request_id ASC
");

$stmt->bind_param("s", $lmr_no);
$stmt->execute();

$result = $stmt->get_result();

$requests = [];

while ($row = $result->fetch_assoc()) {
    $requests[] = $row;
}

$stmt->close();

if (empty($requests)) {
    echo '<div class="alert alert-danger">Request not found.</div>';
    exit;
}
/*
|--------------------------------------------------------------------------
| GET REQUEST HISTORY / ACTIVITY
|--------------------------------------------------------------------------
*/

$history = [];

$historyStmt = $conn->prepare("
    SELECT
        h.history_id,
        h.request_id,
        h.changed_by,
        h.comment,
        h.changes_json,
        h.date_created,
        u.fullname,
        r.item
    FROM purch_request_history_tb h

    LEFT JOIN user_tb u
        ON h.changed_by = u.user_id

    LEFT JOIN purch_request_tb r
        ON h.request_id = r.request_id

    WHERE h.request_id IN (
        SELECT request_id
        FROM purch_request_tb
        WHERE lmr_no = ?
    )

    ORDER BY h.date_created DESC, h.history_id DESC
");


if (!$historyStmt) {
    die(
        'History query failed: ' .
        $conn->error
    );
}

$historyStmt->bind_param(
    "s",
    $lmr_no
);

$historyStmt->execute();

$historyResult = $historyStmt->get_result();

while ($historyRow = $historyResult->fetch_assoc()) {
    $history[] = $historyRow;
}

$historyStmt->close();


/*
|--------------------------------------------------------------------------
| USE FIRST REQUEST FOR GENERAL INFORMATION
|--------------------------------------------------------------------------
*/

$request = $requests[0];


/*
|--------------------------------------------------------------------------
| STEP 3: COLLECT ALL REQUEST IDS UNDER THIS LMR
|--------------------------------------------------------------------------
*/

$requestIds = [];

foreach ($requests as $row) {
    $requestIds[] = (int)$row['request_id'];
}


/*
|--------------------------------------------------------------------------
| STEP 4: GET ALL ATTACHMENTS FOR THIS LMR
|--------------------------------------------------------------------------
|
| Attachments are currently stored against the first request_id.
| However, this query checks ALL request IDs under the same LMR.
|
*/

$attachments = [];

if (!empty($requestIds)) {

    $placeholders = implode(
        ',',
        array_fill(0, count($requestIds), '?')
    );

    $types = str_repeat('i', count($requestIds));

    $sql = "
        SELECT
            attachment_id,
            request_id,
            file_name,
            file_path,
            date_uploaded
        FROM purch_request_attachments
        WHERE request_id IN ($placeholders)
        ORDER BY date_uploaded DESC, attachment_id DESC
    ";

    $attachStmt = $conn->prepare($sql);

    $attachStmt->bind_param(
        $types,
        ...$requestIds
    );

    $attachStmt->execute();

    $attachResult = $attachStmt->get_result();

    while ($attachment = $attachResult->fetch_assoc()) {
        $attachments[] = $attachment;
    }

    $attachStmt->close();
}

?>

    <?php

                $isPurchasing =
                    strcasecmp(
                        trim($_SESSION['department'] ?? ''),
                        'Purchasing'
                    ) === 0
                    || (
                        strcasecmp(
                            trim($_SESSION['user_type'] ?? ''),
                            'admin'
                        ) === 0
                    );

                /*
                |--------------------------------------------------------------------------
                | PO ATTACHMENTS
                |--------------------------------------------------------------------------
                |
                | Only Purchasing/Admin users can upload PO attachments.
                |
                */

                $poUploadErrors = [];
                $poUploadSuccess = '';

                if (
                    $_SERVER['REQUEST_METHOD'] === 'POST'
                    && isset($_POST['add_po_attachments'])
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | SECURITY CHECK
                    |--------------------------------------------------------------------------
                    */

                    if (!$isPurchasing) {

                        $poUploadErrors[] =
                            'You are not authorized to upload PO attachments.';

                    }

                    /*
                    |--------------------------------------------------------------------------
                    | REQUEST ID VALIDATION
                    |--------------------------------------------------------------------------
                    */

                    $poRequestId =
                        (int)($_POST['request_id'] ?? 0);

                    if (
                        $poRequestId <= 0
                        || $poRequestId !== $request_id
                    ) {

                        $poUploadErrors[] =
                            'Invalid request ID.';

                    }

                    /*
                    |--------------------------------------------------------------------------
                    | FILE VALIDATION
                    |--------------------------------------------------------------------------
                    */

                    if (
                        empty($poUploadErrors)
                        && (
                            !isset($_FILES['po_attachments'])
                            || empty($_FILES['po_attachments']['name'][0])
                        )
                    ) {

                        $poUploadErrors[] =
                            'Please select at least one PO attachment.';

                    }

                    /*
                    |--------------------------------------------------------------------------
                    | MAXIMUM FILE COUNT
                    |--------------------------------------------------------------------------
                    */

                    if (empty($poUploadErrors)) {

                        $poFiles =
                            $_FILES['po_attachments'];

                        $poFileCount =
                            count($poFiles['name']);

                        if ($poFileCount > 10) {

                            $poUploadErrors[] =
                                'You can add a maximum of 10 PO attachments at a time.';

                        }

                    }

                    /*
                    |--------------------------------------------------------------------------
                    | UPLOAD DIRECTORY
                    |--------------------------------------------------------------------------
                    */

                    if (empty($poUploadErrors)) {

                        /*
                        |--------------------------------------------------------------------------
                        | Physical folder
                        |--------------------------------------------------------------------------
                        */

                       $poUploadDir =
                            dirname(__DIR__) .
                            "/uploads/po/";

                        /*
                        |--------------------------------------------------------------------------
                        | Database path
                        |--------------------------------------------------------------------------
                        */

                        $poDbDir =
                            "uploads/po/";

                        if (!is_dir($poUploadDir)) {

                            if (!mkdir($poUploadDir, 0777, true)) {

                                $poUploadErrors[] =
                                    'Unable to create PO attachment directory.';

                            }

                        }

                    }

                    /*
                    |--------------------------------------------------------------------------
                    | ALLOWED FILE TYPES
                    |--------------------------------------------------------------------------
                    */

                    if (empty($poUploadErrors)) {

                        $allowedPoExtensions = [
                            'jpg',
                            'jpeg',
                            'png',
                            'pdf',
                            'doc',
                            'docx',
                            'csv',
                            'xls',
                            'xlsx',
                            'xlsm',
                            'xlsb',
                            'xlt',
                            'xltx',
                            'xltm',
                            'ods'
                        ];

                        $uploadedPoFiles = [];

                        /*
                        |--------------------------------------------------------------------------
                        | VALIDATE ALL FILES FIRST
                        |--------------------------------------------------------------------------
                        */

                        for (
                            $i = 0;
                            $i < $poFileCount;
                            $i++
                        ) {

                            if (
                                empty($poFiles['name'][$i])
                                || $poFiles['error'][$i] === UPLOAD_ERR_NO_FILE
                            ) {
                                continue;
                            }

                            if (
                                $poFiles['error'][$i]
                                !== UPLOAD_ERR_OK
                            ) {

                                $poUploadErrors[] =
                                    'Failed to upload PO file: ' .
                                    $poFiles['name'][$i];

                                break;

                            }

                            $originalName =
                                basename(
                                    $poFiles['name'][$i]
                                );

                            $extension =
                                strtolower(
                                    pathinfo(
                                        $originalName,
                                        PATHINFO_EXTENSION
                                    )
                                );

                            /*
                            |--------------------------------------------------------------------------
                            | CHECK FILE TYPE
                            |--------------------------------------------------------------------------
                            */

                            if (
                                !in_array(
                                    $extension,
                                    $allowedPoExtensions,
                                    true
                                )
                            ) {

                                $poUploadErrors[] =
                                    'Invalid PO file type: ' .
                                    htmlspecialchars($originalName) .
                                    '.';

                                break;

                            }

                            /*
                            |--------------------------------------------------------------------------
                            | UNIQUE FILE NAME
                            |--------------------------------------------------------------------------
                            */

                            $newName =
                                uniqid(
                                    'po_',
                                    true
                                )
                                . '_'
                                . $i
                                . '.'
                                . $extension;

                            $fullPath =
                                $poUploadDir .
                                $newName;

                            $dbPath =
                                $poDbDir .
                                $newName;

                            $uploadedPoFiles[] = [

                                'original_name' =>
                                    $originalName,

                                'tmp_name' =>
                                    $poFiles['tmp_name'][$i],

                                'full_path' =>
                                    $fullPath,

                                'db_path' =>
                                    $dbPath

                            ];

                        }

                    }

                    /*
                    |--------------------------------------------------------------------------
                    | SAVE PO ATTACHMENTS
                    |--------------------------------------------------------------------------
                    */

                    if (
                        empty($poUploadErrors)
                        && !empty($uploadedPoFiles)
                    ) {

                        $conn->begin_transaction();

                        try {

                            $poAttachStmt =
                                $conn->prepare("
                                    INSERT INTO po_attachment
                                    (
                                        request_id,
                                        file_name,
                                        file_path
                                    )
                                    VALUES (?, ?, ?)
                                ");

                            if (!$poAttachStmt) {

                                throw new Exception(
                                    'PO attachment database error: ' .
                                    $conn->error
                                );

                            }

                            foreach (
                                $uploadedPoFiles
                                as $file
                            ) {

                                /*
                                |--------------------------------------------------------------------------
                                | MOVE FILE
                                |--------------------------------------------------------------------------
                                */

                                if (
                                    !move_uploaded_file(
                                        $file['tmp_name'],
                                        $file['full_path']
                                    )
                                ) {

                                    throw new Exception(
                                        'Failed to save PO file: ' .
                                        $file['original_name']
                                    );

                                }

                                /*
                                |--------------------------------------------------------------------------
                                | DATABASE RECORD
                                |--------------------------------------------------------------------------
                                */

                                $poAttachStmt->bind_param(
                                    "iss",
                                    $poRequestId,
                                    $file['original_name'],
                                    $file['db_path']
                                );

                                if (!$poAttachStmt->execute()) {

                                    throw new Exception(
                                        'Failed to save PO attachment: ' .
                                        $file['original_name']
                                    );

                                }

                            }

                            $poAttachStmt->close();

                            $conn->commit();

                            $poUploadSuccess =
                                count($uploadedPoFiles) .
                                ' PO attachment(s) added successfully.';

                        } catch (Exception $e) {

                            $conn->rollback();

                            /*
                            |--------------------------------------------------------------------------
                            | REMOVE FILES IF DATABASE SAVE FAILED
                            |--------------------------------------------------------------------------
                            */

                            foreach (
                                $uploadedPoFiles
                                as $file
                            ) {

                                if (
                                    file_exists(
                                        $file['full_path']
                                    )
                                ) {

                                    @unlink(
                                        $file['full_path']
                                    );

                                }

                            }

                            $poUploadErrors[] =
                                $e->getMessage();

                        }

                    }

                }
                /*
                    |--------------------------------------------------------------------------
                    | GET PO ATTACHMENTS
                    |--------------------------------------------------------------------------
                    |
                    | Only Purchasing/Admin can retrieve PO attachments.
                    |
                    */

                    $poAttachments = [];

                    if ($isPurchasing && !empty($requestIds)) {

                        $placeholders = implode(
                            ',',
                            array_fill(
                                0,
                                count($requestIds),
                                '?'
                            )
                        );

                        $types =
                            str_repeat(
                                'i',
                                count($requestIds)
                            );

                        $sql = "
                            SELECT
                                attachment_id,
                                request_id,
                                file_name,
                                file_path,
                                date_uploaded
                            FROM po_attachment
                            WHERE request_id IN ($placeholders)
                            ORDER BY date_uploaded DESC,
                                    attachment_id DESC
                        ";

                        $poStmt =
                            $conn->prepare($sql);

                        if ($poStmt) {

                            $poStmt->bind_param(
                                $types,
                                ...$requestIds
                            );

                            $poStmt->execute();

                            $poResult =
                                $poStmt->get_result();

                            while (
                                $poRow =
                                    $poResult->fetch_assoc()
                            ) {

                                $poAttachments[] =
                                    $poRow;

                            }

                            $poStmt->close();

                        }

                    }   
    ?>
<style>

.request-label {
    font-size: 0.78rem;
    font-weight: 600;
    color: #6c757d;
    margin-bottom: 2px;
}

.request-value {
    font-size: 0.95rem;
    color: #212529;
    margin-bottom: 15px;
    word-break: break-word;
}

.item-card {
    /* border-left: 3px solid #0d6efd; */
    margin-bottom: 8px !important;
}

.item-card .card-body {
    padding: 10px 12px;
}

.item-card .request-label {
    font-size: 0.72rem;
    margin-bottom: 1px;
}

.item-card .request-value {
    font-size: 0.88rem;
    margin-bottom: 8px;
    line-height: 1.3;
}

.item-card .row {
    --bs-gutter-x: 0.75rem;
    --bs-gutter-y: 0;
}
.item-card {
    /* border-left: 3px solid #0d6efd; */
    border-radius: 6px;
    transition: all 0.15s ease-in-out;
}

.item-card:hover {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.item-card .card-body {
    padding: 12px 14px;
}

.item-card .request-label {
    font-size: 0.70rem;
    font-weight: 600;
    color: #6c757d;
    margin-bottom: 1px;
}

.item-card .request-value {
    font-size: 0.85rem;
    color: #212529;
    margin-bottom: 7px;
    line-height: 1.3;
    word-break: break-word;
}

.item-card .badge {
    font-size: 0.70rem;
}

.attachment-card {
    transition: all 0.15s ease-in-out;
}

.attachment-card:hover {
    background-color: #f8f9fa;
    transform: translateY(-1px);
}

.activity-empty {
    min-height: 250px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #adb5bd;
    text-align: center;
}
.activity-list {
    max-height: 750px;
    overflow-y: auto;
    padding-right: 5px;
}

.activity-item {
    font-size: 0.88rem;
}

.activity-item .border-start {
    border-width: 3px !important;
}

.activity-item hr {
    border-color: #e9ecef;
}

.activity-item .bg-light {
    background-color: #f8f9fa !important;
}

.bg-purple {
    background-color: #6f42c1 !important;
    color: #fff !important;
}
.attachment-date-title {
    font-size: 0.78rem;
    font-weight: 700;
    color: #6c757d;
    letter-spacing: 0.5px;
    white-space: nowrap;
}

.attachment-date-group {
    margin-bottom: 20px;
}

.attachment-date-group hr {
    border-color: #dee2e6;
    opacity: 1;
}

</style>

<?php
$isPurchasing =
    strcasecmp(
        trim($_SESSION['department'] ?? ''),
        'Purchasing'
    ) === 0
    || (
        strcasecmp(
            trim($_SESSION['user_type'] ?? ''),
            'admin'
        ) === 0
    );
?>

<?php

/*
|--------------------------------------------------------------------------
| PROCESS GOODS RECEIVED CONFIRMATION
|--------------------------------------------------------------------------
*/

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['goods_received_confirmation'])
) {

    $requestId = (int)(
        $_POST['request_id'] ?? 0
    );


    $goodsReceived = strtolower(
        trim(
            $_POST['goods_received'] ?? ''
        )
    );


    $comment = trim(
        $_POST['goods_received_comment'] ?? ''
    );


    /*
    |--------------------------------------------------------------------------
    | GET LOGGED-IN USER
    |--------------------------------------------------------------------------
    |
    | Change this if your session variable uses
    | a different name.
    |
    */

    $changedBy = (int)(
        $_SESSION['user_id'] ?? 0
    );


    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if ($requestId <= 0) {

        $_SESSION['error'] =
            'Invalid request ID.';

    } elseif (!in_array(
        $goodsReceived,
        ['yes', 'no'],
        true
    )) {

        $_SESSION['error'] =
            'Please select Yes or No.';

    } elseif ($comment === '') {

        $_SESSION['error'] =
            'Please enter your comments.';

    } elseif ($changedBy <= 0) {

        $_SESSION['error'] =
            'Unable to identify the logged-in user.';

    } else {

        /*
        |--------------------------------------------------------------------------
        | VERIFY REQUEST EXISTS AND IS GOODS RECEIVED
        |--------------------------------------------------------------------------
        */

        $checkStmt = $conn->prepare("
            SELECT
                request_id,
                order_status
            FROM purch_request_tb
            WHERE request_id = ?
            LIMIT 1
        ");


        if (!$checkStmt) {

            $_SESSION['error'] =
                'Unable to verify the purchasing request.';

            error_log(
                'Goods Received Confirmation: ' .
                'Prepare failed: ' .
                $conn->error
            );

        } else {

            $checkStmt->bind_param(
                "i",
                $requestId
            );


            if (!$checkStmt->execute()) {

                $_SESSION['error'] =
                    'Unable to verify the purchasing request.';

                error_log(
                    'Goods Received Confirmation: ' .
                    'Execute failed: ' .
                    $checkStmt->error
                );

                $checkStmt->close();

            } else {

                $result =
                    $checkStmt->get_result();

                $requestCheck =
                    $result->fetch_assoc();

                $checkStmt->close();


                if (!$requestCheck) {

                    $_SESSION['error'] =
                        'Purchasing request not found.';

                } elseif (
                    strtolower(
                        trim(
                            $requestCheck['order_status'] ?? ''
                        )
                    ) !== 'goods received'
                ) {

                    $_SESSION['error'] =
                        'This request is no longer marked as Goods Received.';

                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | CHANGES JSON
                    |--------------------------------------------------------------------------
                    */

                    $changes = [

                        'goods_received' => [

                            'old' => 'pending confirmation',

                            'new' => $goodsReceived

                        ]

                    ];


                    $changesJson = json_encode(
                        $changes,
                        JSON_UNESCAPED_UNICODE |
                        JSON_UNESCAPED_SLASHES
                    );


                    if ($changesJson === false) {

                        $_SESSION['error'] =
                            'Unable to prepare confirmation data.';

                    } else {

                        /*
                        |--------------------------------------------------------------------------
                        | INSERT HISTORY
                        |--------------------------------------------------------------------------
                        */

                        $historyStmt = $conn->prepare("
                            INSERT INTO purch_request_history_tb
                            (
                                request_id,
                                bulk_group_id,
                                changed_by,
                                comment,
                                changes_json,
                                date_created
                            )
                            VALUES
                            (
                                ?,
                                0,
                                ?,
                                ?,
                                ?,
                                NOW()
                            )
                        ");


                        if (!$historyStmt) {

                            $_SESSION['error'] =
                                'Unable to save the confirmation.';

                            error_log(
                                'Goods Received Confirmation: ' .
                                'History prepare failed: ' .
                                $conn->error
                            );

                        } else {

                            $historyStmt->bind_param(
                                "iiss",
                                $requestId,
                                $changedBy,
                                $comment,
                                $changesJson
                            );


                            if ($historyStmt->execute()) {

                                $_SESSION['success'] =
                                    'Goods received confirmation submitted successfully.';

                            } else {

                                $_SESSION['error'] =
                                    'Unable to save the confirmation.';

                                error_log(
                                    'Goods Received Confirmation: ' .
                                    'History insert failed: ' .
                                    $historyStmt->error
                                );
                            }


                            $historyStmt->close();
                        }
                    }
                }
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | REDIRECT
    |--------------------------------------------------------------------------
    |
    | Prevent duplicate form submission if the user
    | refreshes the page.
    |
    */

echo "<script>
    window.location.href = '?page=ticket/view_purch_request&request_id={$requestId}';
</script>";

exit;
    exit;
}
?>
<div class="card">

            <!-- =====================================================
                HEADER
            ====================================================== -->

            <div class="card-header d-flex justify-content-between align-items-center text-white">

                <span>
                    <i class="fas fa-file-alt me-2"></i>
                    Purchasing Request
                </span>

                <div class="d-flex gap-2">

                    <!-- Export Activity Logs -->
                    <?php if ($isPurchasing): ?>

                        <a
                            href="ticket/export_purch_activity.php?request_id=<?= (int)$request_id ?>"
                            class="btn btn-success btn-sm"
                        >
                            <i class="fas fa-file-csv me-1"></i>
                            Export Activity CSV
                        </a>

                    <?php endif; ?>
                    <!-- Back -->
                    <a
                        href="?page=ticket/purch_lmr"
                        class="btn btn-secondary btn-sm"
                    >
                        <i class="fas fa-arrow-left me-1"></i>
                        Back to Requests
                    </a>

                </div>

            </div>



    <!-- =====================================================
         MAIN BODY
    ====================================================== -->

    <div class="card-body">
        <div class="row g-4">
            <!-- =================================================
                 LEFT CARD
            ================================================== -->

            <div class="col-lg-8">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-light">
                        <strong>
                            <i class="fas fa-info-circle me-2"></i>
                            Request Details
                        </strong>
                    </div>
                    <div class="card-body">


                    <!-- =====================================
                            GENERAL INFORMATION
                    ====================================== -->

                        <div class="row">
                        <!-- REQUEST ID -->
                        <!-- <div class="col-md-6">

                            <div class="request-label">
                                Request ID
                            </div>

                            <div class="request-value fw-bold">
                                <?= (int)$request['request_id'] ?>
                            </div>

                        </div> -->
                        <!-- LMR -->
                        <div class="col-md-6">

                            <div class="request-label">
                                LMR No.
                            </div>

                            <div class="request-value fw-bold">
                                <?= htmlspecialchars($lmr_no) ?>
                            </div>

                        </div>


                        <!-- REQUESTOR -->
                        <div class="col-md-6">

                            <div class="request-label">
                                Requestor
                            </div>

                            <div class="request-value">
                                <?= htmlspecialchars(
                                    $request['fullname']
                                    ?: $request['requestor']
                                    ?: ''
                                ) ?>
                            </div>

                        </div>


                        <!-- COMPANY -->
                        <div class="col-md-6">

                            <div class="request-label">
                                Company
                            </div>

                            <div class="request-value">
                                <?= htmlspecialchars(
                                    $request['company'] ?? ''
                                ) ?>
                            </div>

                        </div>


                        <!-- DEPARTMENT -->
                        <div class="col-md-6">

                            <div class="request-label">
                                Department
                            </div>

                            <div class="request-value">
                                <?= htmlspecialchars(
                                    $request['department'] ?? ''
                                ) ?>
                            </div>

                        </div>


                        <!-- PURCHASER -->
                        <div class="col-md-6">

                            <div class="request-label">
                                Assigned Purchaser
                            </div>

                            <div class="request-value">

                                <?= htmlspecialchars(
                                    $request['purchaser_name']
                                    ?: 'Unassigned'
                                ) ?>

                            </div>

                        </div>


                        <!-- STATUS -->
                        <div class="col-md-3">

                            <div class="request-label">
                                Status
                            </div>

                            <div class="request-value">

                                <?php
                                $status = strtolower(
                                    trim($request['status'] ?? '')
                                );

                                $statusClass = match ($status) {

                                    'pending' =>
                                        'bg-warning text-dark',

                                    'checking requirements' =>
                                        'bg-info text-dark',

                                    'canvassing' =>
                                        'bg-primary',

                                    'negotiation' =>
                                        'bg-purple',

                                    'draft po under discussion' =>
                                        'bg-warning text-dark',

                                    'draft po approved' =>
                                        'bg-success',

                                    'final po approved' =>
                                        'bg-success',

                                    'rejected' =>
                                        'bg-danger',

                                    'closed' =>
                                        'bg-secondary',

                                    default =>
                                        'bg-secondary'
                                };
                                ?>

                                <span class="badge <?= $statusClass ?>">
                                    <?= htmlspecialchars(
                                        formatStatus($status)
                                    ) ?>
                                </span>

                            </div>

                        </div>


                        <!-- PRIORITY -->
                        <div class="col-md-3">

                            <div class="request-label">
                                Priority
                            </div>

                            <div class="request-value">

                                <?php
                                $priority =
                                    strtolower(
                                        trim(
                                            $request['priority'] ?? ''
                                        )
                                    );

                                $priorityClass = 'bg-secondary';

                                if ($priority === 'urgent') {
                                    $priorityClass = 'bg-danger';
                                } elseif ($priority === 'high') {
                                    $priorityClass = 'bg-warning text-dark';
                                } elseif ($priority === 'medium') {
                                    $priorityClass = 'bg-info text-dark';
                                }
                                ?>

                                <span class="badge <?= $priorityClass ?>">

                                    <?= htmlspecialchars(
                                        ucfirst($priority)
                                    ) ?>

                                </span>

                            </div>

                        </div>


                        <!-- PO NUMBER -->
                        <div class="col-md-6">

                            <div class="request-label">
                                PO Number
                            </div>

                            <div class="request-value">

                                <?= !empty($request['po_no'])
                                    ? htmlspecialchars($request['po_no'])
                                    : '<span class="text-muted">Not yet assigned</span>'
                                ?>

                            </div>

                        </div>


                        <!-- DATE CREATED -->
                        <div class="col-md-6">

                            <div class="request-label">
                                Date Created
                            </div>

                            <div class="request-value">

                                <?= htmlspecialchars(
                                    $request['date_created'] ?? ''
                                ) ?>

                            </div>

                        </div>

                    </div>
<?php
/*
|--------------------------------------------------------------------------
| GOODS RECEIVED CONFIRMATION
|--------------------------------------------------------------------------
*/

$orderStatus = strtolower(
    trim($request['order_status'] ?? '')
);

if ($orderStatus === 'goods received'):
?>

    <div class="alert alert-success mt-4">

        <div class="d-flex justify-content-between align-items-center">

            <div>

                <strong>
                    <i class="fas fa-box-open me-2"></i>
                    Goods Received
                </strong>

                <div class="small mt-1">
                    Please confirm whether you received the item.
                </div>

            </div>

            <button
                type="button"
                class="btn btn-success"
                data-bs-toggle="modal"
                data-bs-target="#goodsReceivedModal"
            >
                <i class="fas fa-check-circle me-1"></i>
                Confirm Goods Received
            </button>

        </div>

    </div>

<?php endif; ?>
                    <hr><br><br>
                    <!-- =====================================
                            ITEMS
                    ====================================== -->

                        <!-- Requested Items -->
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-boxes me-2"></i>
                                    Requested Items
                                    <span class="badge bg-secondary">
                                        <?= count($requests) ?>
                                    </span>
                                </h5>
                            </div>

                            <div class="card-body">

                                <?php if (!empty($requests)): ?>

                                    <div class="row g-3" id="itemsContainer">

                                        <?php foreach ($requests as $index => $item): ?>

                                            <?php
                                            // Show first 4 items by default
                                            $isHidden = $index >= 4;
                                            ?>

                        <div class="col-md-6 item-wrapper <?= $isHidden ? 'd-none extra-item' : '' ?>">
                            <div class="card item-card h-100">
                                <div class="card-body">

                                    <?php
                                    $status = strtolower(trim($item['status'] ?? ''));
                                    $orderStatus = strtolower(trim($item['order_status'] ?? ''));
                                    $priority = strtolower(trim($item['priority'] ?? ''));

                                    /*
                                    |--------------------------------------------------------------------------
                                    | STATUS CLASS
                                    |--------------------------------------------------------------------------
                                    */
                                    $statusClass = match ($status) {
                                        'pending' =>
                                            'bg-warning text-dark',

                                        'checking requirements' =>
                                            'bg-info text-dark',

                                        'canvassing' =>
                                            'bg-primary',

                                        'negotiation' =>
                                            'bg-purple',

                                        'draft po under discussion' =>
                                            'bg-warning text-dark',

                                        'draft po approved' =>
                                            'bg-success',

                                        'final po approved' =>
                                            'bg-success',

                                        'rejected' =>
                                            'bg-danger',

                                        'closed' =>
                                            'bg-secondary',

                                        default =>
                                            'bg-secondary'
                                    };

                                    /*
                                    |--------------------------------------------------------------------------
                                    | ORDER STATUS CLASS
                                    |--------------------------------------------------------------------------
                                    */
                                    $orderStatusClass = match ($orderStatus) {
                                        'order acknowledged' =>
                                            'bg-info text-dark',

                                        'goods received' =>
                                            'bg-success',

                                        'payment processing' =>
                                            'bg-warning text-dark',

                                        'payment issued' =>
                                            'bg-success',

                                        'closed' =>
                                            'bg-secondary',

                                        'n/a',
                                        'na',
                                        '' =>
                                            'bg-secondary',

                                        default =>
                                            'bg-secondary'
                                    };

                                    /*
                                    |--------------------------------------------------------------------------
                                    | PRIORITY CLASS
                                    |--------------------------------------------------------------------------
                                    */
                                    $priorityClass = match ($priority) {
                                        'urgent' =>
                                            'bg-danger',

                                        'high' =>
                                            'bg-warning text-dark',

                                        'medium' =>
                                            'bg-info text-dark',

                                        default =>
                                            'bg-secondary'
                                    };
                                    ?>

                                    <!-- ITEM HEADER -->
                                    <div class="d-flex justify-content-between align-items-start mb-3">

                                        <div class="fw-semibold text-dark">
                                            <?= htmlspecialchars($item['item']) ?>
                                        </div>

                                        <span class="badge bg-light text-secondary">
                                            #<?= $index + 1 ?>
                                        </span>

                                    </div>


                                    <!-- QUANTITY / UOM -->
                                    <div class="row g-3 mb-2">

                                        <div class="col-6">
                                            <div class="request-label">
                                                Quantity
                                            </div>

                                            <div class="request-value">
                                                <?= htmlspecialchars($item['quantity']) ?>
                                            </div>
                                        </div>

                                        <div class="col-6">
                                            <div class="request-label">
                                                UoM
                                            </div>

                                            <div class="request-value">
                                                <?= htmlspecialchars($item['UoM']) ?>
                                            </div>
                                        </div>

                                    </div>


                                    <!-- CATEGORY / PO NUMBER -->
                                    <div class="row g-3 mb-2">

                                        <div class="col-6">

                                            <div class="request-label">
                                                Category
                                            </div>

                                            <div class="request-value">
                                                <?= htmlspecialchars(
                                                    $item['category_name'] ?? 'N/A'
                                                ) ?>
                                            </div>

                                        </div>

                                        <div class="col-6">

                                            <div class="request-label">
                                                PO Number
                                            </div>

                                            <div class="request-value">

                                                <?php if (!empty($item['po_no'])): ?>

                                                    <?= htmlspecialchars($item['po_no']) ?>

                                                <?php else: ?>

                                                    <span class="text-muted">
                                                        Not yet assigned
                                                    </span>

                                                <?php endif; ?>

                                            </div>

                                        </div>

                                    </div>


                                    <!-- DATE NEEDED / PRIORITY -->
                                    <div class="row g-3 mb-2">

                                        <div class="col-6">

                                            <div class="request-label">
                                                Date Needed
                                            </div>

                                            <div class="request-value">

                                                <?= !empty($item['date_needed'])
                                                    ? date('M d, Y', strtotime($item['date_needed']))
                                                    : '-'
                                                ?>

                                            </div>

                                        </div>

                                        <div class="col-6">

                                            <div class="request-label">
                                                Priority
                                            </div>

                                            <div class="request-value">

                                                <span class="badge <?= $priorityClass ?>">
                                                    <?= htmlspecialchars(
                                                        ucfirst($priority ?: 'N/A')
                                                    ) ?>
                                                </span>

                                            </div>

                                        </div>

                                    </div>


                                    <!-- STATUS / ORDER STATUS -->
                                    <div class="row g-3 mb-3">

                                        <div class="col-6">

                                            <div class="request-label">
                                                Status
                                            </div>

                                            <div class="request-value">

                                                <span class="badge <?= $statusClass ?>">
                                                    <?= htmlspecialchars(
                                                        formatStatus($status ?: 'n/a')
                                                    ) ?>
                                                </span>

                                            </div>

                                        </div>

                                        <div class="col-6">

                                            <div class="request-label">
                                                Order Status
                                            </div>

                                            <div class="request-value">

                                                <span class="badge <?= $orderStatusClass ?>">
                                                    <?= htmlspecialchars(
                                                        formatStatus($orderStatus ?: 'n/a')
                                                    ) ?>
                                                </span>

                                            </div>

                                        </div>

                                    </div>
                                    <div class="row g-3 mb-3">
                                        <div class="col-6">
                                        <!-- DESCRIPTION -->
                                        <?php if (!empty($item['description'])): ?>

                                            <div class="request-label">
                                                Description
                                            </div>

                                            <div class="request-value mb-3">
                                                <?= nl2br(htmlspecialchars($item['description'])) ?>
                                            </div>

                                        <?php endif; ?>
                                        </div>
                                        <div class="col-6">
                                        <!-- REMARKS -->
                                        <?php if (!empty($item['remarks'])): ?>

                                            <div class="request-label">
                                                Remarks
                                            </div>

                                            <div class="request-value">
                                                <?= nl2br(htmlspecialchars($item['remarks'])) ?>
                                            </div>

                                        <?php endif; ?>
                                        </div>
                                     </div>
                                </div>
                            </div>
                        </div>

                                <?php endforeach; ?>

                            </div>

                            <?php if (count($requests) > 4): ?>

                                <div class="text-center mt-3">

                                    <button
                                        type="button"
                                        class="btn btn-outline-primary btn-sm"
                                        id="showAllItemsBtn"
                                    >
                                        <i class="fas fa-chevron-down me-1"></i>
                                        Show All Items
                                    </button>

                                </div>

                            <?php endif; ?>

                                    <?php else: ?>

                                        <div class="text-muted text-center py-4">
                                            No requested items found.
                                        </div>

                                    <?php endif; ?>

                                </div>
                            </div>


                                <!-- =====================================
                                    ATTACHMENTS
                                ====================================== -->

                                <hr class="mt-4">

                                <div class="d-flex justify-content-between align-items-center mb-3">

                                    <h6 class="mb-0 fw-bold">
                                        <i class="fas fa-paperclip me-2"></i>
                                        Attachments
                                    </h6>

                                    <div class="d-flex align-items-center gap-2">

                                        <span class="badge bg-secondary">
                                            <?= count($attachments) ?>
                                        </span>

                                        <?php if (!$isPurchasing): ?>

                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-primary"
                                                data-bs-toggle="modal"
                                                data-bs-target="#addAttachmentsModal"
                                            >
                                                <i class="fas fa-plus me-1"></i>
                                                Add Attachments
                                            </button>

                                        <?php endif; ?>

                                        <?php if (!empty($attachments)): ?>

                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-success"
                                                id="downloadAllAttachments"
                                            >
                                                <i class="fas fa-download me-1"></i>
                                                Download All
                                            </button>

                                        <?php endif; ?>

                                    </div>

                                </div>


                        <?php if (empty($attachments)): ?>

                            <div class="text-muted small">
                                <i class="fas fa-info-circle me-1"></i>
                                No attachments for this LMR.
                            </div>

                            <?php else: ?>

                            <?php
                            /*
                            |--------------------------------------------------------------------------
                            | GROUP ATTACHMENTS BY UPLOAD DATE
                            |--------------------------------------------------------------------------
                            */

                            $groupedAttachments = [];

                            foreach ($attachments as $attachment) {

                                $uploadDate = !empty($attachment['date_uploaded'])
                                    ? date('Y-m-d', strtotime($attachment['date_uploaded']))
                                    : 'unknown';

                                $groupedAttachments[$uploadDate][] = $attachment;
                            }
                            ?>

                            <?php foreach ($groupedAttachments as $uploadDate => $dateAttachments): ?>

                                <!-- DATE GROUP -->

                                <div class="attachment-date-group mb-4">

                                    <!-- DATE HEADER -->

                                    <div class="d-flex align-items-center mb-2">

                                        <div class="attachment-date-title">

                                            <?php if ($uploadDate !== 'unknown'): ?>

                                                <?= strtoupper(
                                                    date(
                                                        'M d, Y',
                                                        strtotime($uploadDate)
                                                    )
                                                ) ?>

                                            <?php else: ?>

                                                DATE UNKNOWN

                                            <?php endif; ?>

                                        </div>

                                        <div class="flex-grow-1 ms-3">
                                            <hr class="my-0">
                                        </div>

                                    </div>


                                    <!-- ATTACHMENT GRID -->

                                    <div class="row g-2">

                                        <?php foreach ($dateAttachments as $attachment): ?>

                                            <?php

                                            $fileName =
                                                $attachment['file_name'];

                                            $extension =
                                                strtolower(
                                                    pathinfo(
                                                        $fileName,
                                                        PATHINFO_EXTENSION
                                                    )
                                                );

                                            switch ($extension) {

                                                case 'jpg':
                                                case 'jpeg':
                                                case 'png':

                                                    $icon = 'fa-file-image';
                                                    $iconClass = 'text-success';

                                                    break;

                                                case 'pdf':

                                                    $icon = 'fa-file-pdf';
                                                    $iconClass = 'text-danger';

                                                    break;

                                                case 'doc':
                                                case 'docx':

                                                    $icon = 'fa-file-word';
                                                    $iconClass = 'text-primary';

                                                    break;

                                                default:

                                                    $icon = 'fa-file';
                                                    $iconClass = 'text-secondary';
                                            }

                                            ?>

                                            <!-- GRID ITEM -->

                                            <div class="col-md-3">

                                                <a
                                                    href="ticket/preview_purch_attachment.php?attachment_id=<?= (int)$attachment['attachment_id'] ?>"
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    class="text-decoration-none"
                                                >

                                                    <div class="card attachment-card h-100">

                                                        <div class="card-body py-2 px-3">

                                                            <div class="d-flex align-items-center">

                                                                <!-- SMALL ICON -->

                                                                <i
                                                                    class="fas <?= $icon ?> <?= $iconClass ?> me-2"
                                                                    style="
                                                                        font-size:1.15rem;
                                                                        flex-shrink:0;
                                                                    "
                                                                ></i>


                                                                <!-- FILE INFORMATION -->

                                                                <div
                                                                    class="flex-grow-1"
                                                                    style="min-width:0;"
                                                                >

                                                                    <div
                                                                        class="fw-semibold text-dark"
                                                                        style="
                                                                            word-break:break-word;
                                                                            font-size:0.85rem;
                                                                        "
                                                                    >

                                                                        <?= htmlspecialchars(
                                                                            $fileName
                                                                        ) ?>

                                                                    </div>

                                                                    <small
                                                                        class="text-muted"
                                                                        style="font-size:0.72rem;"
                                                                    >

                                                                        <?= strtoupper(
                                                                            $extension
                                                                        ) ?>

                                                                        ·

                                                                        <?= !empty(
                                                                            $attachment['date_uploaded']
                                                                        )
                                                                            ? date(
                                                                                'h:i A',
                                                                                strtotime(
                                                                                    $attachment['date_uploaded']
                                                                                )
                                                                            )
                                                                            : 'Time unknown'
                                                                        ?>

                                                                    </small>

                                                                </div>


                                                                <!-- ARROW -->

                                                                <i
                                                                    class="fas fa-chevron-right text-muted ms-2"
                                                                    style="font-size:0.65rem;"
                                                                ></i>

                                                            </div>

                                                        </div>

                                                    </div>

                                                </a>

                                            </div>

                                        <?php endforeach; ?>

                                    </div>

                                </div>

                            <?php endforeach; ?>

                        <?php endif; ?>

                </div>

            </div>

        </div>
            <!-- =================================================
                RIGHT CARD
            ================================================== -->

            <div class="col-lg-4">

                <div class="card h-100 shadow-sm">

                    <div class="card-header bg-light">

                        <strong>

                            <i class="fas fa-history me-2"></i>

                            Activity / Comments

                        </strong>

                    </div>


                    <div class="card-body">

                        <?php if (empty($history)): ?>

                            <div class="activity-empty">

                                <div>

                                    <i class="fas fa-comments fa-2x mb-3"></i>

                                    <div class="fw-semibold">
                                        No activity yet
                                    </div>

                                    <small>
                                        Comments, status updates and activity logs
                                        will appear here.
                                    </small>

                                </div>

                            </div>

                        <?php else: ?>

                            <div class="activity-list">

                                <?php foreach ($history as $activity): ?>

                                    <?php

                                    $changes = [];

                                    if (!empty($activity['changes_json'])) {

                                        $decodedChanges = json_decode(
                                            $activity['changes_json'],
                                            true
                                        );

                                        if (is_array($decodedChanges)) {
                                            $changes = $decodedChanges;
                                        }
                                    }

                                    ?>

                                    <div class="activity-item mb-4">

                                        <!-- USER / DATE -->

                                        <div class="d-flex align-items-start">

                                            <div
                                                class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2"
                                                style="width:36px;height:36px;flex-shrink:0;"
                                            >

                                                <i class="fas fa-user"></i>

                                            </div>

                                            <div class="flex-grow-1">

                                                <div class="fw-semibold">

                                                    <?= htmlspecialchars(
                                                        $activity['fullname']
                                                        ?: 'Unknown User'
                                                    ) ?>

                                                </div>

                                                <small class="text-muted">

                                                    <?= !empty($activity['date_created'])
                                                        ? date(
                                                            'M d, Y h:i A',
                                                            strtotime($activity['date_created'])
                                                        )
                                                        : ''
                                                    ?>

                                                </small>

                                            </div>

                                        </div> <br>

                                        <div class="small text-muted mt-1">

                                            <span class="me-3">

                                                <i class="fas fa-hashtag me-1"></i>

                                                Request ID:

                                                <strong>
                                                    <?= (int)$activity['request_id'] ?>
                                                </strong>

                                            </span>


                                            <!-- ITEM -->

                                            <span>

                                                <i class="fas fa-box me-1"></i>

                                                Item:

                                                <strong>
                                                    <?= htmlspecialchars(
                                                        $activity['item']
                                                        ?: 'N/A'
                                                    ) ?>
                                                </strong>

                                            </span>

                                        </div>

                                        <!-- CHANGES -->

                                        <?php if (!empty($changes)): ?>

                                            <div class="mt-3">

                                                <?php foreach ($changes as $field => $change): ?>

                                                    <?php

                                                    $fieldLabel = match ($field) {

                                                        'status' =>
                                                            'Status',

                                                        'purchaser' =>
                                                            'Assigned Purchaser',

                                                        'priority' =>
                                                            'Priority',

                                                        'category' =>
                                                            'Category',

                                                        'order_status' =>
                                                            'Order Status',

                                                        'po_no' =>
                                                            'PO Number',

                                                        default =>
                                                            ucwords(
                                                                str_replace(
                                                                    '_',
                                                                    ' ',
                                                                    $field
                                                                )
                                                            )
                                                    };

                                                    $oldValue =
                                                        $change['old'] ?? '';

                                                    $newValue =
                                                        $change['new'] ?? '';

                                                    ?>

                                                    <div class="border-start border-3 border-primary ps-3 mb-3">

                                                        <div class="small text-muted mb-1">

                                                            <?= htmlspecialchars(
                                                                $fieldLabel
                                                            ) ?>

                                                        </div>

                                                        <div>

                                                            <span class="text-muted">
                                                                <?= htmlspecialchars(
                                                                    $oldValue ?: 'N/A'
                                                                ) ?>
                                                            </span>

                                                            <i class="fas fa-arrow-right mx-2 text-primary"></i>

                                                            <strong>
                                                                <?= htmlspecialchars(
                                                                    $newValue ?: 'N/A'
                                                                ) ?>
                                                            </strong>

                                                        </div>

                                                    </div>

                                                <?php endforeach; ?>

                                            </div>

                                        <?php endif; ?>


                                        <!-- COMMENT -->

                                        <?php if (!empty(trim($activity['comment'] ?? ''))): ?>

                                            <div class="mt-2 p-3 bg-light rounded">

                                                <div class="small text-muted mb-1">

                                                    <i class="fas fa-comment me-1"></i>
                                                    Comment

                                                </div>

                                                <div>

                                                    <?= nl2br(
                                                        htmlspecialchars(
                                                            $activity['comment']
                                                        )
                                                    ) ?>

                                                </div>

                                            </div>

                                        <?php endif; ?>

                                    </div>

                                    <?php if (
                                        $activity !== end($history)
                                    ): ?>

                                        <hr>

                                    <?php endif; ?>

                                <?php endforeach; ?>

                            </div>

                        <?php endif; ?>

               <!-- // PO ATTACHMENT -->
                <?php if ($isPurchasing): ?>

                    <hr class="mt-4">

                    <div class="d-flex justify-content-between align-items-center mb-3">

                        <h6 class="mb-0 fw-bold">
                            <i class="fas fa-file-invoice-dollar me-2"></i>
                            PO Attachments
                        </h6>

                        <div class="d-flex align-items-center gap-2">

                            <span class="badge bg-primary">
                                <?= count($poAttachments) ?>
                            </span>

                            <button
                                type="button"
                                class="btn btn-sm btn-outline-primary"
                                data-bs-toggle="modal"
                                data-bs-target="#addPOAttachmentsModal"
                            >
                                <i class="fas fa-plus me-1"></i>
                                Add PO Attachments
                            </button>

                        </div>

                    </div>


                    <?php if (empty($poAttachments)): ?>

                        <div class="text-muted small">

                            <i class="fas fa-info-circle me-1"></i>
                            No PO attachments for this LMR.

                        </div>

                    <?php else: ?>

                        <?php
                        /*
                        |--------------------------------------------------------------------------
                        | GROUP PO ATTACHMENTS BY UPLOAD DATE
                        |--------------------------------------------------------------------------
                        */

                        $groupedPoAttachments = [];

                        foreach ($poAttachments as $poAttachment) {

                            $uploadDate = !empty($poAttachment['date_uploaded'])
                                ? date(
                                    'Y-m-d',
                                    strtotime(
                                        $poAttachment['date_uploaded']
                                    )
                                )
                                : 'unknown';

                            $groupedPoAttachments[$uploadDate][] =
                                $poAttachment;
                        }
                        ?>


                        <?php foreach ($groupedPoAttachments as $uploadDate => $datePoAttachments): ?>

                            <!-- DATE HEADER -->

                            <div class="attachment-date-group mb-4">

                                <div class="d-flex align-items-center mb-2">

                                    <div class="attachment-date-title">

                                        <?php if ($uploadDate !== 'unknown'): ?>

                                            <?= strtoupper(
                                                date(
                                                    'M d, Y',
                                                    strtotime($uploadDate)
                                                )
                                            ) ?>

                                        <?php else: ?>

                                            DATE UNKNOWN

                                        <?php endif; ?>

                                    </div>

                                    <div class="flex-grow-1 ms-3">

                                        <hr class="my-0">

                                    </div>

                                </div>


                                <!-- PO ATTACHMENTS FOR THIS DATE -->

                                <div class="row">

                                    <?php foreach ($datePoAttachments as $poAttachment): ?>

                                        <?php

                                        $poFileName =
                                            $poAttachment['file_name'];

                                        $poExtension =
                                            strtolower(
                                                pathinfo(
                                                    $poFileName,
                                                    PATHINFO_EXTENSION
                                                )
                                            );


                                        /*
                                        |--------------------------------------------------------------------------
                                        | FILE ICON
                                        |--------------------------------------------------------------------------
                                        */

                                        switch ($poExtension) {

                                            case 'jpg':
                                            case 'jpeg':
                                            case 'png':

                                                $poIcon =
                                                    'fa-file-image';

                                                $poIconClass =
                                                    'text-success';

                                                break;


                                            case 'pdf':

                                                $poIcon =
                                                    'fa-file-pdf';

                                                $poIconClass =
                                                    'text-danger';

                                                break;


                                            case 'doc':
                                            case 'docx':

                                                $poIcon =
                                                    'fa-file-word';

                                                $poIconClass =
                                                    'text-primary';

                                                break;


                                            default:

                                                $poIcon =
                                                    'fa-file';

                                                $poIconClass =
                                                    'text-secondary';

                                        }

                                        ?>


                                        <div class="col-md-6 mb-2">

                                            <a
                                                href="ticket/preview_po_attachment.php?attachment_id=<?= (int)$poAttachment['attachment_id'] ?>"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="text-decoration-none"
                                            >

                                                <div class="card attachment-card">

                                                    <div class="card-body py-2">

                                                        <div class="d-flex align-items-center">


                                                            <!-- SMALLER ICON -->

                                                            <i
                                                                class="
                                                                    fas
                                                                    <?= $poIcon ?>
                                                                    <?= $poIconClass ?>
                                                                    me-3
                                                                "
                                                                style="
                                                                    font-size: 1.25rem;
                                                                    width: 25px;
                                                                    text-align: center;
                                                                    flex-shrink: 0;
                                                                "
                                                            ></i>


                                                            <!-- FILE INFORMATION -->

                                                            <div
                                                                class="flex-grow-1"
                                                                style="min-width:0;"
                                                            >

                                                                <div
                                                                    class="fw-semibold text-dark"
                                                                    style="word-break:break-word;"
                                                                >

                                                                    <?= htmlspecialchars(
                                                                        $poFileName
                                                                    ) ?>

                                                                </div>


                                                                <small class="text-muted">

                                                                    <?= strtoupper(
                                                                        $poExtension
                                                                    ) ?>

                                                                    ·

                                                                    <?= !empty(
                                                                        $poAttachment['date_uploaded']
                                                                    )
                                                                        ? date(
                                                                            'h:i A',
                                                                            strtotime(
                                                                                $poAttachment['date_uploaded']
                                                                            )
                                                                        )
                                                                        : 'Time unknown'
                                                                    ?>

                                                                </small>

                                                            </div>


                                                            <!-- ARROW -->

                                                            <i
                                                                class="
                                                                    fas
                                                                    fa-chevron-right
                                                                    text-muted
                                                                "
                                                            ></i>

                                                        </div>

                                                    </div>

                                                </div>

                                            </a>

                                        </div>


                                    <?php endforeach; ?>

                                </div>

                            </div>

                        <?php endforeach; ?>

                    <?php endif; ?>

                <?php endif; ?>
            </div>
             </div> 
            </div>
        </div>
    </div>

    <!-- =====================================================
        ADD PO ATTACHMENTS MODAL
    ====================================================== -->
        <?php if ($isPurchasing): ?>

        <div
            class="modal fade"
            id="addPOAttachmentsModal"
            tabindex="-1"
            aria-labelledby="addPOAttachmentsModalLabel"
            aria-hidden="true"
        >

            <div class="modal-dialog modal-lg modal-dialog-centered">

                <div class="modal-content">

                    <div class="modal-header">

                        <h5
                            class="modal-title"
                            id="addPOAttachmentsModalLabel"
                        >

                            <i class="fas fa-file-invoice-dollar me-2"></i>

                            Add PO Attachments

                        </h5>

                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Close"
                        ></button>

                    </div>


                    <form
                        method="POST"
                        enctype="multipart/form-data"
                        id="addPOAttachmentsForm"
                    >

                        <div class="modal-body">

                            <input
                                type="hidden"
                                name="request_id"
                                value="<?= (int)$request_id ?>"
                            >

                            <input
                                type="hidden"
                                name="add_po_attachments"
                                value="1"
                            >


                            <div class="alert alert-info">

                                <i class="fas fa-info-circle me-1"></i>

                                PO attachments are visible only to
                                Purchasing users.

                            </div>


                            <div class="mb-3">

                                <label
                                    for="po_attachments"
                                    class="form-label fw-bold"
                                >

                                    Select PO Files

                                </label>

                                <input
                                    type="file"
                                    name="po_attachments[]"
                                    id="po_attachments"
                                    class="form-control"
                                    multiple
                                    accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.csv,.xls,.xlsx,.xlsm,.xlsb,.xlt,.xltx,.xltm,.ods"
                                >

                                <div class="form-text">

                                    Maximum
                                    <strong>
                                        10 files per upload
                                    </strong>.

                                    <br>

                                    Allowed:
                                    JPG, JPEG, PNG, PDF, DOC, DOCX, XLS.

                                    <br>

                                </div>

                            </div>


                            <div
                                id="poAttachmentError"
                                class="alert alert-danger d-none"
                            ></div>


                            <div
                                id="selectedPOFilesContainer"
                                class="mt-3"
                            ></div>

                        </div>


                        <div class="modal-footer">

                            <button
                                type="button"
                                class="btn btn-secondary"
                                data-bs-dismiss="modal"
                            >

                                Cancel

                            </button>


                            <button
                                type="submit"
                                class="btn btn-primary"
                                id="uploadPOAttachmentsBtn"
                            >

                                <i class="fas fa-upload me-1"></i>

                                Upload PO Files

                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>

        <?php endif; ?>

    <!-- =====================================================
            ADD ATTACHMENTS MODAL
        ====================================================== -->

        <div
                class="modal fade"
                id="addAttachmentsModal"
                tabindex="-1"
                aria-labelledby="addAttachmentsModalLabel"
                aria-hidden="true"
            >

                <div class="modal-dialog modal-lg modal-dialog-centered">

                    <div class="modal-content">

                        <div class="modal-header">

                            <h5 class="modal-title" id="addAttachmentsModalLabel">

                                <i class="fas fa-paperclip me-2"></i>
                                Add Attachments

                            </h5>

                            <button
                                type="button"
                                class="btn-close"
                                data-bs-dismiss="modal"
                                aria-label="Close"
                            ></button>

                        </div>

                        <form
                            method="POST"
                            enctype="multipart/form-data"
                            id="addAttachmentsForm"
                        >

                            <div class="modal-body">

                                <input
                                    type="hidden"
                                    name="request_id"
                                    value="<?= (int)$request_id ?>"
                                >

                                <input
                                    type="hidden"
                                    name="add_attachments"
                                    value="1"
                                >

                                <div class="mb-3">

                                    <label
                                        for="new_attachments"
                                        class="form-label fw-bold"
                                    >
                                        Select Files
                                    </label>

                                    <input
                                        type="file"
                                        name="new_attachments[]"
                                        id="new_attachments"
                                        class="form-control"
                                        multiple
                                        accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.csv,.xls,.xlsx,.xlsm,.xlsb,.xlt,.xltx,.xltm,.ods"
                                    >

                                    <div class="form-text">

                                        Maximum <strong>10 files per upload</strong>.

                                        <br>

                                        Allowed:
                                        JPG, JPEG, PNG, PDF, DOC, DOCX, XLS.

                                        <br>

                                    </div>

                                </div>

                                <div
                                    id="newAttachmentError"
                                    class="alert alert-danger d-none"
                                ></div>

                                <div
                                    id="selectedFilesContainer"
                                    class="mt-3"
                                ></div>

                            </div>

                            <div class="modal-footer">

                                <button
                                    type="button"
                                    class="btn btn-secondary"
                                    data-bs-dismiss="modal"
                                >
                                    Cancel
                                </button>

                                <button
                                    type="submit"
                                    class="btn btn-primary"
                                    id="uploadAttachmentsBtn"
                                >
                                    <i class="fas fa-upload me-1"></i>
                                    Upload Files
                                </button>

                            </div>

                        </form>

                    </div>

                </div>



            </div>
        </div>

<?php
/*
|--------------------------------------------------------------------------
| GOODS RECEIVED CONFIRMATION MODAL
|--------------------------------------------------------------------------
*/

$orderStatus = strtolower(
    trim($request['order_status'] ?? '')
);

if ($orderStatus === 'goods received'):
?>

<div
    class="modal fade"
    id="goodsReceivedModal"
    tabindex="-1"
    aria-labelledby="goodsReceivedModalLabel"
    aria-hidden="true"
>

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <!-- HEADER -->

            <div class="modal-header bg-success text-white">

                <h5
                    class="modal-title"
                    id="goodsReceivedModalLabel"
                >
                    <i class="fas fa-box-open me-2"></i>
                    Confirm Goods Received
                </h5>

                <button
                    type="button"
                    class="btn-close btn-close-white"
                    data-bs-dismiss="modal"
                    aria-label="Close"
                ></button>

            </div>


            <!-- BODY -->

            <form
                method="POST"
                action=""
            >

                <div class="modal-body">

                    <input
                        type="hidden"
                        name="goods_received_confirmation"
                        value="1"
                    >

                    <input
                        type="hidden"
                        name="request_id"
                        value="<?= (int)$request['request_id'] ?>"
                    >


                    <!-- QUESTION -->

                    <div class="mb-4">

                        <label class="form-label fw-bold">

                            Did you receive the item?

                            <span class="text-danger">*</span>

                        </label>


                        <div class="form-check mb-2">

                            <input
                                class="form-check-input"
                                type="radio"
                                name="goods_received"
                                id="goodsReceivedYes"
                                value="yes"
                                required
                            >

                            <label
                                class="form-check-label"
                                for="goodsReceivedYes"
                            >
                                <i class="fas fa-check-circle text-success me-1"></i>
                                Yes, I received the item
                            </label>

                        </div>


                        <div class="form-check">

                            <input
                                class="form-check-input"
                                type="radio"
                                name="goods_received"
                                id="goodsReceivedNo"
                                value="no"
                                required
                            >

                            <label
                                class="form-check-label"
                                for="goodsReceivedNo"
                            >
                                <i class="fas fa-times-circle text-danger me-1"></i>
                                No, I did not receive the item
                            </label>

                        </div>

                    </div>


                    <!-- COMMENT -->

                    <div class="mb-3">

                        <label
                            for="goodsReceivedComment"
                            class="form-label fw-bold"
                        >

                            Comments

                            <span class="text-danger">*</span>

                        </label>


                        <textarea
                            name="goods_received_comment"
                            id="goodsReceivedComment"
                            class="form-control"
                            rows="5"
                            placeholder="Please enter your comments regarding the received item..."
                            required
                        ></textarea>


                        <div class="form-text">

                            Please describe the condition of the item
                            or explain why you did not receive it.

                        </div>

                    </div>

                </div>


                <!-- FOOTER -->

                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal"
                    >
                        Cancel
                    </button>


                    <button
                        type="submit"
                        class="btn btn-success"
                    >

                        <i class="fas fa-save me-1"></i>

                        Submit Confirmation

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<?php endif; ?>

<script>
    $(document).on('click', '#showAllItemsBtn', function () {

        const button = $(this);
        const extraItems = $('.extra-item');

        if (extraItems.first().hasClass('d-none')) {

            // Show all items
            extraItems.removeClass('d-none');

            button.html(`
                <i class="fas fa-chevron-up me-1"></i>
                Show Less
            `);

        } else {

            // Hide items after the first 4
            extraItems.addClass('d-none');

            button.html(`
                <i class="fas fa-chevron-down me-1"></i>
                Show All Items
            `);

            $('html, body').animate({
                scrollTop: $('#itemsContainer').offset().top - 100
            }, 300);
        }
    });
</script>
<script>

    document.addEventListener('DOMContentLoaded', function () {

        const input =
            document.getElementById('new_attachments');

        const form =
            document.getElementById('addAttachmentsForm');

        const errorBox =
            document.getElementById('newAttachmentError');

        const selectedFilesContainer =
            document.getElementById('selectedFilesContainer');

        const uploadButton =
            document.getElementById('uploadAttachmentsBtn');


        if (!input || !form) {
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | FILE SELECTION
        |--------------------------------------------------------------------------
        */

        input.addEventListener('change', function () {

            errorBox.classList.add('d-none');
            errorBox.textContent = '';

            selectedFilesContainer.innerHTML = '';

            const files = Array.from(input.files);

            if (files.length === 0) {
                return;
            }


            /*
            |--------------------------------------------------------------------------
            | MAXIMUM 10 FILES
            |--------------------------------------------------------------------------
            */

            if (files.length > 10) {

                errorBox.textContent =
                    'You can select a maximum of 10 files per upload.';

                errorBox.classList.remove('d-none');

                input.value = '';

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | ALLOWED EXTENSIONS
            |--------------------------------------------------------------------------
            */

            const allowed = [
                            'jpg',
                            'jpeg',
                            'png',
                            'pdf',
                            'doc',
                            'docx',
                            'csv',
                            'xls',
                            'xlsx',
                            'xlsm',
                            'xlsb',
                            'xlt',
                            'xltx',
                            'xltm',
                            'ods'
            ];


            let invalidFile = false;


            files.forEach(function (file) {

                const extension =
                    file.name
                        .split('.')
                        .pop()
                        .toLowerCase();


                if (!allowed.includes(extension)) {

                    invalidFile = true;

                }

            });


            if (invalidFile) {

                errorBox.textContent =
                    'One or more selected files are not allowed. Excel files (.xls and .xlsx) are not allowed.';

                errorBox.classList.remove('d-none');

                input.value = '';

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | SHOW SELECTED FILES
            |--------------------------------------------------------------------------
            */

            const list =
                document.createElement('div');

            list.className =
                'list-group';


            files.forEach(function (file, index) {

                const item =
                    document.createElement('div');

                item.className =
                    'list-group-item d-flex justify-content-between align-items-center';


                item.innerHTML = `

                    <div>

                        <i class="fas fa-file me-2 text-primary"></i>

                        ${escapeHtml(file.name)}

                    </div>

                    <small class="text-muted">
                        ${(file.size / 1024 / 1024).toFixed(2)} MB
                    </small>

                `;

                list.appendChild(item);

            });


            selectedFilesContainer.appendChild(list);

        });


        /*
        |--------------------------------------------------------------------------
        | FORM SUBMIT
        |--------------------------------------------------------------------------
        */

        form.addEventListener('submit', function (e) {

            const files =
                Array.from(input.files);


            errorBox.classList.add('d-none');
            errorBox.textContent = '';


            if (files.length === 0) {

                e.preventDefault();

                errorBox.textContent =
                    'Please select at least one file.';

                errorBox.classList.remove('d-none');

                return;
            }


            if (files.length > 10) {

                e.preventDefault();

                errorBox.textContent =
                    'You can upload a maximum of 10 files per action.';

                errorBox.classList.remove('d-none');

                return;
            }


            const allowed = [
                            'jpg',
                            'jpeg',
                            'png',
                            'pdf',
                            'doc',
                            'docx',
                            'csv',
                            'xls',
                            'xlsx',
                            'xlsm',
                            'xlsb',
                            'xlt',
                            'xltx',
                            'xltm',
                            'ods'
            ];


            for (const file of files) {

                const extension =
                    file.name
                        .split('.')
                        .pop()
                        .toLowerCase();


                if (!allowed.includes(extension)) {

                    e.preventDefault();

                    errorBox.textContent =
                        'Invalid file: ' +
                        file.name +
                        '. Excel files are not allowed.';

                    errorBox.classList.remove('d-none');

                    return;
                }

            }


            /*
            |--------------------------------------------------------------------------
            | PREVENT DOUBLE SUBMISSION
            |--------------------------------------------------------------------------
            */

            uploadButton.disabled = true;

            uploadButton.innerHTML = `
                <i class="fas fa-spinner fa-spin me-1"></i>
                Uploading...
            `;

        });


        /*
        |--------------------------------------------------------------------------
        | HTML ESCAPE
        |--------------------------------------------------------------------------
        */

        function escapeHtml(value) {

            return value
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');

        }

    });

</script>
<script>

    document.getElementById('downloadAllAttachments')?.addEventListener('click', function () {

        const attachmentIds = [
            <?php foreach ($attachments as $attachment): ?>
                <?= (int)$attachment['attachment_id'] ?>,
            <?php endforeach; ?>
        ];

        if (attachmentIds.length === 0) {
            alert('No attachments found.');
            return;
        }

        const button = this;

        // Prevent double clicking
        button.disabled = true;

        button.innerHTML = `
            <i class="fas fa-spinner fa-spin me-1"></i>
            Downloading...
        `;


        /*
        |--------------------------------------------------------------------------
        | Download each file separately
        |--------------------------------------------------------------------------
        */

        attachmentIds.forEach(function (attachmentId, index) {

            setTimeout(function () {

                const link = document.createElement('a');

                link.href =
                    'ticket/download_purch_attachment.php?attachment_id='
                    + attachmentId;

                link.download = '';

                document.body.appendChild(link);

                link.click();

                document.body.removeChild(link);

            }, index * 1200);

        });


        /*
        |--------------------------------------------------------------------------
        | Restore button
        |--------------------------------------------------------------------------
        */

        setTimeout(function () {

            button.disabled = false;

            button.innerHTML = `
                <i class="fas fa-download me-1"></i>
                Download All
            `;

        }, attachmentIds.length * 1200 + 1000);

    });

</script>
<script>

    document.addEventListener('DOMContentLoaded', function () {

        const input =
            document.getElementById('po_attachments');

        const form =
            document.getElementById('addPOAttachmentsForm');

        const errorBox =
            document.getElementById('poAttachmentError');

        const selectedContainer =
            document.getElementById(
                'selectedPOFilesContainer'
            );

        const uploadButton =
            document.getElementById(
                'uploadPOAttachmentsBtn'
            );


        if (!input || !form) {
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | FILE SELECTION
        |--------------------------------------------------------------------------
        */

        input.addEventListener(
            'change',
            function () {

                errorBox.classList.add(
                    'd-none'
                );

                errorBox.textContent = '';

                selectedContainer.innerHTML = '';

                const files =
                    Array.from(input.files);


                if (files.length === 0) {
                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | MAXIMUM 10 FILES
                |--------------------------------------------------------------------------
                */

                if (files.length > 10) {

                    errorBox.textContent =
                        'You can select a maximum of 10 PO files per upload.';

                    errorBox.classList.remove(
                        'd-none'
                    );

                    input.value = '';

                    return;

                }


                /*
                |--------------------------------------------------------------------------
                | ALLOWED FILE TYPES
                |--------------------------------------------------------------------------
                */

                const allowed = [
                            'jpg',
                            'jpeg',
                            'png',
                            'pdf',
                            'doc',
                            'docx',
                            'csv',
                            'xls',
                            'xlsx',
                            'xlsm',
                            'xlsb',
                            'xlt',
                            'xltx',
                            'xltm',
                            'ods'
                ];


                for (const file of files) {

                    const extension =
                        file.name
                            .split('.')
                            .pop()
                            .toLowerCase();


                    if (
                        !allowed.includes(
                            extension
                        )
                    ) {

                        errorBox.textContent =
                            'Invalid file: ' +
                            file.name +
                            '. Excel files are not allowed.';

                        errorBox.classList.remove(
                            'd-none'
                        );

                        input.value = '';

                        return;

                    }

                }


                /*
                |--------------------------------------------------------------------------
                | DISPLAY SELECTED FILES
                |--------------------------------------------------------------------------
                */

                const list =
                    document.createElement(
                        'div'
                    );

                list.className =
                    'list-group';


                files.forEach(
                    function (file) {

                        const item =
                            document.createElement(
                                'div'
                            );

                        item.className =
                            'list-group-item d-flex justify-content-between align-items-center';


                        item.innerHTML = `

                            <div>

                                <i class="
                                    fas
                                    fa-file
                                    me-2
                                    text-primary
                                "></i>

                                ${escapeHtml(
                                    file.name
                                )}

                            </div>

                            <small class="text-muted">

                                ${(
                                    file.size /
                                    1024 /
                                    1024
                                ).toFixed(2)} MB

                            </small>

                        `;


                        list.appendChild(
                            item
                        );

                    }
                );


                selectedContainer.appendChild(
                    list
                );

            }
        );


        /*
        |--------------------------------------------------------------------------
        | FORM SUBMIT
        |--------------------------------------------------------------------------
        */

        form.addEventListener(
            'submit',
            function (e) {

                const files =
                    Array.from(
                        input.files
                    );


                errorBox.classList.add(
                    'd-none'
                );

                errorBox.textContent = '';


                if (files.length === 0) {

                    e.preventDefault();

                    errorBox.textContent =
                        'Please select at least one PO file.';

                    errorBox.classList.remove(
                        'd-none'
                    );

                    return;

                }


                if (files.length > 10) {

                    e.preventDefault();

                    errorBox.textContent =
                        'You can upload a maximum of 10 PO files per action.';

                    errorBox.classList.remove(
                        'd-none'
                    );

                    return;

                }


                const allowed = [
                            'jpg',
                            'jpeg',
                            'png',
                            'pdf',
                            'doc',
                            'docx',
                            'csv',
                            'xls',
                            'xlsx',
                            'xlsm',
                            'xlsb',
                            'xlt',
                            'xltx',
                            'xltm',
                            'ods'
                ];


                for (const file of files) {

                    const extension =
                        file.name
                            .split('.')
                            .pop()
                            .toLowerCase();


                    if (
                        !allowed.includes(
                            extension
                        )
                    ) {

                        e.preventDefault();

                        errorBox.textContent =
                            'Invalid file: ' +
                            file.name +
                            '. Excel files are not allowed.';

                        errorBox.classList.remove(
                            'd-none'
                        );

                        return;

                    }

                }


                /*
                |--------------------------------------------------------------------------
                | PREVENT DOUBLE SUBMISSION
                |--------------------------------------------------------------------------
                */

                uploadButton.disabled = true;

                uploadButton.innerHTML = `
                    <i class="fas fa-spinner fa-spin me-1"></i>
                    Uploading...
                `;

            }
        );


        /*
        |--------------------------------------------------------------------------
        | HTML ESCAPE
        |--------------------------------------------------------------------------
        */

        function escapeHtml(value) {

            return value
                .replace(
                    /&/g,
                    '&amp;'
                )
                .replace(
                    /</g,
                    '&lt;'
                )
                .replace(
                    />/g,
                    '&gt;'
                )
                .replace(
                    /"/g,
                    '&quot;'
                )
                .replace(
                    /'/g,
                    '&#039;'
                );

        }

    });

</script>
<?php
$conn->close();
?>