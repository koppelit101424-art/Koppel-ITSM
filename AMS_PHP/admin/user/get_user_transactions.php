<?php
include '../auth/auth.php';
include '../db/db.php';

header('Content-Type: application/json');

$user_id = (int)($_GET['user_id'] ?? 0);
if ($user_id <= 0) {
    echo json_encode(['error' => 'Invalid user']);
    exit;
}

// Fetch pending transactions: issued but not returned
$sql = "
    SELECT 
        t.transaction_id,
        d.tag_number,
        d.cpu,
        t.date_issued
    FROM transaction_tb t
    JOIN desktop_tb d ON t.desktop_id = d.desktop_id
    WHERE t.user_id = ? AND t.date_returned IS NULL AND t.status = 'Issued'
    ORDER BY t.date_issued DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$transactions = [];
while ($row = $result->fetch_assoc()) {
    $transactions[] = $row;
}

echo json_encode(['transactions' => $transactions]);
$stmt->close();
$conn->close();
?>