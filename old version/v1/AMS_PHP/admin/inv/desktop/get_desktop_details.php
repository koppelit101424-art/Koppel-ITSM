<?php
include '../../db/db.php';

header('Content-Type: application/json');

if (!isset($_POST['id']) || empty($_POST['id'])) {
    echo json_encode(['error' => 'No ID received']);
    exit;
}

$id = intval($_POST['id']);

$sql = "SELECT * FROM desktop_tb WHERE desktop_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode($row);
} else {
    echo json_encode(['error' => 'No record found']);
}

$stmt->close();
$conn->close();
?>