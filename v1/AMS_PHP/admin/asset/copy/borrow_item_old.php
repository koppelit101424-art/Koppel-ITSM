<?php
  include '../auth/auth.php';
  include '../db/db.php';

$item_id = $_GET['item_id'] ?? 0;

// Fetch item details
$sql = "SELECT * FROM item_tb WHERE item_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $item_id);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();

// Fetch users for dropdown
$users = $conn->query("SELECT user_id, fullname FROM user_tb ORDER BY fullname ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Borrow Item</title>
  <link href="../asset/css/style.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<!-- jQuery (needed for Select2) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

  </head>
<body>
<div class="container-fluid">
  <div class="row">
    <!-- Sidebar -->
   <div class="col-md-2 sidebar" style="height: 100vh;">
      <h4 class="text-white px-3">Inventory</h4>
      <a href="../index.php"><i class="fas fa-boxes me-2"></i> Inventory</a>
      <a href="../users.php"><i class="fas fa-user-tie me-2" ></i> Users</a>
      <a href="../transactions.php"><i class="fas fa-file-invoice-dollar me-2" ></i> Transactions</a>
      <a href="#"><i class="fas fa-chart-line me-2" ></i> Reports</a>
      <a href="#"><i class="fas fa-cog me-2"></i> Settings</a>
      <a href="logout.php" onclick="return confirm('Are you sure you want to log out?');">
        <i class="fas fa-right-from-bracket me-2" style="color:white;"></i> Logout
      </a>
    </div>

    <!-- Main Content -->
    <div class="col-md-10 main">
      <h2>Borrow Item</h2>
      <form method="POST" action="process_borrow.php">
        <input type="hidden" name="item_id" value="<?= $item['item_id'] ?>">

        <div class="row mb-3">
          <div class="col-md-6">
            <label class="form-label">Item Name</label>
            <input type="text" class="form-control" value="<?= $item['name'] ?>" readonly>
          </div>
          <div class="col-md-6">
            <label class="form-label">Brand / Model</label>
            <input type="text" class="form-control" value="<?= $item['brand'] ?> - <?= $item['model'] ?>" readonly>
          </div>
        </div>

      <div class="row mb-3">
          <div class="col-md-6">
            <label class="form-label">Issue To</label>
            <select name="user_id" id="user_id" class="form-control" required>
              <option value="">-- Select User --</option>
              <?php while ($u = $users->fetch_assoc()): ?>
                <option value="<?= $u['user_id'] ?>"><?= $u['fullname'] ?></option>
              <?php endwhile; ?>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label">Quantity</label>
            <input type="number" name="quantity" class="form-control" min="1" max="<?= $item['quantity'] ?>" required>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">Remarks</label>
          <textarea name="remarks" class="form-control" rows="5"></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Borrow</button>
        <a href="../index.php" class="btn btn-secondary">Cancel</a>
      </form>
    </div>
  </div>
</div>
<script>
  $(document).ready(function() {
    $('#user_id').select2({
      placeholder: "-- Select User --",
      allowClear: true,
      width: '100%'  // makes it responsive inside Bootstrap
    });
  });
</script>
</body>
</html>
