<?php
include __DIR__ . '/../../../includes/auth.php';
include __DIR__ . '/../../../includes/db.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$request_id = $_POST['request_id'] ?? null;
$status = $_POST['status'] ?? null;

$validStatuses = ['Pending', 'Approved', 'Rejected', 'Received'];
if (!is_numeric($request_id) || !in_array($status, $validStatuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$date_updated = date('Y-m-d H:i:s');
$stmt = $conn->prepare("UPDATE request_tb SET status = ?, date_updated = ? WHERE request_id = ?");
$stmt->bind_param("ssi", $status, $date_updated, $request_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $conn->error]);
}

$stmt->close();
$conn->close();
?>