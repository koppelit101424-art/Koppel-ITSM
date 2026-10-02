<?php

/*
|--------------------------------------------------------------------------
| Download Purchasing Attachment
|--------------------------------------------------------------------------
|
| File:
| ITSM/admin/ticket/download_purch_attachment.php
|
| Actual uploads:
| ITSM/user/uploads/purchasing/
|
| Downloads ONE attachment using attachment_id.
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


// ---------------------------------------------------------
// CHECK ATTACHMENT
// ---------------------------------------------------------

if (!$attachment) {
    http_response_code(404);
    exit('Attachment not found.');
}


// ---------------------------------------------------------
// UPLOAD DIRECTORY
// ---------------------------------------------------------

/*
|--------------------------------------------------------------------------
| Admin structure:
|
| ITSM/
| ├── admin/
| │   └── ticket/
| │       └── download_purch_attachment.php
| │
| └── user/
|     └── uploads/
|         └── purchasing/
|
|--------------------------------------------------------------------------
*/

$uploadBasePath =
    dirname(__DIR__, 2) .
    '/user/uploads/purchasing';

$realUploadBasePath = realpath($uploadBasePath);

if (
    $realUploadBasePath === false ||
    !is_dir($realUploadBasePath)
) {
    http_response_code(404);
    exit('Upload directory not found.');
}


// ---------------------------------------------------------
// GET STORED FILE NAME
// ---------------------------------------------------------

/*
|--------------------------------------------------------------------------
| file_path example:
|
| uploads/purchasing/lmr_abc123.png
|
| We only use basename() so the database cannot provide
| a path outside the purchasing upload directory.
|--------------------------------------------------------------------------
*/

$storedFileName = basename(
    $attachment['file_path']
);

if ($storedFileName === '') {
    http_response_code(404);
    exit('Invalid attachment file.');
}


// ---------------------------------------------------------
// BUILD PHYSICAL FILE PATH
// ---------------------------------------------------------

$filePath =
    $realUploadBasePath .
    DIRECTORY_SEPARATOR .
    $storedFileName;


// ---------------------------------------------------------
// RESOLVE REAL PATH
// ---------------------------------------------------------

$realFilePath = realpath($filePath);


// ---------------------------------------------------------
// SECURITY CHECK
// ---------------------------------------------------------

if (
    $realFilePath === false ||
    strpos(
        $realFilePath,
        $realUploadBasePath . DIRECTORY_SEPARATOR
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
// ORIGINAL FILE NAME
// ---------------------------------------------------------

$downloadName = basename(
    $attachment['file_name']
);

if ($downloadName === '') {
    $downloadName = $storedFileName;
}


// ---------------------------------------------------------
// GET MIME TYPE
// ---------------------------------------------------------

$extension = strtolower(
    pathinfo($downloadName, PATHINFO_EXTENSION)
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
// CLEAR OUTPUT BUFFER
// ---------------------------------------------------------

while (ob_get_level() > 0) {
    ob_end_clean();
}


// ---------------------------------------------------------
// DOWNLOAD HEADERS
// ---------------------------------------------------------

header(
    'Content-Type: ' . $contentType
);

header(
    'Content-Disposition: attachment; filename="' .
    addslashes($downloadName) .
    '"'
);

header(
    'Content-Length: ' .
    filesize($realFilePath)
);

header(
    'Cache-Control: no-cache, no-store, must-revalidate'
);

header(
    'Pragma: no-cache'
);

header(
    'Expires: 0'
);


// ---------------------------------------------------------
// DOWNLOAD FILE
// ---------------------------------------------------------

readfile($realFilePath);

$conn->close();

exit;
