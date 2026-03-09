<?php
  include '../auth/auth.php';
  include '../db/db.php';

if (!isset($_GET['id'])) {
    die("Invalid request.");
}

$transaction_id = intval($_GET['id']);

// Get transaction info
$sql = "SELECT t.*, i.name AS item_name, i.brand, i.model 
        FROM transaction_tb t 
        JOIN item_tb i ON t.item_id = i.item_id
        WHERE t.transaction_id = $transaction_id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    die("Transaction not found.");
}

$transaction = $result->fetch_assoc();

// Default return date is today
$date_today = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Return Item</title>
  <link href="../asset/css/main.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="container-fluid">
    <?php include 'sidebar.php'; ?>
    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Return Item</h2>
        </div>

        <form action="process_return.php" method="post">
            <input type="hidden" name="transaction_id" value="<?= $transaction_id ?>">
            <input type="hidden" name="item_id" value="<?= $transaction['item_id'] ?>">

            <div class="mb-3">
              <label class="form-label">Item</label>
              <input type="text" class="form-control" value="<?= $transaction['item_name'] ?> (<?= $transaction['brand'] ?> - <?= $transaction['model'] ?>)" readonly>
            </div>

            <div class="mb-3">
              <label class="form-label">Quantity Borrowed / Issued</label>
              <input type="number" class="form-control" value="<?= $transaction['quantity'] ?>" readonly>
            </div>

            <div class="mb-3">
              <label class="form-label">Quantity Returned</label>
              <input type="number" name="quantity_returned" class="form-control" max="<?= $transaction['quantity'] ?>" min="1" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Date Returned</label>
              <input type="date" name="date_returned" class="form-control" value="<?= $date_today ?>" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Remarks</label>
              <textarea name="remarks" class="form-control" rows="3"></textarea>
            </div>

            <button type="submit" class="btn btn-success">Submit Return</button>
            <a href="../transactions.php" class="btn btn-secondary ms-2">Cancel</a>
        </form>
    </div>
  </div>
</div>
</body>
</html>
