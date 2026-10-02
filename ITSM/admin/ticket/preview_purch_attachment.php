<?php

/*
|--------------------------------------------------------------------------
| Standalone Attachment Preview - ADMIN
|--------------------------------------------------------------------------
|
| File:
| ITSM/admin/ticket/preview_purch_attachment.php
|
| Uploads:
| ITSM/user/uploads/purchasing/
|
*/

require_once __DIR__ . '/../../includes/db.php';


// ---------------------------------------------------------
// GET ATTACHMENT ID
// ---------------------------------------------------------

$attachment_id = (int)($_GET['attachment_id'] ?? 0);

if ($attachment_id <= 0) {
    http_response_code(400);
    exit('Invalid attachment ID.');
}


// ---------------------------------------------------------
// GET ATTACHMENT FROM DATABASE
// ---------------------------------------------------------

$stmt = $conn->prepare("
    SELECT
        attachment_id,
        request_id,
        file_name,
        file_path
    FROM purch_request_attachments
    WHERE attachment_id = ?
    LIMIT 1
");

if (!$stmt) {
    http_response_code(500);
    exit('Database error.');
}

$stmt->bind_param("i", $attachment_id);
$stmt->execute();

$result = $stmt->get_result();
$attachment = $result->fetch_assoc();

$stmt->close();

if (!$attachment) {
    http_response_code(404);
    exit('Attachment not found.');
}


// ---------------------------------------------------------
// FILE NAME
// ---------------------------------------------------------

$fileName = basename($attachment['file_name']);


// ---------------------------------------------------------
// FILE PATH
// ---------------------------------------------------------
//
// Database example:
//
// uploads/purchasing/lmr_abc123.pdf
//
// Actual physical location:
//
// ITSM/user/uploads/purchasing/lmr_abc123.pdf
//
// Current script:
//
// ITSM/admin/ticket/preview_purch_attachment.php
//
// We need to go from:
//
// ITSM/admin/ticket/
//
// back to:
//
// ITSM/
//
// then into:
//
// user/uploads/purchasing/
//
// ---------------------------------------------------------

$fileNameOnly = basename($attachment['file_path']);

$filePath =
    dirname(__DIR__, 2) .
    '/user/uploads/purchasing/' .
    $fileNameOnly;


// ---------------------------------------------------------
// SECURITY CHECK
// ---------------------------------------------------------

$realFilePath = realpath($filePath);

$uploadBasePath = realpath(
    dirname(__DIR__, 2) .
    '/user/uploads/purchasing'
);

if (
    $realFilePath === false ||
    $uploadBasePath === false ||
    strpos(
        $realFilePath,
        $uploadBasePath . DIRECTORY_SEPARATOR
    ) !== 0
) {
    http_response_code(404);
    exit('File not found.');
}


// ---------------------------------------------------------
// CHECK FILE
// ---------------------------------------------------------

if (
    !is_file($realFilePath) ||
    !is_readable($realFilePath)
) {
    http_response_code(404);
    exit('File not found.');
}


// ---------------------------------------------------------
// MIME TYPE
// ---------------------------------------------------------

$extension = strtolower(
    pathinfo($fileName, PATHINFO_EXTENSION)
);

$mimeTypes = [

    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png'  => 'image/png',

    'pdf'  => 'application/pdf',

    'doc'  => 'application/msword',

    'docx' =>
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',

];

$contentType =
    $mimeTypes[$extension]
    ?? 'application/octet-stream';


// ---------------------------------------------------------
// CLEAR OUTPUT BUFFERS
// ---------------------------------------------------------

while (ob_get_level() > 0) {
    ob_end_clean();
}


// ---------------------------------------------------------
// SEND HEADERS
// ---------------------------------------------------------

header('Content-Type: ' . $contentType);

header(
    'Content-Disposition: inline; filename="' .
    addslashes($fileName) .
    '"'
);

header(
    'Content-Length: ' .
    filesize($realFilePath)
);

header(
    'Cache-Control: private, max-age=0, must-revalidate'
);

header('Pragma: public');


// ---------------------------------------------------------
// OUTPUT FILE
// ---------------------------------------------------------

readfile($realFilePath);

exit;
