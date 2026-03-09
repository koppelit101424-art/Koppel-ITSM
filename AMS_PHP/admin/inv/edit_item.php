<?php
include '../auth/auth.php';
include '../db/db.php';
include 'edit_sql.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Item</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="../asset/css/main.css" rel="stylesheet">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <!-- Main Content -->
    <div class="main-content" id="mainContent">
    <?php include '../header2.php'; ?>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center"><h2>Edit Item</h2> </div>
        <div class="card-body">

        <?php if ($message): ?>
          <div class="alert alert-info"><?= $message ?></div>
        <?php endif; ?>

        <form method="POST" class="mt-3">
          <div class="row mb-3">
            <div class="col-md-4">
              <label class="form-label">Item Code (Auto Generated)</label>
              <input type="text" id="item_code" name="item_code" class="form-control" value="<?= htmlspecialchars($item['item_code']) ?>" >
            </div>

            <div class="col-md-4">
              <label class="form-label">Item</label>
              <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($item['name']) ?>" required>
            </div>

            <div class="col-md-4">
              <label class="form-label">Brand</label>
              <input type="text" name="brand" class="form-control" value="<?= htmlspecialchars($item['brand']) ?>" required>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Model</label>
              <input type="text" name="model" class="form-control" value="<?= htmlspecialchars($item['model']) ?>" required>
            </div>

            <div class="col-md-6">
              <label class="form-label">Serial Number</label>
              <input type="text" name="serial_number" class="form-control" value="<?= htmlspecialchars($item['serial_number']) ?>">
            </div>
          </div>

          <div class="mb-3">
              <label class="form-label">Description</label>
              <textarea name="description" class="form-control" rows="6"><?= htmlspecialchars($item['description']) ?></textarea>
          </div>

          <div class="row mb-3">
            <div class="col-md-4">
              <label class="form-label">Quantity</label>
              <input type="number" name="quantity" class="form-control" value="<?= htmlspecialchars($item['quantity']) ?>" required>
            </div>

            <div class="col-md-4">
              <label class="form-label">Date Received</label>
              <input type="date" name="date_received" class="form-control" value="<?= htmlspecialchars($item['date_received']) ?>" required>
            </div>

            <div class="col-md-4">
              <label class="form-label">Item Type</label>
              <select name="type_id" class="form-control" required>
                <option value="">Select Type</option>
                <?php if ($type_result->num_rows > 0): ?>
                  <?php while($type = $type_result->fetch_assoc()): ?>
                    <option value="<?= $type['type_id'] ?>" <?= ($type['type_id'] == $item['type_id']) ? 'selected' : '' ?>>
                      <?= htmlspecialchars($type['type_name']) ?>
                    </option>
                  <?php endwhile; ?>
                <?php endif; ?>
              </select>
            </div>
          </div>

          <button type="submit" class="btn btn-primary">Update Item</button>
          <a href="" onclick="window.history.back(); return false;" class="btn btn-secondary">Back</a>
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
          .then(data => data.count) // ⚠ no +1 when editing
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
<?php $conn->close(); ?>
