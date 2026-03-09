<?php
  include '../auth/auth.php';
  include '../db/db.php';

if (!isset($_GET['user_id'])) {
    header("Location: users.php");
    exit;
}

$user_id = intval($_GET['user_id']);

$stmt = $conn->prepare("DELETE FROM user_tb WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->close();

header("Location: ../users.php?deleted=1");
$conn->close();
