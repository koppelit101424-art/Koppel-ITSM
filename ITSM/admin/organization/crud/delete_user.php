<?php
  include __DIR__ . '/../../../includes/auth.php';
  include __DIR__ . '/../../../includes/db.php';

if (!isset($_GET['user_id'])) {
          echo "<script>
            window.location.href='?page=organization/users';
        </script>";
    exit;
}

$user_id = intval($_GET['user_id']);

$stmt = $conn->prepare("DELETE FROM user_tb WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->close();

// header("Location: ../users.php?deleted=1");
          echo "<script>
            window.location.href='?page=organization/users';
        </script>";
$conn->close();
