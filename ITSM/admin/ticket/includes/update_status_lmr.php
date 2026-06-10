<?php

include '../../includes/db.php';

header('Content-Type: application/json');

$request_id = $_POST['request_id'] ?? 0;
$status     = $_POST['status'] ?? '';

$stmt = $conn->prepare("
    UPDATE request_tb
    SET status = ?
    WHERE request_id = ?
");

$stmt->bind_param("si", $status, $request_id);

$success = $stmt->execute();

echo json_encode([
    'success' => $success
]);

exit;