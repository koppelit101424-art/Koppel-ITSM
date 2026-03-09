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
  <title>Issue Item</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="../asset/css/main.css" rel="stylesheet">
  <!-- Select2 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <!-- jQuery (needed for Select2) -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <!-- Select2 JS -->
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

</head>
<body>
    <?php include 'sidebar.php'; ?>
    <!-- Main Content -->
    <div class="main-content" id="mainContent">
    <?php include '../header2.php'; ?>
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center"><h2>Issue Item</h2> </div>
      <div class="card-body">
      <form method="POST" action="process_issue.php">
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

        <button type="submit" class="btn btn-primary">Issue</button>
        <a href="" onclick="window.history.back(); return false;" class="btn btn-secondary">Cancel</a>
      </form>
    </div>
  </div>
</div>
  <script>
    $(document).ready(function() {
      $('#user_id').select2({
        placeholder: "Select User",
        allowClear: true,
        width: '100%'  
      });
    });
  </script>
</body>
</html>
