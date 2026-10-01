<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

mysqli_report(MYSQLI_REPORT_OFF);

include 'includes/auth.php';
include 'includes/db.php';

    $user_id = $_SESSION['user_id'];

    $userQuery = $conn->prepare("SELECT fullname, department FROM user_tb WHERE user_id = ?");
    $userQuery->bind_param("i", $user_id);
    $userQuery->execute();
    $result = $userQuery->get_result();
    $user = $result->fetch_assoc();

// ==========================================
// GENERATE LMR NUMBER
// Format: DEPT-YYMM-00001
// Example: MKTG-2610-00001
// ==========================================

    // Department prefixes
    $departmentPrefixes = [
        'Marketing'       => 'MKTG',
        'Sales'           => 'SALES',
        'PDED'            => 'PDED',
        'PDED OEM'        => 'OEM',
        'PDED DESIGN'     => 'DESIGN',
        'Purchasing'      => 'PURCH',
        'Accounting'      => 'ACTG',
        'Information Technology'              => 'IT',
        'Human Resource'  => 'HR',
        'HR'              => 'HR',
        'Logistics'       => 'LOGI',
    ];

    // Get department from logged-in user
    $department = trim($user['department'] ?? '');

    // Get department prefix
    $prefix = $departmentPrefixes[$department] ?? 'PURCH';

    // Current year and month
    $yearMonth = date('ym');

    // Prefix for this month's LMR
    $lmrPrefix = $prefix . '-' . $yearMonth;

    // Find the latest number for this department and month
    $lastLMR = $conn->prepare("
        SELECT MAX(
            CAST(SUBSTRING_INDEX(lmr_no, '-', -1) AS UNSIGNED)
        ) AS max_id
        FROM purch_request_tb
        WHERE lmr_no LIKE CONCAT(?, '-%')
    ");

    $lastLMR->bind_param("s", $lmrPrefix);
    $lastLMR->execute();

    $result = $lastLMR->get_result();
    $row = $result->fetch_assoc();

    // Increment number
    $num = ((int)($row['max_id'] ?? 0)) + 1;

    // Generate LMR
    $newLMR = $lmrPrefix . '-' . str_pad($num, 5, '0', STR_PAD_LEFT);

    $lastLMR->close();

$success = $error = '';
$errors = [];
// $ticket_id = $_GET['ticket_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $errors = [];

    // ==========================================
    // SHARED FIELDS
    // ==========================================

    $lmr_no = trim($_POST['lmr_no'] ?? '');

    // Get these from SESSION instead of trusting hidden fields
    $user_id = (int)($_SESSION['user_id'] ?? 0);
    $requestor = trim($user['fullname'] ?? '');
    $department = trim($user['department'] ?? '');
    $created_by = $user_id;
    // Default purchaser
    $purchaser_id = 1;


    // ==========================================
    // VALIDATE SHARED FIELDS
    // ==========================================

    if ($lmr_no === '') {
        $errors[] = "LMR No is required.";
    }

    if ($user_id <= 0) {
        $errors[] = "Invalid user.";
    }

    if ($requestor === '') {
        $errors[] = "Requestor is required.";
    }

    if ($department === '') {
        $errors[] = "Department is required.";
    }

    if ($created_by <= 0) {
        $errors[] = "Invalid request creator.";
    }


    // ==========================================
    // GET ITEM ARRAYS
    // ==========================================

    $items = $_POST['item'] ?? [];
    $descriptions = $_POST['description'] ?? [];
    $quantities = $_POST['quantity'] ?? [];
    $uoms = $_POST['uom'] ?? [];
    $dates_needed = $_POST['date_needed'] ?? [];
    $remarks_list = $_POST['remarks'] ?? [];
    $statuses = $_POST['status'] ?? [];


    // ==========================================
    // VALIDATE ITEMS
    // ==========================================

    $validItems = [];

    foreach ($items as $i => $item) {

        $item = trim($item);

        // Ignore empty rows
        if ($item === '') {
            continue;
        }

        $desc = trim($descriptions[$i] ?? '');
        $qty = (float)($quantities[$i] ?? 0);
        $uom = trim($uoms[$i] ?? '');
        $date_needed = trim($dates_needed[$i] ?? '');
        $remarks = trim($remarks_list[$i] ?? '');
        $status = 'Pending';
        $po_no = '';
        $priority = 'Medium';

        $itemHasError = false;

        if ($desc === '') {
            $errors[] = "Description required for item: {$item}";
            $itemHasError = true;
        }

        if ($qty <= 0) {
            $errors[] = "Quantity must be greater than 0 for item: {$item}";
            $itemHasError = true;
        }

        if ($uom === '') {
            $errors[] = "UoM required for item: {$item}";
            $itemHasError = true;
        }

        if ($date_needed === '') {
            $errors[] = "Date Needed required for item: {$item}";
            $itemHasError = true;
        }

        if (!$itemHasError) {

            $validItems[] = [
                'item' => $item,
                'desc' => $desc,
                'qty' => $qty,
                'uom' => $uom,
                'date_needed' => $date_needed,
                'remarks' => $remarks,
                'status' => $status,
                'po_no' => $po_no,
                'priority' => $priority
            ];
        }
    }


    // ==========================================
    // REQUIRE AT LEAST ONE ITEM
    // ==========================================

    if (count($validItems) === 0) {
        $errors[] = "At least one valid item is required.";
    }


    // ==========================================
    // INSERT
    // ==========================================

    if (empty($errors)) {

        $sql = "
            INSERT INTO purch_request_tb
            (
                lmr_no,
                user_id,
                requestor,
                department,
                item,
                description,
                quantity,
                UoM,
                date_needed,
                remarks,
                status,
                date_created,
                date_updated,
                created_by,
                purchaser_id,
                po_no,
                priority
            )
            VALUES
            (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                NOW(),
                NOW(),
                ?, ?, ?, ?
            )
        ";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {

            $errors[] = "Prepare failed: " . $conn->error;

        } else {

            // Start transaction
            $conn->begin_transaction();

            $insertedCount = 0;

            foreach ($validItems as $itemData) {

                $stmt->bind_param(
                    "sissssdssssiiss",
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
                    $status,
                    $created_by,
                    $purchaser_id,
                    $po_no,
                    $priority
                );

                if (!$stmt->execute()) {

                    $errors[] =
                        "Failed to insert item '{$itemData['item']}': " .
                        $stmt->error;

                    // Stop inserting if one item fails
                    break;
                }

                $insertedCount++;
            }

            // ==========================================
            // COMMIT / ROLLBACK
            // ==========================================

                if (empty($errors)) {

                    $conn->commit();

                    $stmt->close();

                    echo "<script>
                        window.location.href = '?page=ticket/purch_lmr';
                    </script>";
                    exit;
                }
                else {

                $conn->rollback();

                $stmt->close();
            }
        }
    }
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
<span>Add Purchasing LMR (Multiple Items)</span>
<a href="?page=ticket/purch_lmr"  class="btn btn-secondary btn-sm">
<!-- <i class="fas fa-arrow-left me-1"></i> -->
 Back to Requests
