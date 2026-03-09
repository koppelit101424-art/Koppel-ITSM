<?php
include '../auth/auth.php';
include '../db/db.php';

$message = "";

// Validate ID
if (!isset($_GET['id'])) {
    die("❌ Invalid Transaction ID");
}
$id = intval($_GET['id']);

// Fetch transaction
$sql = "SELECT * FROM transaction_tb WHERE transaction_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("❌ Transaction not found");
}

$transaction = $result->fetch_assoc();
$stmt->close();

// Handle update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $remarks  = $_POST['remarks'];
    $action   = $_POST['action'];
    $quantity = $_POST['quantity'];

    // Keep the old date_returned unless action is 'returned'
    $date_returned = $transaction['date_returned'];
    if ($action === 'returned' && (empty($date_returned) || $date_returned == '0000-00-00' || $date_returned == '0000-00-00 00:00:00')) {
        $date_returned = date("Y-m-d"); // auto set today if returning
    }

    $update_sql = "UPDATE transaction_tb 
                   SET remarks=?, action=?, quantity=?, date_returned=? 
                   WHERE transaction_id=?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ssisi", $remarks, $action, $quantity, $date_returned, $id);

    if ($update_stmt->execute()) {
        header("Location: ../transactions.php?msg=Transaction updated successfully!");
        exit;
    } else {
        $message = "❌ Error: " . $update_stmt->error;
    }
    $update_stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>ITSM - Edit Transaction</title>
  <link rel="icon" href="../asset/img/Koppel_bip.ico">
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
      <div class="card-header d-flex justify-content-between align-items-center"> 
      <div class="d-flex justify-content-between align-items-center ">
        <h2>Edit Transaction</h2>
      </div></div>
      <div class="card-body">
      <!-- <?= $transaction['transaction_id'] ?> -->
      <?php if ($message): ?>
        <div class="alert alert-info"><?= $message ?></div>
      <?php endif; ?>

      <form method="POST" class="mt-3">
        <div class="mb-3">
          <label class="form-label">Remarks</label>
          <textarea name="remarks" class="form-control" rows="4"><?= htmlspecialchars($transaction['remarks']) ?></textarea>
        </div>

        <div class="row mb-3">
          <div class="col-md-6">
            <label class="form-label">Action</label>
            <select name="action" class="form-control" required>
              <option value="issued"   <?= $transaction['action'] == 'issued' ? 'selected' : '' ?>>Issued</option>
              <option value="borrowed" <?= $transaction['action'] == 'borrowed' ? 'selected' : '' ?>>Borrowed</option>
              <option value="returned" <?= $transaction['action'] == 'returned' ? 'selected' : '' ?>>Returned</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Quantity</label>
            <input type="number" name="quantity" class="form-control" value="<?= htmlspecialchars($transaction['quantity']) ?>" required>
          </div>
        </div>

        <button type="submit" class="btn btn-primary">Update Transaction</button>
        <a href="../transactions.php" class="btn btn-secondary">Back</a>
      </form>
    </div>
  </div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>
