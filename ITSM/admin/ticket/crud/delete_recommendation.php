<?php

include __DIR__ . '/../../../includes/auth.php';
include __DIR__ . '/../../../includes/db.php';

$id = (int)($_GET['id'] ?? 0);
$request_id = (int)($_GET['request_id'] ?? 0);

$stmt = $conn->prepare("
    DELETE FROM request_recommendations
    WHERE recommendation_id = ?
");

$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();
echo "<script>
    window.location.href='?page=ticket/view_request&request_id=" . $request_id . "';
</script>";
// header("Location: ../../index.php?page=ticket/view_request&request_id=".$request_id);
exit;