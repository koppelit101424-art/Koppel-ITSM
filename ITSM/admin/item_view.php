<?php
include __DIR__ . '/../includes/db.php';

// Get item ID from QR
$item_id = $_GET['id'] ?? null;

if (!$item_id) {
    die("Invalid request.");
}

// Query
$sql = "
SELECT 
    i.*, 
    q.qr_code_path,
    c.condition_name,
    s.cpu,
    s.ram,
    s.rom,
    s.motherboard,
    s.os,
    s.`key`,
    s.antivirus,
    s.comp_name
FROM qr_tb q
JOIN item_tb i ON i.item_id = q.item_id
LEFT JOIN item_condition_tb c ON i.condition_id = c.condition_id
LEFT JOIN laptop_pc_specs s ON s.item_id = i.item_id
WHERE q.item_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $item_id);
$stmt->execute();

$result = $stmt->get_result();
$item = $result->fetch_assoc();

if (!$item) {
    die("Item not found.");
}

// Output plain text
echo "<h2>Item Details</h2>";

echo "<strong>Code:</strong> " . htmlspecialchars($item['item_code']) . "<br>";
echo "<strong>Name:</strong> " . htmlspecialchars($item['name']) . "<br>";
echo "<strong>Brand:</strong> " . htmlspecialchars($item['brand']) . "<br>";
echo "<strong>Model:</strong> " . htmlspecialchars($item['model']) . "<br>";
echo "<strong>Serial Number:</strong> " . htmlspecialchars($item['serial_number']) . "<br>";
echo "<strong>Quantity:</strong> " . htmlspecialchars($item['quantity']) . "<br>";
echo "<strong>Date Received:</strong> " . htmlspecialchars($item['date_received']) . "<br>";
echo "<strong>Condition:</strong> " . htmlspecialchars($item['condition_name'] ?? 'N/A') . "<br><br>";

// Show specs only if available
if (!empty($item['cpu']) || !empty($item['ram'])) {
    echo "<h3>Specs</h3>";
    echo "<strong>CPU:</strong> " . htmlspecialchars($item['cpu']) . "<br>";
    echo "<strong>RAM:</strong> " . htmlspecialchars($item['ram']) . "<br>";
    echo "<strong>Storage:</strong> " . htmlspecialchars($item['rom']) . "<br>";
    echo "<strong>Motherboard:</strong> " . htmlspecialchars($item['motherboard']) . "<br>";
    echo "<strong>OS:</strong> " . htmlspecialchars($item['os']) . "<br>";
    echo "<strong>OS Key:</strong> " . htmlspecialchars($item['key']) . "<br>";
    echo "<strong>Antivirus:</strong> " . htmlspecialchars($item['antivirus']) . "<br>";
    echo "<strong>Computer Name:</strong> " . htmlspecialchars($item['comp_name']) . "<br><br>";
}
echo "<h3>Description</h3>";
echo nl2br(htmlspecialchars($item['description']));
?>