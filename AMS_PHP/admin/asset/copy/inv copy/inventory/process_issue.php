<?php
  include '../auth/auth.php';
  include '../db/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $item_id  = $_POST['item_id'];
    $user_id  = $_POST['user_id'];
    $quantity = $_POST['quantity'];
    $remarks  = $_POST['remarks'];

    // Insert into transaction log with date_returned as NULL
    $sql = "INSERT INTO transaction_tb (item_id, user_id, action, quantity, remarks, date_returned) 
            VALUES (?, ?, 'issued', ?, ?, 'N/A')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiis", $item_id, $user_id, $quantity, $remarks);
    $stmt->execute();

    // Update stock
    $sql2 = "UPDATE item_tb SET quantity = quantity - ? WHERE item_id = ?";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param("ii", $quantity, $item_id);
    $stmt2->execute();

    header("Location: ../transactions.php?msg=Item issued successfully");
    exit;
}
?>
