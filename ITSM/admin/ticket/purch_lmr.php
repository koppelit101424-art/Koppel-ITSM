<?php
include __DIR__ . '/../../includes/auth.php';
include __DIR__ . '/../../includes/db.php';

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
    ORDER BY r.date_created DESC
";

$stmt = $conn->prepare($sql);


}else {

    // Non-Purchasing users can only see requests
    // assigned to them
$sql = "
    SELECT 
        r.request_id,
        r.user_id,
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
    ORDER BY r.date_created DESC
";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $created_by);
}

$stmt->execute();
$requests = $stmt->get_result();
?>


<style>
.table-hover tbody tr:hover { background-color: #f1f1f1; }
/* .badge-open { background-color: #0d6efd; color:#fff; } */
.badge-proceed { background-color: #198754; color:#fff; }
.badge-checking { background-color: #0d6efd; color:#fff; }
.badge-canceled { background-color: #dc3545; color:#fff; }
.badge-pending { background-color: #ffc107; color:#000; }
.badge-closed { background-color: #6c757d; color:#fff; }

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
</style>

    <div class="card ">
        <div class="card-header d-flex justify-content-between text-white">
            <span>Purchasing LMR</span>
            <a href="?page=ticket/includes/add_purch_request" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> Create LMR
            </a>
        </div>

        <div class="card-body">

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

    <?php if (    strcasecmp(trim($currentDepartment), 'Purchasing') === 0
    || (
        isset($_SESSION['user_type'])
        && strcasecmp(trim($_SESSION['user_type']), 'admin') === 0
    )): ?>

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
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
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
                            <th>Department</th>
                            <th>Item</th>
                            <th>Qty</th>
                            <th>UoM</th>
                            <th>Assigned to</th>
                            <!-- <th>Requested by</th> -->
                            <th>Status</th>
                            <th>Date Created</th>
                            <th>Date Needed</th>
                            <th>Remarks</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($requests->num_rows > 0): ?>
                            <?php $i = 1; while ($row = $requests->fetch_assoc()): ?>
                                <tr
                                    data-request-id="<?= $row['request_id'] ?>"
                                    data-lmr-no="<?= htmlspecialchars($row['lmr_no']) ?>"
                                    data-status="<?= htmlspecialchars(strtolower(trim($row['status']))) ?>"
                                    data-company="<?= htmlspecialchars(strtolower(trim($row['company']))) ?>"
                                    data-department="<?= htmlspecialchars(strtolower(trim($row['department']))) ?>"
                                    data-purchaser-id="<?= htmlspecialchars($row['purchaser_id']) ?>"
                                    data-date="<?= htmlspecialchars(date('Y-m-d', strtotime($row['date_created']))) ?>"
                                    style="cursor:pointer;">
                                    <td><?= $i++ ?></td>
                                    <td><?= htmlspecialchars($row['lmr_no']) ?></td>
                                    <td><?= htmlspecialchars($row['department']) ?></td>
                                    <td><?= htmlspecialchars(string: $row['item']) ?></td>
                                    <td><?= $row['quantity'] ?></td>
                                    <td><?= htmlspecialchars($row['UoM']) ?></td>
                                    <?php
                                    $purchaser_id = $row['purchaser_id'];

                                    if ($purchaser_id == 1) {
                                        $purchaser_name = 'Unassigned';
                                    } else {
                                        $stmt = $conn->prepare("SELECT fullname FROM user_tb WHERE user_id = ?");
                                        $stmt->bind_param("i", $purchaser_id);
                                        $stmt->execute();

                                        $result = $stmt->get_result();
                                        $purchaser = $result->fetch_assoc();

                                        $purchaser_name = $purchaser['fullname'] ?? 'Unknown';
                                    }
                                    ?>

                                    <td><?= htmlspecialchars($purchaser_name) ?></td>
                                    <?php
                                    $created_by = $row['created_by'];
                                        $stmt = $conn->prepare("SELECT fullname FROM user_tb WHERE user_id = ?");
                                        $stmt->bind_param("i", $created_by);
                                        $stmt->execute();

                                        $result = $stmt->get_result();
                                        $created_by = $result->fetch_assoc();

                                        $requestor_name = $created_by['fullname'] ?? 'Unknown';
                                    ?>

                                    <!-- <td><?= htmlspecialchars($requestor_name) ?></td> -->
                                    <td>
                                        <?php 
                                            $statusClass = '';
                                            switch (strtolower($row['status'])) {
                                                // case 'open': $statusClass = 'badge-open'; break;
                                                case 'proceed request': $statusClass = 'badge-proceed'; break;
                                                case 'checking request': $statusClass = 'badge-checking'; break;
                                                case 'pending': $statusClass = 'badge-pending'; break;
                                                case 'closed': $statusClass = 'badge-closed'; break;
                                                default: $statusClass = 'badge-pending'; break;
                                            }
                                        ?>
                                        <span class="badge <?= $statusClass ?> text-white" style="width: 100%;">
                                            <?= ucfirst($row['status']) ?>
                                        </span>
                                    </td>
                                    
                                    <td><?= date('m-d-Y', strtotime($row['date_created'])) ?></td>
                                    <td><?= date('m-d-Y', strtotime( $row['date_needed'])) ?></td>
                                    <td><?= htmlspecialchars($row['remarks'] ?? '-') ?></td>
                            <td onclick="event.stopPropagation();">

                                <a href="?page=ticket/view_request&request_id=<?= $row['request_id'] ?>"
                                class="btn btn-sm btn-primary"
                                title="View">
                                    <i class="fas fa-eye"></i>
                                </a>

                                <button
                                    type="button"
                                    class="btn btn-sm btn-success btn-print"
                                    data-lmr="<?= htmlspecialchars($row['lmr_no']) ?>"
                                    data-status="<?= strtolower(trim($row['status'])) ?>"
                                    title="Print">
                                    <i class="fas fa-print"></i>
                                </button>
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
            { orderable: false, targets: [5, 9, 11] }
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

        const rowDate = ($row.data('date') || '')
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

});
</script>


<?php $conn->close(); ?>
