<?php
include '../auth/auth.php';
include '../db/db.php';

$request_id = intval($_GET['id'] ?? 0);
if ($request_id > 0) {
    $stmt = $conn->prepare("DELETE FROM request_tb WHERE request_id=? AND created_by=?");
    $stmt->bind_param("ii", $request_id, $_SESSION['user_id']);
    $stmt->execute();
    $stmt->close();
}
header("Location: requests.php");
exit;
