<?php
include __DIR__ . '/../../../includes/auth.php';
include __DIR__ . '/../../../includes/db.php';
$success = $error = '';
$errors = [];
$ticket_id = $_GET['ticket_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Shared fields (same for all items)
    $lmr_no = trim($_POST['lmr_no'] ?? '');
    $requestor = trim($_POST['requestor'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $created_by = intval($_POST['created_by'] ?? 0);
    $ticket_id = intval($_POST['ticket_id'] ?? 0);
    // Validate shared fields
    if (empty($lmr_no)) $errors[] = "LMR No is required";
    if (empty($requestor)) $errors[] = "Requestor is required";
    if (empty($department)) $errors[] = "Department is required";
    if ($created_by <= 0) $errors[] = "Invalid request creator";

    // Process each item row
    $items = $_POST['item'] ?? [];
    $descriptions = $_POST['description'] ?? [];
    $quantities = $_POST['quantity'] ?? [];
    $uoms = $_POST['uom'] ?? [];
    $dates_needed = $_POST['date_needed'] ?? [];
    $remarks_list = $_POST['remarks'] ?? [];
    $statuses = $_POST['status'] ?? [];

    $validItems = [];
    for ($i = 0; $i < count($items); $i++) {
        $item = trim($items[$i] ?? '');
        if (empty($item)) continue;

        $desc = trim($descriptions[$i] ?? '');
        $qty = floatval($quantities[$i] ?? 0);
        $uom = trim($uoms[$i] ?? '');
        $date_needed = trim($dates_needed[$i] ?? '');
        $remarks = trim($remarks_list[$i] ?? '');
        $status = trim($statuses[$i] ?? 'Pending');

        if (empty($desc)) $errors[] = "Description required for item: $item";
        if ($qty <= 0) $errors[] = "Quantity must be > 0 for item: $item";
        if (empty($uom)) $errors[] = "UoM required for item: $item";
        if (empty($date_needed)) $errors[] = "Date Needed required for item: $item";

        $validItems[] = compact('item', 'desc', 'qty', 'uom', 'date_needed', 'remarks', 'status');
    }

    if (empty($validItems)) {
        $errors[] = "At least one valid item is required.";
        }

    $sql = "INSERT INTO `request_tb`(
        `lmr_no`,
        `requestor`,
        `department`,
        `item`,
        `description`,
        `quantity`,
        `UoM`,
        `date_needed`,
        `remarks`,
        `status`,
        `date_created`,
        `date_updated`,
        `created_by`,
        `ticket_id`
    )
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), ?, ?)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) die("Prepare failed: " . $conn->error);

    foreach ($validItems as $itemData) {
        $stmt->bind_param(
            "sssssdssssii",
            $lmr_no,
            $requestor,
            $department,
            $itemData['item'],
            $itemData['desc'],
            $itemData['qty'],
            $itemData['uom'],
            $itemData['date_needed'],
            $itemData['remarks'],
            $itemData['status'],
            $created_by,
            $ticket_id
        );

        if (!$stmt->execute()) {
            $errors[] = "Failed to insert item " . $itemData['item'] . ": " . $stmt->error;
        }
        //      if ($insertCount > 0) {
        //     $success = "$insertCount item(s) added successfully under LMR No: " . htmlspecialchars($lmr_no);
        // } else {
        //     $error = "Failed to save items.";
        // }
    }
    $stmt->close();

   
    }
?>

<style>
.item-row { border-top: 1px dashed #ccc; padding-top: 15px; margin-top: 15px; }
</style>
</head>
<body>
<div class="card">

<div class="card-header d-flex justify-content-between align-items-center text-white">
<span>Add New Request (Multiple Items)</span>
<a href="?page=ticket/requests"  class="btn btn-secondary btn-sm">
<!-- <i class="fas fa-arrow-left me-1"></i> -->
 Back to Requests
</a>
</div>

<div class="card-body">

<?php if (!empty($errors)): ?>
<div class="alert alert-danger">
<ul class="mb-0">
<?php foreach ($errors as $err): ?>
<li><?= htmlspecialchars($err) ?></li>
<?php endforeach; ?>
</ul>
</div>
<?php endif; ?>

<?php if ($success): ?>
<div class="alert alert-success">
<?= $success ?>
<a href="?page=ticket/requests" class="alert-link">View Requests</a>
</div>
<?php endif; ?>

<form method="POST" action="" id="requestForm">

<div class="row mb-4">
<div class="col-md-4">
<label class="form-label">LMR No *</label>
<input type="text" class="form-control" name="lmr_no" required>
</div>

<div class="col-md-4">
<label class="form-label">Requestor *</label>
<input type="text" class="form-control" name="requestor" required>
</div>

<div class="col-md-4">
<label class="form-label">Department *</label>
<input type="text" class="form-control" name="department" required>
</div>
</div>
<input type="hidden" name="ticket_id" value="<?= htmlspecialchars($ticket_id) ?>">
<!-- CREATED BY -->
<input type="hidden" name="created_by" value="<?= $_SESSION['user_id'] ?>">

<h5>Items</h5>
<div id="itemsContainer"></div>

<button type="button" class="btn btn-outline-primary mt-2" onclick="addItemRow()">
<i class="fas fa-plus"></i> Add Item
</button>

<div class="d-flex justify-content-end mt-4">
<button type="submit" class="btn btn-primary me-2">
<!-- <i class="fas fa-save me-1"></i>  -->
Save All Items
</button>
<a href="#" onclick="window.history.back(); return false;" class="btn btn-secondary">
<!-- <i class="fas fa-times me-1"></i> -->
 Cancel
</a>
</div>

</form>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
function addItemRow() {
    const container = document.getElementById('itemsContainer');
    const row = document.createElement('div');
    row.className = 'item-row';
    row.innerHTML = `
    <div class="row">
        <div class="col-md-3">
            <label class="form-label">Item *</label>
            <input type="text" class="form-control" name="item[]" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Description *</label>
            <textarea class="form-control" name="description[]" rows="1" required></textarea>
        </div>
        <div class="col-md-2">
            <label class="form-label">Qty *</label>
            <input type="number" class="form-control" name="quantity[]" value="1" step="0.01" required>
        </div>
        <div class="col-md-2">
            <label class="form-label">Unit of Measurement *</label>
            <input type="text" class="form-control" name="uom[]" value="pc" required>
        </div>
        <div class="col-md-2">
            <label class="form-label">Date Needed *</label>
            <input type="date" class="form-control" name="date_needed[]" value="ASAP" required>
        </div>
    </div>
    <div class="row mt-2">
        <div class="col-md-6">
            <label class="form-label">Remarks</label>
            <textarea class="form-control" name="remarks[]" rows="1"></textarea>
        </div>
        <div class="col-md-3">
            <label class="form-label">Status</label>
            <select class="form-control" name="status[]">
                <option value="Pending" selected>Pending</option>
                <option value="Approved">Approved</option>
                <option value="Rejected">Rejected</option>
                <option value="Received">Received</option>
            </select>
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.item-row').remove()">
                <i class="fas fa-trash"></i> Remove
            </button>
        </div>
    </div>`;
    container.appendChild(row);
}

window.onload = addItemRow;
</script>

</body>
</html>
<?php $conn->close(); ?>
