<?php
  include '../auth/auth.php';
  include '../db/db.php';

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

    if ($stmt->execute()) {
        $message = "✅ Item added successfully! Generated Code: " . $item_code;
    } else {
        $message = "❌ Error: " . $stmt->error;
    }

    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Item - Inventory System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="../asset/css/style.css" rel="stylesheet">
</head>

<body>
<div class="container-fluid">
  <div class="row">
 <?php include 'sidebar.php'; ?>
    <!-- Main Content -->
    <div class="col-md-10 main">
      <h2>Add New Item</h2>

      <?php if ($message): ?>
        <div class="alert alert-info"><?= $message ?></div>
      <?php endif; ?>

      <form method="POST" class="mt-3">
      <div class="row mb-3">
        <div class="col-md-4">
          <label class="form-label">Item Code (Auto Generated)</label>
          <input type="text" id="item_code" class="form-control" readonly>
        </div>

        <div class="col-md-4">
          <label class="form-label">Item</label>
          <input type="text" name="name" class="form-control" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Brand</label>
          <input type="text" name="brand" class="form-control" required>
        </div>
      </div>

        <div class="row mb-3">
          <div class="col-md-6">
            <label class="form-label">Model</label>
            <input type="text" name="model" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Serial Number</label>
            <input type="text" name="serial_number" class="form-control">
          </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="6"></textarea>
        </div>

        <div class="row mb-3">
          <div class="col-md-4">
            <label class="form-label">Quantity</label>
            <input type="number" name="quantity" class="form-control" value="1" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Date Received</label>
            <input type="date" name="date_received" class="form-control" value="<?= date('Y-m-d') ?>" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Item Type</label>
            <select name="type_id" class="form-control" required>
              <option value="">Select Item Type</option>
              <?php if ($type_result->num_rows > 0): ?>
                <?php while($type = $type_result->fetch_assoc()): ?>
                  <option value="<?= $type['type_id'] ?>"><?= htmlspecialchars($type['type_name']) ?></option>
                <?php endwhile; ?>
              <?php endif; ?>
            </select>
          </div>
        </div>

        <button type="submit" class="btn btn-primary" style="background-color: #33A1E0">Save Item</button>
        <a href="../index.php" class="btn btn-secondary">Back</a>
      </form>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
  const itemInput   = document.querySelector("input[name='name']");
  const serialInput = document.querySelector("input[name='serial_number']");
  const codeInput   = document.getElementById("item_code");

  async function generateCode() {
    const item   = itemInput.value.trim();
    const serial = serialInput.value.trim();

    if (item.length >= 3) {
      let prefix = item.substring(0,3).toUpperCase();
      let last4  = (serial && serial.toUpperCase() !== "N/A") ? serial.slice(-4) : "0000";

      // Fetch count PER ITEM NAME
      let count = await fetch("get_item_count.php?item=" + encodeURIComponent(item))
        .then(res => res.json())
        .then(data => data.count + 1) // next stock
        .catch(() => 1);

      codeInput.value = prefix + last4 + count;
    }
  }

  itemInput.addEventListener("input", generateCode);
  serialInput.addEventListener("input", generateCode);
});
</script>
</body>
</html>
