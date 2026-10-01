<?php

include 'includes/auth.php';
include 'includes/db.php';

$attachment_id = (int)($_GET['attachment_id'] ?? 0);

if ($attachment_id <= 0) {
    die('Invalid attachment.');
}


/*
|--------------------------------------------------------------------------
| GET ATTACHMENT
|--------------------------------------------------------------------------
*/

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

$stmt->bind_param("i", $attachment_id);
$stmt->execute();

$result = $stmt->get_result();
$attachment = $result->fetch_assoc();

$stmt->close();

if (!$attachment) {
    die('Attachment not found.');
}


/*
|--------------------------------------------------------------------------
| FILE INFORMATION
|--------------------------------------------------------------------------
*/

$fileName = basename($attachment['file_name']);

$filePath = $attachment['file_path'];


/*
|--------------------------------------------------------------------------
| PREVENT DIRECTORY TRAVERSAL
|--------------------------------------------------------------------------
*/

$filePath = str_replace('\\', '/', $filePath);

if (strpos($filePath, '..') !== false) {
    die('Invalid file path.');
}


/*
|--------------------------------------------------------------------------
| SERVER FILE PATH
|--------------------------------------------------------------------------
*/

$fullPath = __DIR__ . '/../../' . $filePath;


/*
|--------------------------------------------------------------------------
| CHECK FILE
|--------------------------------------------------------------------------
*/

if (!file_exists($fullPath) || !is_file($fullPath)) {
    die('The attachment file could not be found on the server.');
}


/*
|--------------------------------------------------------------------------
| MIME TYPE
|--------------------------------------------------------------------------
*/

$extension = strtolower(
    pathinfo($fileName, PATHINFO_EXTENSION)
);

$mimeTypes = [

    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png'  => 'image/png',

    'pdf'  => 'application/pdf',

    'doc'  => 'application/msword',

    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
];

$mimeType = $mimeTypes[$extension]
    ?? 'application/octet-stream';


/*
|--------------------------------------------------------------------------
| DOWNLOAD DOC/DOCX
|
| Browser cannot reliably preview old DOC / DOCX directly.
| Images and PDF are displayed inline.
|--------------------------------------------------------------------------
*/

if ($extension === 'doc' || $extension === 'docx') {

    header('Content-Type: ' . $mimeType);

    header(
        'Content-Disposition: attachment; filename="' .
        str_replace('"', '', $fileName) .
        '"'
    );

    header('Content-Length: ' . filesize($fullPath));

    readfile($fullPath);

    exit;
}


/*
|--------------------------------------------------------------------------
| DISPLAY IMAGE / PDF INLINE
|--------------------------------------------------------------------------
*/

header('Content-Type: ' . $mimeType);

header(
    'Content-Disposition: inline; filename="' .
    str_replace('"', '', $fileName) .
    '"'
);

header('Content-Length: ' . filesize($fullPath));

header('X-Content-Type-Options: nosniff');

readfile($fullPath);

exit;