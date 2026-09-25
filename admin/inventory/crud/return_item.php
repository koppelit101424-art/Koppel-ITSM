<?php
include __DIR__ . '/../../../includes/auth.php';
include __DIR__ . '/../../../includes/db.php';

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
    <div class="main-content" id="mainContent">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center text-white"><h2>Return Item</h2> </div>
      <div class="card-body">
        <form action="?page=process_return" method="post">
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

            <button type="submit" class="btn btn-primary">Submit Return</button>
        <a href="" onclick="window.history.back(); return false;" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
  </div>
</div></div></div>
</body>
</html>
