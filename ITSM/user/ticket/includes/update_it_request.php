<?php

include 'includes/auth.php';
include 'includes/db.php';

$created_by = (int)$_SESSION['user_id'];


// =====================================================
// GET POST DATA
// =====================================================

$request_id = isset($_POST['request_id'])
    ? (int)$_POST['request_id']
    : 0;

$item = trim($_POST['item'] ?? '');
$description = trim($_POST['description'] ?? '');
$quantity = (float)($_POST['quantity'] ?? 0);
$uom = trim($_POST['uom'] ?? '');
$date_needed = trim($_POST['date_needed'] ?? '');
$remarks = trim($_POST['remarks'] ?? '');
$ticket_id = (int)($_POST['ticket_id'] ?? 0);


// =====================================================
// VALIDATE
// =====================================================

if ($request_id <= 0) {

    echo '<script>
        alert("Invalid request.");
        window.history.back();
    </script>';

    exit;
}

if (empty($item)) {

    echo '<script>
        alert("Item is required.");
        window.history.back();
    </script>';

    exit;
}

if (empty($description)) {

    echo '<script>
        alert("Description is required.");
        window.history.back();
    </script>';

    exit;
}

if ($quantity <= 0) {

    echo '<script>
        alert("Quantity must be greater than zero.");
        window.history.back();
    </script>';

    exit;
}

if (empty($uom)) {

    echo '<script>
        alert("Unit of Measurement is required.");
        window.history.back();
    </script>';

    exit;
}

if (empty($date_needed)) {

    echo '<script>
        alert("Date Needed is required.");
        window.history.back();
    </script>';

    exit;
}


// =====================================================
// GET USER DEPARTMENT
// =====================================================

$userQuery = $conn->prepare("
    SELECT fullname, department
    FROM user_tb
    WHERE user_id = ?
    LIMIT 1
");

$userQuery->bind_param(
    "i",
    $created_by
);

$userQuery->execute();

$userResult = $userQuery->get_result();
$user = $userResult->fetch_assoc();

$userQuery->close();

if (!$user) {

    echo '<script>
        alert("User information could not be found.");
        window.history.back();
    </script>';

    exit;
}

$requestor = $user['fullname'];
$department = $user['department'];


// =====================================================
// VERIFY OWNER + PENDING STATUS
// =====================================================

$check = $conn->prepare("
    SELECT
        request_id,
        status
    FROM request_tb
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

    echo '<script>
        alert("You are not allowed to edit this request.");
        window.history.back();
    </script>';

    exit;
}


if (strcasecmp(trim($request['status']), 'pending') !== 0) {

    echo '<script>
        alert("This request can no longer be edited.");
        window.history.back();
    </script>';

    exit;
}


// =====================================================
// UPDATE REQUEST
// =====================================================

$update = $conn->prepare("
    UPDATE request_tb
    SET
        requestor = ?,
        department = ?,
        item = ?,
        description = ?,
        quantity = ?,
        UoM = ?,
        date_needed = ?,
        remarks = ?,
        ticket_id = ?,
        date_updated = NOW()
    WHERE request_id = ?
      AND created_by = ?
      AND LOWER(TRIM(status)) = 'pending'
");

if (!$update) {

    die("Prepare failed: " . $conn->error);
}


$update->bind_param(
    "ssssisssiii",
    $requestor,
    $department,
    $item,
    $description,
    $quantity,
    $uom,
    $date_needed,
    $remarks,
    $ticket_id,
    $request_id,
    $created_by
);


// =====================================================
// EXECUTE
// =====================================================

if ($update->execute()) {

    echo '<script>
        alert("Request updated successfully.");
        window.location.href = "?page=ticket/requests";
    </script>';

    exit;

} else {

    echo '<script>
        alert("Failed to update the request.");
        window.history.back();
    </script>';

    exit;
}

$update->close();
$conn->close();
