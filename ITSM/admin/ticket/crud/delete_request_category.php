<?php

include __DIR__ . '/../../../includes/auth.php';
include __DIR__ . '/../../../includes/db.php';


// =====================================================
// GET CATEGORY ID
// =====================================================

$categoryId = isset($_POST['category_id'])
    ? (int)$_POST['category_id']
    : 0;


// =====================================================
// VALIDATE ID
// =====================================================

if ($categoryId <= 0) {

    echo json_encode([
        'success' => false,
        'message' => 'Invalid category ID.'
    ]);

    exit;

}


// =====================================================
// CHECK IF CATEGORY EXISTS
// =====================================================

$check = $conn->prepare("
    SELECT category_id
    FROM request_category_tb
    WHERE category_id = ?
    LIMIT 1
");

$check->bind_param(
    'i',
    $categoryId
);

$check->execute();

$result = $check->get_result();

if ($result->num_rows === 0) {

    $check->close();

    echo json_encode([
        'success' => false,
        'message' => 'Request category not found.'
    ]);

    exit;

}

$check->close();


// =====================================================
// DELETE CATEGORY
// =====================================================

$stmt = $conn->prepare("
    DELETE FROM request_category_tb
    WHERE category_id = ?
");

$stmt->bind_param(
    'i',
    $categoryId
);


if ($stmt->execute()) {

    echo json_encode([
        'success' => true,
        'message' => 'Request category deleted successfully.'
    ]);

} else {

    echo json_encode([
        'success' => false,
        'message' => 'Unable to delete request category.'
    ]);

}


$stmt->close();

$conn->close();

?>
