<?php

/*
|--------------------------------------------------------------------------
| PO Attachment Preview
|--------------------------------------------------------------------------
| File:
| ITSM/user/ticket/preview_po_attachment.php
|
| PO files:
| ITSM/user/uploads/po/
|--------------------------------------------------------------------------
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
// GET PO ATTACHMENT FROM DATABASE
// ---------------------------------------------------------

$stmt = $conn->prepare("
    SELECT
        attachment_id,
        request_id,
        file_name,
        file_path
    FROM po_attachment
    WHERE attachment_id = ?
    LIMIT 1
");

if (!$stmt) {
    http_response_code(500);
    exit('Database error: ' . $conn->error);
}

$stmt->bind_param("i", $attachment_id);

$stmt->execute();

$result = $stmt->get_result();

$attachment = $result->fetch_assoc();

$stmt->close();


// ---------------------------------------------------------
// ATTACHMENT NOT FOUND
// ---------------------------------------------------------

if (!$attachment) {
    http_response_code(404);
    exit('PO attachment not found.');
}


// ---------------------------------------------------------
// FILE NAME
// ---------------------------------------------------------

$fileName = basename(
    $attachment['file_name']
);


// ---------------------------------------------------------
// PO UPLOAD DIRECTORY
// ---------------------------------------------------------

$uploadBasePath = realpath(
    dirname(__DIR__) . '/uploads/po'
);

if ($uploadBasePath === false) {
    http_response_code(500);
    exit('PO upload directory does not exist.');
}


// ---------------------------------------------------------
// BUILD FILE PATH
// ---------------------------------------------------------

/*
|--------------------------------------------------------------------------
| If database contains:
|
| uploads/po/po_xxxxx.pdf
|
| We remove "uploads/po/" and append only the filename
| to the known PO upload directory.
|--------------------------------------------------------------------------
*/

$filePath =
    $uploadBasePath .
    DIRECTORY_SEPARATOR .
    basename($attachment['file_path']);


// ---------------------------------------------------------
// RESOLVE REAL FILE PATH
// ---------------------------------------------------------

$realFilePath = realpath($filePath);


// ---------------------------------------------------------
// SECURITY CHECK
// ---------------------------------------------------------

if (
    $realFilePath === false ||
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
    pathinfo(
        $fileName,
        PATHINFO_EXTENSION
    )
);

$mimeTypes = [

    'jpg' =>
        'image/jpeg',

    'jpeg' =>
        'image/jpeg',

    'png' =>
        'image/png',

    'pdf' =>
        'application/pdf',

    'doc' =>
        'application/msword',

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

header(
    'Content-Type: ' . $contentType
);

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