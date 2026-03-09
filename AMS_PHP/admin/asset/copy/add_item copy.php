<?php
include 'db.php';

$message = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name         = $_POST['name'];
    $brand        = $_POST['brand'];
    $model        = $_POST['model'];
    $serial       = $_POST['serial_number'];
    $description  = $_POST['description'];
    $quantity     = $_POST['quantity'];
    $date_received= $_POST['date_received'];

    $sql = "INSERT INTO item_tb (name, brand, model, serial_number, description, quantity, date_received) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssis", $name, $brand, $model, $serial, $description, $quantity, $date_received);

    if ($stmt->execute()) {
        $message = "✅ Item added successfully!";
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
  <link href="style.css" rel="stylesheet">
</head>
<body>
<div class="container-fluid">
  <div class="row">
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Content -->
    <div class="col-md-10 main">
      <h2>Add New Item</h2>

      <?php if ($message): ?>
        <div class="alert alert-info"><?= $message ?></div>
      <?php endif; ?>

      <form method="POST" class="mt-3">
        <div class="row mb-3">
          <div class="col-md-6">
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-control" required>
          </div>
          <div class="col-md-6">
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
            <input type="date" name="date_received" class="form-control" 
                    value="<?= date('Y-m-d') ?>" required>
            </div>
        </div>

        <button type="submit" class="btn btn-success">Save Item</button>
        <a href="index.php" class="btn btn-secondary">Back</a>
      </form>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>
