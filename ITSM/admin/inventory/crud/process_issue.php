<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
include __DIR__ . '/../../../includes/auth.php';
include __DIR__ . '/../../../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $item_id  = $_POST['item_id'];
    $user_id  = $_POST['user_id'];
    $quantity = $_POST['quantity'];
    $remarks  = $_POST['remarks'];
    $action_date = $_POST['action_date'];
    $date_returned = "0000-00-00 00:00:00";

    try {
        // 1. Insert into transaction log (Executes ONCE)
        $sql = "INSERT INTO transaction_tb (item_id, user_id, action, quantity, remarks, action_date, date_returned) 
            VALUES (?, ?, 'issued', ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiisss", $item_id, $user_id, $quantity, $remarks, $date_returned, $date_returned);
        $stmt->execute();

        // 2. Update stock (Executes ONCE)
        $sql2 = "UPDATE item_tb SET quantity = quantity - ? WHERE item_id = ?";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->bind_param("ii", $quantity, $item_id);
        $stmt2->execute();

        // Redirect after successful execution
        echo "<script>
            window.location.href='?page=inventory/all_assets&msg=" . urlencode("Item issued successfully!") . "';
        </script>";
        exit;

    } catch (mysqli_sql_exception $e) {
        // Handle error without running duplicate queries
        echo "SQL Error: " . $e->getMessage();
    }
}
?>