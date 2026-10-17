
<?php

include '../../includes/auth.php';
include '../../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../index.php?page=ticket/purch_lmr');
    exit;
}

$loggedInUserId = (int)($_SESSION['user_id'] ?? 0);

$request_id = filter_input(
    INPUT_POST,
    'request_id',
    FILTER_VALIDATE_INT
);

if ($loggedInUserId <= 0 || !$request_id || $request_id <= 0) {
    header('Location: ../../index.php?page=ticket/purch_lmr');
    exit;
}

// Verify request ownership and status
$stmt = $conn->prepare("
    SELECT created_by, status
    FROM purch_request_tb
    WHERE request_id = ?
    LIMIT 1
");

$stmt->bind_param("i", $request_id);
$stmt->execute();

$result = $stmt->get_result();
$request = $result->fetch_assoc();

$stmt->close();

// Request must exist
if (!$request) {
    header('Location: ../../index.php?page=ticket/purch_lmr');
    exit;
}

// Only the original requestor can delete their own LMR
if ((int)$request['created_by'] !== $loggedInUserId) {
    header('Location: ../../index.php?page=ticket/purch_lmr');
    exit;
}

// Only Pending requests can be deleted
if (strcasecmp(trim($request['status']), 'pending') !== 0) {
    header('Location: ../../index.php?page=ticket/purch_lmr');
    exit;
}

// Enforce both conditions again during deletion
$stmt = $conn->prepare("
    DELETE FROM purch_request_tb
    WHERE request_id = ?
      AND created_by = ?
      AND LOWER(TRIM(status)) = 'pending'
");

$stmt->bind_param("ii", $request_id, $loggedInUserId);
$stmt->execute();

$stmt->close();

header('Location: ../../index.php?page=ticket/purch_lmr');
exit;