<?php
include '../db/db.php';

$item = isset($_GET['item']) ? $_GET['item'] : "";

// Count items with the same name
$sql = "SELECT COUNT(*) as total FROM item_tb WHERE LOWER(name) = LOWER(?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $item);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

echo json_encode(["count" => $row['total']]);
$stmt->close();
$conn->close();
