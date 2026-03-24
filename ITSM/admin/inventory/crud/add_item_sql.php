<?php 
$message = "";

// ===== Fetch item types =====
$type_sql = "SELECT type_id, type_name FROM item_type ORDER BY type_name ASC";
$type_result = $conn->query($type_sql);

// ===== Handle form submission =====
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Basic item fields
    $name          = trim($_POST['name']);
    $brand         = $_POST['brand'];
    $model         = $_POST['model'];
    $serial        = strtoupper(trim($_POST['serial_number']));
    $description   = $_POST['description'];
    $quantity      = $_POST['quantity'];
    $date_received = $_POST['date_received'];
    $type_id       = $_POST['type_id'];

    // ===== Generate Item Code in PHP =====
    $prefix = strtoupper(substr($name, 0, 3));
    $last4  = ($serial === "" || $serial === "N/A") ? "0000" : substr($serial, -4);

    // Count items with same name
    $count_sql = "SELECT COUNT(*) as total FROM item_tb WHERE LOWER(name) = LOWER(?)";
    $stmt_count = $conn->prepare($count_sql);
    $stmt_count->bind_param("s", $name);
    $stmt_count->execute();
    $res = $stmt_count->get_result();
    $row = $res->fetch_assoc();
    $count = $row['total'] + 1; // +1 for the new item
    $stmt_count->close();

    $itemNumber = str_pad($count, 2, "0", STR_PAD_LEFT);
    $item_code = $prefix . $last4 . $itemNumber;

    // ===== Serial number check =====
    $check = $conn->prepare("SELECT item_id FROM item_tb WHERE serial_number = ?");
    $check->bind_param("s", $serial);
    $check->execute();
    $check->store_result();
    if ($check->num_rows > 0) {
        $message = "❌ Serial Number already exists.";
        return;
    }

    // ===== Insert into item_tb =====
    $sql = "INSERT INTO item_tb (item_code, name, brand, model, serial_number, description, quantity, date_received, type_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssisi", $item_code, $name, $brand, $model, $serial, $description, $quantity, $date_received, $type_id);

    if ($stmt->execute()) {

        $item_id = $stmt->insert_id; // Get inserted item ID

        // ===== Insert Laptop/System Unit specs if applicable =====
        $item_lower = strtolower($name);
        if (strpos($item_lower, "laptop") !== false || strpos($item_lower, "system unit") !== false) {

            // Extra specs fields
            $cpu         = $_POST['cpu'] ?? '';
            $ram         = $_POST['ram'] ?? '';
            $rom         = $_POST['rom'] ?? '';
            $motherboard = $_POST['motherboard'] ?? '';
            $os          = $_POST['os'] ?? '';
            $key         = $_POST['key'] ?? '';
            $antivirus   = $_POST['antivirus'] ?? '';
            $comp_name   = $_POST['comp_name'] ?? '';

            $spec_sql = "INSERT INTO laptop_pc_specs 
                (item_id, cpu, ram, rom, motherboard, os, `key`, antivirus, comp_name)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $spec_stmt = $conn->prepare($spec_sql);
            $spec_stmt->bind_param(
                "issssssss", 
                $item_id, $cpu, $ram, $rom, $motherboard, $os, $key, $antivirus, $comp_name
            );
            $spec_stmt->execute();
            $spec_stmt->close();
        }

        // Redirect after successful insert
        echo "<script>
            window.location.href='?page=inventory/all_assets&msg=" . urlencode("Item added successfully! Code: $item_code") . "';
        </script>";
        exit();

    }

    $stmt->close();
}
?>