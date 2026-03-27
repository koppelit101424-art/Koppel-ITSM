<?php
include __DIR__ . '/../includes/db.php';

// Get QR path from URL
$item_id = $_GET['id'] ?? null;

// Query: get item using QR path
$sql = "SELECT i.*, q.qr_code_path 
FROM qr_tb q
JOIN item_tb i ON i.item_id = q.item_id
WHERE q.item_id = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Database error: " . $conn->error);
}

// QR path is STRING
$stmt->bind_param("s", $item_id);
$stmt->execute();

$result = $stmt->get_result();
$item = $result->fetch_assoc();

if (!$item) {
    die("Item not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Item Details</title>
<link rel="stylesheet" href="../assets/css/bootstrap.min.css">
<style>
    body { background: #f8f9fa; }
    .container { max-width: 800px; margin-top: 50px; }
    table th { width: 200px; }
</style>
</head>
<body>
<div class="container mt-4">
    <h2>Item Details</h2>
    <table class="table table-bordered">

      <tr><th>Item ID</th><td><?= htmlspecialchars($item['item_id']) ?></td></tr>
        <tr><th>Item Code</th><td><?= htmlspecialchars($item['item_code']) ?></td></tr>
        <tr><th>Name</th><td><?= htmlspecialchars($item['name']) ?></td></tr>
        <tr><th>Brand</th><td><?= htmlspecialchars($item['brand']) ?></td></tr>
        <tr><th>Model</th><td><?= htmlspecialchars($item['model']) ?></td></tr>
        <tr><th>Serial Number</th><td><?= htmlspecialchars($item['serial_number']) ?></td></tr>
        <tr><th>Description</th><td><?= htmlspecialchars($item['description']) ?></td></tr>
        <tr><th>Quantity</th><td><?= htmlspecialchars($item['quantity']) ?></td></tr>
        <tr><th>Date Received</th><td><?= htmlspecialchars($item['date_received']) ?></td></tr>
        <tr><th>QR Code</th>
            <td>
                <?php 
                $fullPath = "../inventory/qrcodes/" . ($item['qr_code_path'] ?? '');
                if (!empty($item['qr_code_path']) && file_exists($fullPath)): ?>
                    <a href="<?= htmlspecialchars($fullPath) ?>" target="_blank">
                        <img src="<?= htmlspecialchars($fullPath) ?>" width="100" alt="QR Code">
                    </a>
                <?php else: ?>
                    <span class="text-muted">No QR</span>
                <?php endif; ?>
            </td>
        </tr>
    </table>
</div>
</body>
</html>