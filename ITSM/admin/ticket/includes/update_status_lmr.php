<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

include __DIR__ . '/../../includes/db.php';

header('Content-Type: application/json');

$request_id = $_POST['request_id'] ?? 0;
$status     = $_POST['status'] ?? '';
$comment     = $_POST['comment'] ?? '';

$stmt = $conn->prepare("
    UPDATE request_tb
    SET status = ?
    WHERE request_id = ?
");

if (!$stmt) {
    echo json_encode([
        'success' => false,
        'error' => $conn->error
    ]);
    exit;
}

$stmt->bind_param("si", $status, $request_id);

if (!$stmt->execute()) {
    echo json_encode([
        'success' => false,
        'error' => $stmt->error
    ]);
    exit;
}

echo json_encode([
    'success' => true
]);