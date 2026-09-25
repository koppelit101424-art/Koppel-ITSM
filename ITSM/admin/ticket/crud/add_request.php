<?php
include __DIR__ . '/../../../includes/auth.php';
include __DIR__ . '/../../../includes/db.php';

$lastLMR = $conn->query("
    SELECT MAX(CAST(SUBSTRING(lmr_no, 4) AS UNSIGNED)) as max_id 
    FROM request_tb
    WHERE lmr_no LIKE 'IT-%'
");

$newLMR = 'IT-000001';

if ($lastLMR) {
    $row = $lastLMR->fetch_assoc();
    $num = (int)$row['max_id'] + 1;
    $newLMR = 'IT-' . str_pad($num, 6, '0', STR_PAD_LEFT);
}

$success = $error = '';
$errors = [];
$ticket_id = $_GET['ticket_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Shared fields (same for all items)
    $lmr_no = trim($_POST['lmr_no'] ?? '');
    $user_id = intval($_POST['user_id'] ?? 0);
    $requestor = trim($_POST['requestor'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $created_by = intval($_POST['created_by'] ?? 0);
    $ticket_id = intval($_POST['ticket_id'] ?? 0);
    // Validate shared fields
    if (empty($lmr_no)) $errors[] = "LMR No is required";
    if ($user_id <= 0) $errors[] = "User is required";
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
        `user_id`,
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
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), ?, ?)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) die("Prepare failed: " . $conn->error);

    foreach ($validItems as $itemData) {
        $stmt->bind_param(
            "ssssssdssssii",
            $lmr_no,
            $user_id,
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
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
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
<input type="text" class="form-control" name="lmr_no"
    value="<?= htmlspecialchars($_POST['lmr_no'] ?? $newLMR) ?>" readonly>
</div>

<div class="col-md-4">
    <label class="form-label">Fullname</label>
    <?php $users = $conn->query("SELECT user_id, fullname, department FROM user_tb ORDER BY fullname ASC"); ?>
        <select name="user_id" id="user_id" class="form-select form-select" required>
        <option value="">Select User</option>
        <?php while ($u = $users->fetch_assoc()): ?>
        <option value="<?= $u['user_id'] ?>"   
        data-fullname="<?= htmlspecialchars($u['fullname']) ?>"
        data-department="<?= htmlspecialchars($u['department']) ?>">
        <?= $u['fullname'] ?></option>
        <?php endwhile; ?>
    </select>
</div>
<div class="col-md-4">
    <label class="form-label">Reference Ticket</label><br>
<input type="text" class="form-control" name="ticket_id" value="<?= htmlspecialchars($ticket_id) ?>">
</div>
<!-- <div class="col-md-4">
<label class="form-label">Requestor *</label>

</div>

<div class="col-md-4">
<label class="form-label">Department *</label> </div>-->
<input type="hidden" name="requestor" id="requestor">
<input type="hidden" name="department" id="department">

</div>

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

$(document).ready(function() {
    $('#user_id').select2({
        placeholder: "Search user...",
        allowClear: true,
        width: '100%'
    });
    $('#user_id').trigger('change');
});
$('#user_id').on('change', function() {
    let selected = $(this).find(':selected');

    let fullname = selected.data('fullname') || '';
    let department = selected.data('department') || '';

    $('#requestor').val(fullname);
    $('#department').val(department);
});
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
