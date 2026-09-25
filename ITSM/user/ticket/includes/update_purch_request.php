<?php

include 'includes/auth.php';
include 'includes/db.php';

$created_by = (int)$_SESSION['user_id'];

$request_id = isset($_POST['request_id'])
    ? (int)$_POST['request_id']
    : 0;

$department  = trim($_POST['department'] ?? '');
$item        = trim($_POST['item'] ?? '');
$description = trim($_POST['description'] ?? '');
$quantity    = $_POST['quantity'] ?? 0;
$UoM = trim($_POST['uom'] ?? '');
$date_needed = $_POST['date_needed'] ?? '';
$remarks     = trim($_POST['remarks'] ?? '');


// =====================================================
// VALIDATE REQUEST
// =====================================================

if ($request_id <= 0) {
    die('Invalid request.');
}


// =====================================================
// VERIFY OWNER + PENDING STATUS
// =====================================================

$check = $conn->prepare("
    SELECT request_id, status
    FROM purch_request_tb
    WHERE request_id = ?
      AND created_by = ?
    LIMIT 1
");

$check->bind_param(
    "ii",
    $request_id,
    $created_by
);

$check->execute();

$result = $check->get_result();
$request = $result->fetch_assoc();

$check->close();

if (!$request) {
    die('You are not allowed to edit this request.');
}

if (strcasecmp(trim($request['status']), 'pending') !== 0) {
    die('This request can no longer be edited.');
}


// =====================================================
// UPDATE
// =====================================================

$update = $conn->prepare("
    UPDATE purch_request_tb
    SET
        department = ?,
        item = ?,
        description = ?,
        quantity = ?,
        UoM = ?,
        date_needed = ?,
        remarks = ?
    WHERE request_id = ?
      AND created_by = ?
      AND LOWER(TRIM(status)) = 'pending'
");

$update->bind_param(
    "sssisssii",
    $department,
    $item,
    $description,
    $quantity,
    $UoM,
    $date_needed,
    $remarks,
    $request_id,
    $created_by
);

if ($update->execute()) {

    echo '<script>

        window.location.href = "?page=ticket/purch_lmr";
    </script>';

    exit;

} else {

    echo '<script>
        alert("Failed to update the request.");
        window.history.back();
    </script>';

    exit;
}
