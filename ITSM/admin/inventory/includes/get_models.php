<?php
include __DIR__ . '/../includes/db.php';
header('Content-Type: application/json');

$type = $_GET['type'] ?? '';
$brand = $_GET['brand'] ?? '';

if (!$type) {
    echo json_encode([]);
    exit;
}

if ($brand) {
    $stmt = $conn->prepare("SELECT DISTINCT model FROM item_tb WHERE name=? AND brand=? ORDER BY model ASC");
    $stmt->bind_param("ss", $type, $brand);
} else {
    $stmt = $conn->prepare("SELECT DISTINCT model FROM item_tb WHERE name=? ORDER BY model ASC");
    $stmt->bind_param("s", $type);
}

$stmt->execute();
$result = $stmt->get_result();
$models = [];
while($row = $result->fetch_assoc()){
    $models[] = $row['model'];
}

echo json_encode($models);
exit;