</a>
</div>

<div class="card-body">

<?php 
    if (!empty($errors)):        
?>
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
<input type="text"
       class="form-control"
       name="lmr_no"
       value="<?= htmlspecialchars($newLMR) ?>"
       >
</div>

<div class="col-md-4">
    <label class="form-label">Fullname</label>
    <input type="hidden" name="user_id" value="<?= $_SESSION['user_id'] ?>">
    <input type="text" class="form-control" value="<?= $_SESSION['fullname'] ?>">
</div>
<div class="col-md-4">
    <label class="form-label">Department</label>
    <input type="hidden" name="user_id" value="<?= $_SESSION['user_id'] ?>">
    <input type="text" class="form-control" value="<?= $_SESSION['department'] ?>">
</div>
<!-- <div class="col-md-4">
    <label class="form-label">Reference Ticket</label><br>
    <input type="text" class="form-control" name="ticket_id" value="<?= htmlspecialchars($ticket_id) ?>">
</div> -->
<!-- <div class="col-md-4">
<label class="form-label">Requestor *</label>

</div>

<div class="col-md-4">
<label class="form-label">Department *</label> </div>-->
<input type="hidden" name="requestor" value="<?= htmlspecialchars($user['fullname'] ?? '') ?>">
<input type="hidden" name="department" value="<?= htmlspecialchars($user['department'] ?? '') ?>">

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
            <input type="text" class="form-control" name="item[]" placeholder="" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Description *</label>
            <textarea class="form-control" name="description[]" rows="1" placeholder=""required></textarea>
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
            <input type="date" class="form-control" name="date_needed[]" value="" required>
        </div>
    </div>
    <div class="row mt-2">
        <div class="col-md-10">
            <label class="form-label">Remarks</label>
            <textarea class="form-control" name="remarks[]" rows="5" placeholder=""></textarea>

        </div>
          <div class="col-md-2"><br><br>
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
