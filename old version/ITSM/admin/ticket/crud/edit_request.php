<?php
include __DIR__ . '/../../../includes/auth.php';
include __DIR__ . '/../../../includes/db.php';
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('Invalid request ID');
}

$request_id = (int)$_GET['id'];

// Fetch request data
$sql = "SELECT * FROM request_tb WHERE request_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $request_id);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows === 0) {
    die('Request not found');
}

$request = $result->fetch_assoc();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $lmr_no = $conn->real_escape_string(trim($_POST['lmr_no'] ?? ''));
    $requestor = $conn->real_escape_string(trim($_POST['requestor'] ?? ''));
    $department = $conn->real_escape_string(trim($_POST['department'] ?? ''));
    $item = $conn->real_escape_string(trim($_POST['item'] ?? ''));
    $description = $conn->real_escape_string(trim($_POST['description'] ?? ''));
    $quantity = (int)($_POST['quantity'] ?? 1);
    $UoM = $conn->real_escape_string(trim($_POST['UoM'] ?? ''));
    $date_needed = !empty($_POST['date_needed']) ? $_POST['date_needed'] : null;
    $remarks = $conn->real_escape_string(trim($_POST['remarks'] ?? ''));
    $status = $conn->real_escape_string(trim($_POST['status'] ?? 'Pending'));

    // Validate
    if (empty($requestor) || empty($item)) {
        $message = "Requestor and Item are required.";
    } else {
        $updateSql = "
            UPDATE request_tb SET
                lmr_no = '$lmr_no',
                requestor = '$requestor',
                department = '$department',
                item = '$item',
                description = '$description',
                quantity = $quantity,
                UoM = '$UoM',
                date_needed = " . ($date_needed ? "'$date_needed'" : "NULL") . ",
                remarks = '$remarks',
                status = '$status'
            WHERE request_id = $request_id
        ";

        if ($conn->query($updateSql)) {
            // Redirect to requests.php after 1 second (with success message in session if needed)
            // header("Location: ?page=ticket/requests.php&updated=1");
                 echo "<script>
                        window.location.href='?page=ticket/requests';
                    </script>";
            exit();
        } else {
            $message = "Error: " . $conn->error;
        }
    }
}
?>

      <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center"> 
      <div class="d-flex justify-content-between align-items-center text-white">
        <h2> Add New User</h2>
      </div></div>
      <div class="card-body">

        <?php if ($message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">LMR No.</label>
                    <input type="text" name="lmr_no" class="form-control" value="<?= htmlspecialchars($request['lmr_no'] ?? '') ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Requestor *</label>
                    <input type="text" name="requestor" class="form-control" value="<?= htmlspecialchars($request['requestor'] ?? '') ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Department</label>
                    <input type="text" name="department" class="form-control" value="<?= htmlspecialchars($request['department'] ?? '') ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Item *</label>
                    <input type="text" name="item" class="form-control" value="<?= htmlspecialchars($request['item'] ?? '') ?>" required>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="2"><?= htmlspecialchars($request['description'] ?? '') ?></textarea>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Quantity</label>
                    <input type="number" name="quantity" class="form-control" value="<?= htmlspecialchars($request['quantity'] ?? 1) ?>" min="1">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">UoM</label>
                    <input type="text" name="UoM" class="form-control" value="<?= htmlspecialchars($request['UoM'] ?? '') ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Date Needed *</label>
                    <input type="date" class="form-control" name="date_needed"
                        value="<?= isset($request['date_needed']) ? date('Y-m-d', strtotime($request['date_needed'])) : '' ?>" required>
                </div>
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        <option value="Pending" <?= ($request['status'] ?? '') === 'Pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="Approved" <?= ($request['status'] ?? '') === 'Approved' ? 'selected' : '' ?>>Approved</option>
                        <option value="Rejected" <?= ($request['status'] ?? '') === 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                        <option value="Completed" <?= ($request['status'] ?? '') === 'Completed' ? 'selected' : '' ?>>Completed</option>
                    </select>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Remarks</label>
                    <textarea name="remarks" class="form-control" rows="2"><?= htmlspecialchars($request['remarks'] ?? '') ?></textarea>
                </div>
                   <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"> Update Request
                </button>
                <a href="#" onclick="window.history.back(); return false;" class="btn btn-secondary">Back
                </a>
            </div>
            </div>

         
        </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<?php $conn->close(); ?>