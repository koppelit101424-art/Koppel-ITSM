<?php 
$message = "";

// Fetch item types
$type_sql = "SELECT type_id, type_name FROM item_type ORDER BY type_name ASC";
$type_result = $conn->query($type_sql);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name         = trim($_POST['name']);
    $brand        = $_POST['brand'];
    $model        = $_POST['model'];
    $serial       = strtoupper(trim($_POST['serial_number']));
    $description  = $_POST['description'];
    $quantity     = $_POST['quantity'];
    $date_received= $_POST['date_received'];
    $type_id      = $_POST['type_id'];

    // ===== Generate Item Code in PHP (for safety) =====
    $prefix = strtoupper(substr($name, 0, 3));
    $last4  = ($serial === "" || $serial === "N/A") ? "0000" : substr($serial, -4);

    // Count per item name
    $count_sql = "SELECT COUNT(*) as total FROM item_tb WHERE LOWER(name) = LOWER(?)";
    $stmt_count = $conn->prepare($count_sql);
    $stmt_count->bind_param("s", $name);
    $stmt_count->execute();
    $res = $stmt_count->get_result();
    $row = $res->fetch_assoc();
    $count = $row['total'] + 1; // +1 for new item
    $stmt_count->close();

    $item_code = $prefix . $last4 . $count;

    // Insert
    $sql = "INSERT INTO item_tb (item_code, name, brand, model, serial_number, description, quantity, date_received, type_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssisi", $item_code, $name, $brand, $model, $serial, $description, $quantity, $date_received, $type_id);

    // if ($stmt->execute()) {
    //     $message = "✅ Item added successfully! Generated Code: " . $item_code;
    if ($stmt->execute()) {

    echo "<script>
        window.location.href='?page=inventory/all_assets&msg=" . urlencode("Item updated successfully! Code: $item_code") . "';
    </script>";
    exit();
    } else {
        $message = "❌ Error: " . $stmt->error;
    }

    $stmt->close();
}
?>
