<?php
include 'includes/auth.php';
include 'includes/db.php';

$created_by = $_SESSION['user_id'];

// Get logged-in user's department
$userQuery = $conn->prepare("
    SELECT department, company
    FROM user_tb
    WHERE user_id = ?
");

$userQuery->bind_param("i", $created_by);
$userQuery->execute();

$userResult = $userQuery->get_result();
$currentUser = $userResult->fetch_assoc();

$currentDepartment = trim($currentUser['department'] ?? '');
$currentCompany = trim($currentUser['company'] ?? '');

$userQuery->close();


// ==========================================
// PURCHASING → SHOW ALL
// OTHER DEPARTMENTS → SHOW ASSIGNED ONLY
// ==========================================

if (
    strcasecmp(trim($currentDepartment), 'Purchasing') === 0
    || (
        isset($_SESSION['user_type'])
        && strcasecmp(trim($_SESSION['user_type']), 'admin') === 0
    )
) {

// Purchasing users and admins can see all requests
$sql = "
    SELECT 
        r.request_id,
        r.user_id,
        r.lmr_no,
        u.fullname,
        u.company,
        r.department,
        r.item,
        r.description,
        r.quantity,
        r.UoM,
        r.date_needed,
        r.remarks,
        r.date_created,
        r.status,
        r.created_by,
        r.purchaser_id
    FROM purch_request_tb r
    LEFT JOIN user_tb u 
        ON r.created_by = u.user_id
    ORDER BY r.date_created ASC
";

$stmt = $conn->prepare($sql);


}else {

    // Non-Purchasing users can only see requests
    // assigned to them
$sql = "
    SELECT 
        r.request_id,
        r.user_id,
        u.fullname,
        r.lmr_no,
        u.company AS company,
        r.department,
        r.item,
        r.description,
        r.quantity,
        r.UoM,
        r.date_needed,
        r.remarks,
        r.date_created,
        r.status,
        r.created_by,
        r.purchaser_id
    FROM purch_request_tb r
    LEFT JOIN user_tb u 
        ON r.created_by = u.user_id
    WHERE r.created_by = ?
    ORDER BY r.date_created ASC
";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $created_by);
}

$stmt->execute();
$requests = $stmt->get_result();
?>


<style>
.table-hover tbody tr:hover { background-color: #f1f1f1; }
.badge-proceed {
    background-color: #198754;
    color: #fff;
}

.badge-checking {
    background-color: #0d6efd;
    color: #fff;
}

.badge-negotiation {
    background-color: #6f42c1;
    color: #fff;
}

.badge-draft {
    background-color: #fd7e14;
    color: #fff;
}

.badge-canceled {
    background-color: #dc3545;
    color: #fff;
}

.badge-pending {
    background-color: #ffc107;
    color: #000;
}

.badge-closed {
    background-color: #6c757d;
    color: #fff;
}

.status-filter.active { background-color: #1E3A8A; color: #fff; }
.status-filter.active:hover { background-color: #1E3A8A; color: #fff; }


.custom-menu {
    display: none;
    position: absolute;
    background: white;
    border: 1px solid #ddd;
    box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    z-index: 10000;
    min-width: 180px;
    border-radius: 4px;
    padding: 5px 0;
}
.custom-menu a {
    display: block;
    padding: 8px 16px;
    color: #333;
    text-decoration: none;
}
.custom-menu a:hover { background-color: #f0f8ff; }
.btn-outline-blue {
    color: #1E3A8A;
    border-color: #1E3A8A;
}
.btn-outline-blue:hover,
.btn-outline-blue.active {
    background-color: #1E3A8A;
    color: white;
}
.unassigned-purchaser {
    color: #dc3545;
    font-weight: 600;
}

</style>

<?php
$isPurchasing =
    strcasecmp(trim($currentDepartment), 'Purchasing') === 0
    || (
        isset($_SESSION['user_type'])
        && strcasecmp(trim($_SESSION['user_type']), 'admin') === 0
    );
?>

<div class="card-header d-flex justify-content-between align-items-center text-white">
    <span>Purchasing LMR</span>

    <div class="d-flex gap-2">
        <a href="?page=ticket/includes/add_purch_request"
           class="btn btn-sm btn-primary">
            <i class="fas fa-plus me-1"></i>
            Create LMR
        </a>

        <?php if ($isPurchasing): ?>
            <button type="button"
                    class="btn btn-info btn-sm"
                    id="exportPurchasingCSV">
                <i class="fas fa-file-csv me-1"></i>
                Export CSV
            </button>
        <?php endif; ?>
    </div>
</div>



<div class="card-body">
<?php
    $created_by = $_SESSION['user_id'];

    // Get logged-in user's department
    $userQuery = $conn->prepare("
        SELECT fullname, department 
        FROM user_tb 
        WHERE user_id = ?
    ");

    $userQuery->bind_param("i", $created_by);
    $userQuery->execute();

    $userResult = $userQuery->get_result();
    $currentUser = $userResult->fetch_assoc();

    $currentDepartment = trim($currentUser['department'] ?? '');

    $userQuery->close();

$departmentQuery = $conn->query("
    SELECT DISTINCT department
    FROM purch_request_tb
    WHERE department IS NOT NULL
      AND TRIM(department) != ''
    ORDER BY department ASC
");

$departments = [];

while ($departmentRow = $departmentQuery->fetch_assoc()) {
    $departments[] = $departmentRow['department'];
}

$companyQuery = $conn->query("
    SELECT DISTINCT company
    FROM user_tb
    WHERE company IS NOT NULL
      AND TRIM(company) != ''
    ORDER BY company ASC
");

$companies = [];

while ($companyRow = $companyQuery->fetch_assoc()) {
    $companies[] = $companyRow['company'];
}

?>

    <?php if (strcasecmp($currentDepartment, 'Purchasing') === 0): ?>

        <!-- ==========================================
             PURCHASING FILTERS
        =========================================== -->

        <div class="row g-3 align-items-end">

            <!-- Request Scope -->
            <div class="col-md-2">
                <label class="form-label">Request View</label>
                <select id="requestViewFilter" class="form-select">
                    <option value="">All Requests</option>
                    <option value="assigned">Assigned to Me</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Company</label>

                <select id="companyFilter" class="form-select">

                    <option value="">All Companies</option>

                    <?php foreach ($companies as $company): ?>

                        <option
                            value="<?= htmlspecialchars(strtolower(trim($company))) ?>"
                            <?= strcasecmp(trim($company), $currentCompany) === 0 ? 'selected' : '' ?>
                        >
                            <?= htmlspecialchars($company) ?>
                        </option>

                    <?php endforeach; ?>

                </select>
            </div>

            <!-- Department -->
            <div class="col-md-2">
                  <label class="form-label">Departments</label>
                <select id="departmentFilter" class="form-select">
                    <option value="">All Departments</option>

                    <?php foreach ($departments as $department): ?>
                        <option value="<?= htmlspecialchars(strtolower(trim($department))) ?>">
                            <?= htmlspecialchars($department) ?>
                        </option>
                    <?php endforeach; ?>
                </select>


            </div>

            <!-- Status -->
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select id="statusSelectFilter" class="form-select">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="checking requirements">Checking Requirements</option>
                    <option value="canvassing">Canvassing</option>
                    <option value="negotiation">Negotiation</option>
                    <option value="under discussion">Under Discussion</option>
                    <option value="draft">Draft </option>
                    <option value="final">Final </option>
                    <option value="end">End </option>
                    <option value="closed">Closed</option>
                </select>
            </div>

            <!-- Date From -->
            <div class="col-md-2">
                <label class="form-label">Date From</label>
                <input type="date" id="dateFrom" class="form-control">
            </div>

            <!-- Date To -->
            <div class="col-md-2">
                <label class="form-label">Date To</label>
                <input type="date" id="dateTo" class="form-control">
            </div>

        </div>

    <?php else: ?>

        <!-- ==========================================
             NON-PURCHASING USERS
        =========================================== -->

        <div class="d-flex flex-wrap gap-2 mb-3">

            <button
                class="btn btn-outline-blue btn-sm status-filter active"
                data-status="">
                All
            </button>

            <button
                class="btn btn-outline-blue btn-sm status-filter"
                data-status="pending">
                Pending
            </button>

            <button
                class="btn btn-outline-blue btn-sm status-filter"
                data-status="approved">
                Approved
            </button>

            <button
                class="btn btn-outline-blue btn-sm status-filter"
                data-status="rejected">
                Rejected
            </button>

        </div>

    <?php endif; ?>

</div>


            <div class="table-responsive">
                <table id="requestsTable" class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>LMR No.</th>
                            <th>User</th>
                            <th>Department</th>
                            <th>Item</th>
                            <th>Qty</th>
                            <th>UoM</th>
                            <th>Assigned to</th>
                            <!-- <th>Requested by</th> -->
                            <th>Status</th>
                            <th>Date Created</th>
                            <th>Date Needed</th>
                            <!-- <th>Remarks</th> -->
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            
                            ?>
                        <?php if ($requests->num_rows > 0): ?>
                            <?php $i = 1; while ($row = $requests->fetch_assoc()): 
                                $purchaser_id = (int)$row['purchaser_id'];

                            if ($purchaser_id == 1) {
                                $purchaser_name = 'Unassigned';
                            } else {
                                $stmtPurchaser = $conn->prepare("
                                    SELECT fullname
                                    FROM user_tb
                                    WHERE user_id = ?
                                    LIMIT 1
                                ");

                                $stmtPurchaser->bind_param("i", $purchaser_id);
                                $stmtPurchaser->execute();

                                $purchaserResult = $stmtPurchaser->get_result();
                                $purchaser = $purchaserResult->fetch_assoc();

                                $purchaser_name = $purchaser['fullname'] ?? 'Unknown';

                                $stmtPurchaser->close();
                            }

                            $createdById = (int)$row['created_by'];

                            $stmtRequestor = $conn->prepare("
                                SELECT fullname
                                FROM user_tb
                                WHERE user_id = ?
                                LIMIT 1
                            ");

                            $stmtRequestor->bind_param("i", $createdById);
                            $stmtRequestor->execute();

                            $requestorResult = $stmtRequestor->get_result();
                            $requestor = $requestorResult->fetch_assoc();

                            $requestor_name = $requestor['fullname'] ?? 'Unknown';

                            $stmtRequestor->close();
                                ?>
 
                        <tr
                            data-request-id="<?= (int)$row['request_id'] ?>"
                            data-lmr-no="<?= htmlspecialchars($row['lmr_no'], ENT_QUOTES) ?>"
                            data-status="<?= htmlspecialchars(strtolower(trim($row['status'])), ENT_QUOTES) ?>"
                            data-company="<?= htmlspecialchars($row['company'], ENT_QUOTES) ?>"
                            data-department="<?= htmlspecialchars($row['department'], ENT_QUOTES) ?>"
                            data-item="<?= htmlspecialchars($row['item'], ENT_QUOTES) ?>"
                            data-description="<?= htmlspecialchars($row['description'] ?? '', ENT_QUOTES) ?>"
                            data-quantity="<?= htmlspecialchars($row['quantity'], ENT_QUOTES) ?>"
                            data-uom="<?= htmlspecialchars($row['UoM'], ENT_QUOTES) ?>"
                            data-date-needed="<?= htmlspecialchars($row['date_needed'], ENT_QUOTES) ?>"
                            data-date-created="<?= htmlspecialchars($row['date_created'], ENT_QUOTES) ?>"
                            data-remarks="<?= htmlspecialchars($row['remarks'] ?? '', ENT_QUOTES) ?>"
                            data-purchaser-id="<?= (int)$row['purchaser_id'] ?>"
                            data-purchaser-name="<?= htmlspecialchars($purchaser_name, ENT_QUOTES) ?>"
                            data-requestor-name="<?= htmlspecialchars($requestor_name, ENT_QUOTES) ?>"
                            style="cursor:pointer;"
                        >

                                    <td><?= $i++ ?></td>
                                    <td><?= htmlspecialchars($row['lmr_no']) ?></td>
                                    <td><?= htmlspecialchars($requestor_name) ?></td>
                                 
                                    <td><?= htmlspecialchars($row['department']) ?></td>
                                    <td><?= htmlspecialchars($row['item']) ?></td>

                                    <td><?= $row['quantity'] ?></td>
                                    <td><?= htmlspecialchars($row['UoM']) ?></td>
                                  <td>
                                        <?php if ($purchaser_id == 1): ?>
                                            <span class="unassigned-purchaser">
                                                Unassigned
                                            </span>
                                        <?php else: ?>
                                            <?= htmlspecialchars($purchaser_name) ?>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?php 
                                            $status = strtolower(trim($row['status']));

                                            switch ($status) {
                                                case 'proceed request':
                                                    $statusClass = 'badge-proceed';
                                                    break;

                                                case 'checking requirements':
                                                case 'canvassing':
                                                    $statusClass = 'badge-checking';
                                                    break;

                                                case 'negotiation':
                                                case 'under discussion':
                                                    $statusClass = 'badge-negotiation';
                                                    break;

                                                case 'draft':
                                                    $statusClass = 'badge-draft';
                                                    break;

                                                case 'final':
                                                    $statusClass = 'badge-proceed';
                                                    break;

                                                case 'pending':
                                                    $statusClass = 'badge-pending';
                                                    break;

                                                case 'end':
                                                case 'closed':
                                                    $statusClass = 'badge-closed';
                                                    break;

                                                case 'canceled':
                                                    $statusClass = 'badge-canceled';
                                                    break;

                                                default:
                                                    $statusClass = 'badge-pending';
                                                    break;
                                            }
                                        ?>

                                        <span class="badge <?= $statusClass ?>" style="width: 100%;">
                                            <?= ucfirst($row['status']) ?>
                                        </span>
                                    </td>
                                    
                                    <td><?= date('m-d-Y', strtotime($row['date_created'])) ?></td>
                                    <td><?= date('m-d-Y', strtotime( $row['date_needed'])) ?></td>
                                    <!-- <td><?= htmlspecialchars($row['remarks'] ?? '-') ?></td> -->
                                    <td onclick="event.stopPropagation();">

                                        <a href="?page=ticket/view_request&request_id=<?= $row['request_id'] ?>"
                                        class="btn btn-sm btn-primary"
                                        title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        <?php
                                        $isLocalUser =
                                            (int)$row['created_by'] === (int)$_SESSION['user_id'];

                                        $isPending =
                                            strcasecmp(trim($row['status']), 'pending') === 0;
                                        ?>

                                        <?php if ($isLocalUser && $isPending): ?>
                                            <a href="?page=ticket/includes/edit_request&request_id=<?= (int)$row['request_id'] ?>"
                                            class="btn btn-sm btn-warning"
                                            title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        <?php endif; ?>

                                        <!-- <button
                                            type="button"
                                            class="btn btn-sm btn-success btn-print"
                                            data-lmr="<?= htmlspecialchars($row['lmr_no']) ?>"
                                            data-status="<?= htmlspecialchars(strtolower(trim($row['status']))) ?>"
                                            title="Print">
                                            <i class="fas fa-print"></i>
                                        </button> -->

                                    </td>

                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                        
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<!-- Context Menu -->
<!-- <div id="contextMenu" class="custom-menu">
    <a href="#" id="deleteRequest" class="text-danger"><i class="fas fa-trash"></i> Delete Request</a>
</div> -->

<!-- =========================================================
     REQUEST DETAILS MODAL
========================================================= -->
<div class="modal fade modal-xl" id="requestDetailsModal" tabindex="-1"
     aria-labelledby="requestDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" >
    <div class="modal-content border-0 shadow-xl">

            <div class="modal-header bg-gradient-primary  text-white">
                <div>
                    <!-- <h5 class="modal-title" id="requestDetailsModalLabel">
                        LMR Request Details
                    </h5> -->
                    <h5 id="modalLmrNo"></h5>
                </div>

                <button type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal"
                        aria-label="Close">
                </button>
            </div>

            <div class="modal-body">

                <!-- Hidden request ID -->
                <input type="hidden" id="modalRequestId">

                <!-- =================================================
                     REQUEST INFORMATION
                ================================================== -->
                <div class="row g-3">

                    <div class="col-md-4">
                        <label class="form-label fw-bold">LMR No.</label>
                        <input type="text"
                               id="modalLmr"
                               class="form-control"
                               readonly>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Company</label>
                        <input type="text"
                               id="modalCompany"
                               class="form-control"
                               readonly>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Department</label>
                        <input type="text"
                               id="modalDepartment"
                               class="form-control"
                               readonly>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Item</label>
                        <input type="text"
                               id="modalItem"
                               class="form-control"
                               readonly>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">Quantity</label>
                        <input type="text"
                               id="modalQuantity"
                               class="form-control"
                               readonly>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">UoM</label>
                        <input type="text"
                               id="modalUom"
                               class="form-control"
                               readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Requested By</label>
                        <input type="text"
                               id="modalRequestor"
                               class="form-control"
                               readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Date Needed</label>
                        <input type="text"
                               id="modalDateNeeded"
                               class="form-control"
                               readonly>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Date Created</label>
                        <input type="text"
                               id="modalDateCreated"
                               class="form-control"
                               readonly>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-bold">Description</label>
                        <textarea id="modalDescription"
                                  class="form-control"
                                  rows="3"
                                  readonly></textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-bold">Remarks</label>
                        <textarea id="modalRemarks"
                                  class="form-control"
                                  rows="3"
                                  readonly></textarea>
                    </div>



                    <!-- =================================================
                         PURCHASING CONTROLS
                    ================================================== -->
                    <?php
                    $isPurchasing =
                        strcasecmp(trim($currentDepartment), 'Purchasing') === 0
                        || (
                            isset($_SESSION['user_type'])
                            && strcasecmp(trim($_SESSION['user_type']), 'admin') === 0
                        );
                    ?>

                    <?php if ($isPurchasing): ?>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">
                                Assigned Purchaser
                            </label>

                            <div class="input-group">

                                <select id="modalPurchaser"
                                        class="form-select">

                                    <option value="1">
                                        Unassigned
                                    </option>

                                    <?php
                                    $purchaserQuery = $conn->query("
                                        SELECT user_id, fullname, company
                                        FROM user_tb
                                        WHERE department = 'Purchasing' AND is_active = 1
                                        ORDER BY company ASC
                                    ");

                                    while ($purchaser = $purchaserQuery->fetch_assoc()):
                                    ?>

                                        <option value="<?= (int)$purchaser['user_id'] ?>">
                                            <?= htmlspecialchars($purchaser['company']) ?>-
                                            <?= htmlspecialchars($purchaser['fullname']) ?>
                                        </option>

                                    <?php endwhile; ?>

                                </select>

                                <button type="button"
                                        id="assignToMeBtn"
                                        class="btn btn-outline-primary">
                                    Assign to Me
                                </button>

                            </div>
                        </div>

                        <div class="col-md-6">

                            <label class="form-label fw-bold">
                                Status
                            </label>

                            <select id="modalStatus"
                                    class="form-select">

                                <option value="pending">
                                    Pending
                                </option>

                                <option value="checking requirements">Checking Requirements</option>
                                <option value="canvassing">Canvassing</option>
                                <option value="negotiation">Negotiation</option>
                                <option value="under discussion">Under Discussion</option>
                                <option value="draft">Draft </option>
                                <option value="final">Final </option>
                                <option value="end">End </option>
                                <option value="closed">Closed</option>

                            </select>

                        </div>

                    <?php else: ?>

                        <!-- REQUESTOR VIEW -->

                        <div class="col-md-6">

                            <label class="form-label fw-bold">
                                Assigned Purchaser
                            </label>

                            <input type="text"
                                   id="modalPurchaserDisplay"
                                   class="form-control"
                                   readonly>

                        </div>

                        <div class="col-md-6">

                            <label class="form-label fw-bold">
                                Status
                            </label>

                            <input type="text"
                                   id="modalStatusDisplay"
                                   class="form-control"
                                   readonly>

                        </div>

                    <?php endif; ?>

                </div>

                <!-- =================================================
                     SAVE MESSAGE
                ================================================== -->
                <div id="modalSaveMessage"
                     class="alert d-none mt-4 mb-0">
                </div>

            </div>

            <div class="modal-footer">

                <button type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">
                    Close
                </button>

                <?php if ($isPurchasing): ?>

                    <button type="button"
                            id="saveRequestChanges"
                            class="btn btn-primary"
                            disabled>
                        <i class="fas fa-save me-1"></i>
                        Save Changes
                    </button>

                <?php endif; ?>

            </div>

        </div>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<!-- <script>
$(document).ready(function () {
    const table = $('#requestsTable').DataTable({
        pageLength: 10,
        order: [[0, "desc"]],
        columnDefs: [{ orderable: false, targets: [5, 9] }]
    });

    // Status filter
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        const selectedStatus = $('.status-filter.active').data('status');
        const rowStatus = $(table.row(dataIndex).node()).data('status');
        if (!selectedStatus) return true;
        return rowStatus === selectedStatus;
    });

    $('.status-filter').on('click', function () {
        $('.status-filter').removeClass('active');
        $(this).addClass('active');
        table.draw();
    });
});

// Context menu
let currentRequestId = null;

$(function () {

    $('.btn-print').on('click', function (e) {

        e.preventDefault();
        e.stopPropagation();

        const status = ($(this).data('status') || '').toLowerCase().trim();
        const lmr = $(this).data('lmr');

        if (status !== 'proceed request') {
            alert('Printing is only available when the request status is "Proceed Request".');
            return;
        }

        window.open(
            '?page=ticket/includes/print_request&lmr_no=' + encodeURIComponent(lmr),
            '_blank'
        );

    });

});
</script> -->
<script>
$(document).ready(function () {

    const table = $('#requestsTable').DataTable({
        pageLength: 10,
        order: [[0, "desc"]],
        columnDefs: [
            { orderable: false, targets: [5, 9, 10] }
        ]
    });


    // =====================================================
    // CUSTOM FILTER
    // =====================================================

    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {

        // Only apply to our table
        if (settings.nTable.id !== 'requestsTable') {
            return true;
        }

        const row = table.row(dataIndex).node();

        if (!row) {
            return true;
        }

        const $row = $(row);

        // Row data
        const rowStatus = ($row.data('status') || '').toString().toLowerCase().trim();

        const rowCompany = ($row.data('company') || '')
            .toString()
            .toLowerCase()
            .trim();
        const rowDepartment = ($row.data('department') || '')
            .toString()
            .toLowerCase()
            .trim();

        const purchaserId = ($row.data('purchaser-id') || '')
            .toString()
            .trim();

        const rowDate = ($row.data('date-created') || '')
            .toString()
            .trim();


        // =================================================
        // PURCHASING FILTERS
        // =================================================

        const requestView = $('#requestViewFilter').val();
        const department = $('#departmentFilter').val();
        const company = $('#companyFilter').val();

        const status = $('#statusSelectFilter').val();
        const dateFrom = $('#dateFrom').val();
        const dateTo = $('#dateTo').val();


        // -----------------------------------------------
        // Assigned to Me
        // -----------------------------------------------

        if (requestView === 'assigned') {

            // PHP session user ID
            const currentUserId = '<?= (int)$_SESSION['user_id'] ?>';

            if (purchaserId !== currentUserId) {
                return false;
            }
        }

            if (company && rowCompany !== company) {
                return false;
            }


        // -----------------------------------------------
        // Department
        // -----------------------------------------------

        if (department && rowDepartment !== department) {
            return false;
        }


        // -----------------------------------------------
        // Status
        // -----------------------------------------------

        if (status && rowStatus !== status) {
            return false;
        }


        // -----------------------------------------------
        // Date From
        // -----------------------------------------------

        if (dateFrom && rowDate < dateFrom) {
            return false;
        }


        // -----------------------------------------------
        // Date To
        // -----------------------------------------------

        if (dateTo && rowDate > dateTo) {
            return false;
        }


        return true;
    });


    // =====================================================
    // PURCHASING FILTERS - ON CHANGE
    // =====================================================

    $('#companyFilter, #requestViewFilter, #departmentFilter, #statusSelectFilter, #dateFrom, #dateTo')
        .on('change', function () {
            table.draw();
        });



    // =====================================================
    // NON-PURCHASING STATUS FILTER
    // =====================================================

    $('.status-filter').on('click', function () {

        $('.status-filter').removeClass('active');
        $(this).addClass('active');

        table.draw();
    });


    // =====================================================
    // NON-PURCHASING STATUS FILTER
    // =====================================================

    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {

        if (settings.nTable.id !== 'requestsTable') {
            return true;
        }

        // If Purchasing filters exist, don't use button filter
        if ($('#statusSelectFilter').length) {
            return true;
        }

        const selectedStatus = $('.status-filter.active').data('status');



        if (!selectedStatus) {
            return true;
        }

        const row = table.row(dataIndex).node();

        if (!row) {
            return true;
        }

        const rowStatus = ($(row).data('status') || '')
            .toString()
            .toLowerCase()
            .trim();

        return rowStatus === selectedStatus;
    });


    // =====================================================
    // PRINT
    // =====================================================

    $('.btn-print').on('click', function (e) {

        e.preventDefault();
        e.stopPropagation();

        const status = ($(this).data('status') || '')
            .toString()
            .toLowerCase()
            .trim();

        const lmr = $(this).data('lmr');

        if (status !== 'proceed request') {
            alert('Printing is only available when the request status is "Proceed Request".');
            return;
        }

        window.open(
            '?page=ticket/includes/print_request&lmr_no=' +
            encodeURIComponent(lmr),
            '_blank'
        );
    });

  
    // =====================================================
// EXPORT PURCHASING REQUESTS TO CSV
// Exports only the currently filtered/visible requests
// =====================================================

    function exportPurchasingCSV() {

        const rows = [];

        // CSV Headers
        const headers = [
            "LMR No.",
            "Company",
            "Requested By",
            "Department",
            "Item",
            "Description",
            "Quantity",
            "UoM",
            "Assigned To",
            "Status",
            "Date Created",
            "Date Needed",
            "Remarks"
        ];

        rows.push(headers);

        // -------------------------------------------------
        // Get rows currently displayed by DataTables
        // -------------------------------------------------

        table.rows({
            search: 'applied',
            order: 'applied'
        }).every(function () {

            const row = this.node();

            if (!row) {
                return;
            }

            const $row = $(row);

            // -------------------------------------------------
            // Get data from data-* attributes
            // -------------------------------------------------

            const lmrNo =
                $row.attr('data-lmr-no') || '';

            const company =
                $row.attr('data-company') || '';

            const requestor =
                $row.attr('data-requestor-name') || '';

            const department =
                $row.attr('data-department') || '';

            const item =
                $row.attr('data-item') || '';

            const description =
                $row.attr('data-description') || '';

            const quantity =
                $row.attr('data-quantity') || '';

            const uom =
                $row.attr('data-uom') || '';

            const purchaser =
                $row.attr('data-purchaser-name') || 'Unassigned';

            const status =
                $row.attr('data-status') || '';

            const dateCreated =
                $row.attr('data-date-created') || '';

            const dateNeeded =
                $row.attr('data-date-needed') || '';

            const remarks =
                $row.attr('data-remarks') || '';

            // -------------------------------------------------
            // Format status
            // -------------------------------------------------

            const formattedStatus = status
                ? status.replace(/\b\w/g, function (letter) {
                    return letter.toUpperCase();
                })
                : '';

            // -------------------------------------------------
            // Format dates
            // -------------------------------------------------

            function formatCSVDate(value) {

                if (!value) {
                    return '';
                }

                const date = new Date(value);

                if (isNaN(date.getTime())) {
                    return value;
                }

                const month = String(
                    date.getMonth() + 1
                ).padStart(2, '0');

                const day = String(
                    date.getDate()
                ).padStart(2, '0');

                const year = date.getFullYear();

                return month + '-' + day + '-' + year;
            }

            // -------------------------------------------------
            // Add row
            // -------------------------------------------------

            rows.push([
                lmrNo,
                company,
                requestor,
                department,
                item,
                description,
                quantity,
                uom,
                purchaser,
                formattedStatus,
                formatCSVDate(dateCreated),
                formatCSVDate(dateNeeded),
                remarks
            ]);

        });

        // -------------------------------------------------
        // Check if there are records
        // -------------------------------------------------

        if (rows.length === 1) {

            alert(
                'There are no purchasing requests to export.'
            );

            return;
        }

        // -------------------------------------------------
        // Convert values to CSV-safe format
        // -------------------------------------------------

        function escapeCSV(value) {

            if (value === null || value === undefined) {
                return '""';
            }

            value = String(value);

            // Escape double quotes
            value = value.replace(/"/g, '""');

            // Wrap every value in quotes
            return '"' + value + '"';
        }

        const csvContent = rows
            .map(function (row) {

                return row
                    .map(escapeCSV)
                    .join(',');

            })
            .join('\r\n');

        // -------------------------------------------------
        // Add UTF-8 BOM
        // Helps Excel display special characters correctly
        // -------------------------------------------------

        const BOM = '\uFEFF';

        const blob = new Blob(
            [BOM + csvContent],
            {
                type: 'text/csv;charset=utf-8;'
            }
        );

        // -------------------------------------------------
        // Create filename
        // -------------------------------------------------

        const today = new Date();

        const year = today.getFullYear();

        const month = String(
            today.getMonth() + 1
        ).padStart(2, '0');

        const day = String(
            today.getDate()
        ).padStart(2, '0');

        const filename =
            'purchasing_requests_' +
            year + '-' +
            month + '-' +
            day +
            '.csv';

        // -------------------------------------------------
        // Download
        // -------------------------------------------------

        const link = document.createElement('a');

        link.href = URL.createObjectURL(blob);
        link.download = filename;

        document.body.appendChild(link);

        link.click();

        document.body.removeChild(link);

        URL.revokeObjectURL(link.href);
    }


    // =====================================================
    // EXPORT BUTTON
    // =====================================================

    $('#exportPurchasingCSV').on('click', function () {
        exportPurchasingCSV();
    });

    // =====================================================
    // USER / ROLE
    // =====================================================

    const isPurchasing = <?= $isPurchasing ? 'true' : 'false' ?>;
    const currentUserId = <?= (int)$_SESSION['user_id'] ?>;


    // =====================================================
    // MODAL
    // =====================================================

    const modalElement = document.getElementById('requestDetailsModal');

    const requestModal = new bootstrap.Modal(modalElement);


    // =====================================================
    // ORIGINAL VALUES
    // Used to determine whether Save should be enabled
    // =====================================================

    let originalStatus = '';
    let originalPurchaserId = '';


    // =====================================================
    // OPEN REQUEST MODAL
    // =====================================================

    $('#requestsTable tbody').on('click', 'tr', function (e) {

        // Don't open modal when clicking action buttons/links
        if (
            $(e.target).closest('a').length ||
            $(e.target).closest('button').length
        ) {
            return;
        }

        const row = $(this);

        const requestId = row.data('request-id');

        const lmrNo = row.data('lmr-no');
        const company = row.data('company');
        const department = row.data('department');
        const item = row.data('item');
        const description = row.data('description');
        const quantity = row.data('quantity');
        const uom = row.data('uom');

        const dateNeeded = row.data('date-needed');
        const dateCreated = row.data('date-created');

        const remarks = row.data('remarks');

        const status =
            (row.data('status') || '')
                .toString()
                .toLowerCase()
                .trim();

        const purchaserId =
            (row.data('purchaser-id') || '1')
                .toString();

        const purchaserName =
            row.data('purchaser-name') || 'Unassigned';

        const requestorName =
            row.data('requestor-name') || 'Unknown';


        // =================================================
        // SET MODAL DATA
        // =================================================

        $('#modalRequestId').val(requestId);

        $('#modalLmrNo').text('LMR No. ' + lmrNo);

        $('#modalLmr').val(lmrNo);
        $('#modalCompany').val(company);
        $('#modalDepartment').val(department);
        $('#modalItem').val(item);
        $('#modalDescription').val(description);
        $('#modalQuantity').val(quantity);
        $('#modalUom').val(uom);

        $('#modalDateNeeded').val(formatDate(dateNeeded));
        $('#modalDateCreated').val(formatDate(dateCreated));

        $('#modalRemarks').val(remarks || '-');

        $('#modalRequestor').val(requestorName);


        // =================================================
        // PURCHASING
        // =================================================

        if (isPurchasing) {

            originalStatus = status;
            originalPurchaserId = purchaserId;

            $('#modalStatus').val(status);
            $('#modalPurchaser').val(purchaserId);

            resetSaveButton();

        } else {

            // =================================================
            // REQUESTOR
            // =================================================

            $('#modalStatusDisplay').val(
                formatStatus(status)
            );

            $('#modalPurchaserDisplay').val(
                purchaserName
            );
        }


        // =================================================
        // SHOW MODAL
        // =================================================

        requestModal.show();

    });


    // =====================================================
    // FORMAT DATE
    // =====================================================

    function formatDate(value) {

        if (!value) {
            return '-';
        }

        const date = new Date(value);

        if (isNaN(date.getTime())) {
            return value;
        }

        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const year = date.getFullYear();

        return month + '-' + day + '-' + year;
    }


    // =====================================================
    // FORMAT STATUS
    // =====================================================

    function formatStatus(status) {

        if (!status) {
            return '-';
        }

        return status
            .replace(/\b\w/g, function (letter) {
                return letter.toUpperCase();
            });
    }


    // =====================================================
    // CHECK FOR CHANGES
    // =====================================================

    function checkForChanges() {

        const currentStatus =
            ($('#modalStatus').val() || '')
                .toString()
                .toLowerCase()
                .trim();

        const currentPurchaser =
            ($('#modalPurchaser').val() || '')
                .toString()
                .trim();


        const hasChanges =
            currentStatus !== originalStatus ||
            currentPurchaser !== originalPurchaserId;


        $('#saveRequestChanges').prop(
            'disabled',
            !hasChanges
        );

    }


    // =====================================================
    // STATUS CHANGE
    // =====================================================

    $('#modalStatus').on('change', function () {
        checkForChanges();
    });


    // =====================================================
    // PURCHASER CHANGE
    // =====================================================

    $('#modalPurchaser').on('change', function () {
        checkForChanges();
    });


    // =====================================================
    // ASSIGN TO ME
    // =====================================================

    $('#assignToMeBtn').on('click', function () {

        $('#modalPurchaser').val(
            String(currentUserId)
        );

        checkForChanges();

    });


    // =====================================================
    // RESET SAVE BUTTON
    // =====================================================

    function resetSaveButton() {

        $('#saveRequestChanges')
            .prop('disabled', true)
            .html(
                '<i class="fas fa-save me-1"></i> Save Changes'
            );

        $('#modalSaveMessage')
            .addClass('d-none')
            .removeClass('alert-success alert-danger')
            .text('');
    }


    // =====================================================
    // SAVE CHANGES
    // =====================================================

    $('#saveRequestChanges').on('click', function () {

        const button = $(this);

        const requestId =
            $('#modalRequestId').val();

        const status =
            $('#modalStatus').val();

        const purchaserId =
            $('#modalPurchaser').val();


        if (!requestId) {
            return;
        }


        // Prevent duplicate requests
        button.prop('disabled', true);

        button.html(
            '<span class="spinner-border spinner-border-sm me-1"></span>' +
            'Saving...'
        );


    $.ajax({
    url: 'ticket/includes/assign_request.php',
    type: 'POST',
    dataType: 'json',

    data: {
        request_id: requestId,
        status: status,
        purchaser_id: purchaserId
    },

    success: function (response) {

        if (response.success) {

            originalStatus = status;
            originalPurchaserId = purchaserId;

            const row =
                $('#requestsTable tbody tr[data-request-id="' +
                requestId +
                '"]');

            row.attr('data-status', status);
            row.attr('data-purchaser-id', purchaserId);

            row.data('status', status);
            row.data('purchaser-id', purchaserId);

            const statusBadge =
                row.find('td').eq(8).find('.badge');

            statusBadge.removeClass(
                'badge-proceed badge-checking badge-pending badge-closed badge-canceled'
            );

            let statusClass = 'badge-pending';

            if (status === 'checking requirements' ||
                status === 'canvassing') {
                statusClass = 'badge-checking';
            }
            else if (status === 'negotiation' ||
                    status === 'under discussion') {
                statusClass = 'badge-negotiation';
            }
            else if (status === 'draft') {
                statusClass = 'badge-draft';
            }
            else if (status === 'final') {
                statusClass = 'badge-proceed';
            }
            else if (status === 'end' || status === 'closed') {
                statusClass = 'badge-closed';
            }

            statusBadge
                .addClass(statusClass)
                .text(formatStatus(status));

            if (response.purchaser_name) {

                row.attr(
                    'data-purchaser-name',
                    response.purchaser_name
                );

                row.data(
                    'purchaser-name',
                    response.purchaser_name
                );

                row.find('td').eq(7).text(
                    response.purchaser_name
                );
            }

            $('#modalSaveMessage')
                .removeClass('d-none alert-danger')
                .addClass('alert-success')
                .text(
                    response.message ||
                    'Request updated successfully.'
                );

            button
                .prop('disabled', true)
                .html(
                    '<i class="fas fa-check me-1"></i> Saved'
                );

            table.draw(false);

            setTimeout(function () {
                button.html(
                    '<i class="fas fa-save me-1"></i> Save Changes'
                );
            }, 1500);

        } else {

            showSaveError(
                response.message ||
                'Unable to update request.'
            );
        }
    },

    error: function (xhr) {

        console.error('HTTP Status:', xhr.status);
        console.error('Response:', xhr.responseText);

        showSaveError(
            'Server error (' +
            xhr.status +
            '): ' +
            xhr.responseText
        );
    }
    });

    });


    // =====================================================
    // ERROR
    // =====================================================

    function showSaveError(message) {

        $('#modalSaveMessage')
            .removeClass('d-none alert-success')
            .addClass('alert-danger')
            .text(message);

        $('#saveRequestChanges')
            .prop('disabled', false)
            .html(
                '<i class="fas fa-save me-1"></i> Save Changes'
            );

    }


    // =====================================================
    // PRINT
    // =====================================================

    $('.btn-print').on('click', function (e) {

        e.preventDefault();
        e.stopPropagation();

        const status =
            ($(this).data('status') || '')
                .toString()
                .toLowerCase()
                .trim();

        const lmr =
            $(this).data('lmr');


        if (status !== 'final') {

            alert(
                'Printing is only available when the request status is "Proceed Request".'
            );

            return;
        }


        window.open(
            '?page=ticket/includes/print_request&lmr_no=' +
            encodeURIComponent(lmr),
            '_blank'
        );

    });

});

</script>



<?php $conn->close(); ?>
