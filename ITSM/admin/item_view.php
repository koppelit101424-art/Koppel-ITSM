<?php
// include __DIR__ . '/../includes/auth.php'; // removed for public access
include __DIR__ . '/../includes/db.php';

// Fetch qr_code_path from GET
$qr_code_path = $_GET['item_id'] ?? null;


// Fetch the item from DB with LEFT JOIN
$sql = "
SELECT i.*, q.qr_code_path 
FROM item_tb i
LEFT JOIN qr_tb q ON q.item_id = i.item_id
WHERE i.item_id = ?
";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Database error: " . $conn->error);
}

// Bind as string
$stmt->bind_param("s", $qr_code_path);
$stmt->execute();
$result = $stmt->get_result();
$item = $result->fetch_assoc();


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

        <tr><th>Item ID</th><td><?= $item ?><?php echo $qr_code_path;?></td></tr>
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