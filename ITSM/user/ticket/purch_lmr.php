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
                r.category_id,
                rc.category_name,
                r.description,
                r.quantity,
                r.UoM,
                r.date_needed,
                r.remarks,
                r.date_created,
                r.status,
                r.order_status,
                r.priority,
                r.po_no,
                r.created_by,
                r.purchaser_id
            FROM purch_request_tb r
            LEFT JOIN user_tb u 
                ON r.created_by = u.user_id
            LEFT JOIN request_category_tb rc
                ON r.category_id = rc.category_id
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
                r.category_id,
                rc.category_name,
                r.description,
                r.quantity,
                r.UoM,
                r.date_needed,
                r.remarks,
                r.date_created,
                r.status,
                r.order_status,
                r.priority,
                r.po_no,
                r.created_by,
                r.purchaser_id
            FROM purch_request_tb r
            LEFT JOIN user_tb u 
                ON r.created_by = u.user_id
            LEFT JOIN request_category_tb rc
                ON r.category_id = rc.category_id
            WHERE TRIM(r.department) = TRIM(?)
            ORDER BY r.date_created ASC
        ";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $currentDepartment);
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
        padding: 8px 13px;
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
    .priority-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 12px;
        text-align: center;
        min-width: 65px;
    }

    .priority-urgent {
        background-color: #dc3545;
        color: white;
    }

    .priority-high {
        background-color: #ffc107;
        color: white;
    }

    .priority-medium {
        background-color: #0d6efd;
        /* color: #212529; */
        color: white;
    }

    .priority-default {
        background-color: #6c757d;
        color: white;
    }
</style>

<div class="card ">
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
                            class="btn btn-warning btn-sm"
                            id="bulkUpdateBtn"
                            disabled>
                        <i class="fas fa-edit me-1"></i>
                        Bulk Update
                        <span id="selectedCount" class="badge bg-dark ms-1">0</span>
                    </button>

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


                $categoryQuery = $conn->query("
                SELECT category_id, category_name
                FROM request_category_tb
                ORDER BY category_name ASC
                ");

                $filterCategories = [];

                while ($categoryRow = $categoryQuery->fetch_assoc()) {
                    $filterCategories[] = $categoryRow;
                }

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
                <div class="col-md-3">
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

                <!-- Urgency -->
                <div class="col-md-2">
                    <label class="form-label">Urgency</label>
                    <select id="statusUrgencyFilter" class="form-select">
                        <option value="">All </option>
                        <option value="urgent">Urgent</option>
                        <option value="high">High</option>
                        <option value="medium">Medium</option>
                    </select>
                </div>
                <!-- Category -->
                <div class="col-md-3">
                    <label class="form-label">Category</label>

                    <select id="categoryFilter" class="form-select">
                        <option value="">All Categories</option>

                        <?php foreach ($filterCategories as $category): ?>

                            <option value="<?= htmlspecialchars(
                                strtolower(trim($category['category_name']))
                            ) ?>">
                                <?= htmlspecialchars($category['category_name']) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>
                </div>
                <!-- Status -->
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select id="statusSelectFilter" class="form-select">
                        <option value="">All Status</option>
                        <option value="not_final_po_approved">
                            Not Final PO Approved
                        </option>
                        <option value="pending">Pending</option>
                        <option value="checking requirements">Checking Requirements</option>
                        <option value="canvassing">Canvassing</option>
                        <option value="negotiation">Negotiation</option>
                        <option value="draft po under discussion">Draft PO Under Discussion</option>
                        <option value="draft po approved">Draft PO Approved</option>
                        <option value="final po approved">Final PO Approved</option>
                        <option value="rejected">Rejected </option>
                        <!-- <option value="closed">Closed</option> -->
                    </select>
                </div>
                <!-- Order Status -->
                <div class="col-md-3">
                    <label class="form-label">Order Status</label>

                    <select id="orderStatusFilter" class="form-select">

                        <option value="">All Order Status</option>

                        <option value="n/a">N/A</option>
                        <option value="order acknowledged">Order Acknowledged</option>
                        <!-- <option value="goods delivered">Goods Delivered</option> -->
                        <option value="goods received">Goods Received</option>
                        <option value="payment processing">Payment Processing</option>
                        <option value="payment issued">Payment Issued</option>
                        <option value="closed">Closed</option>

                    </select>
                </div>
                <!-- Date From -->
                <div class="col-md-3">
                    <label class="form-label">Date From</label>
                    <input type="date" id="dateFrom" class="form-control">
                </div>

                <!-- Date To -->
                <div class="col-md-3">
                    <label class="form-label">Date To</label>
                    <input type="date" id="dateTo" class="form-control">
                </div>

            </div>

                <?php else: ?>

                    <!-- ==========================================
                        NON-PURCHASING USERS
                    =========================================== -->

                
                    <div class="row g-3 align-items-end">
                        <!-- Urgency -->
                        <div class="col-md-2">
                            <label class="form-label">Urgency</label>
                            <select id="statusUrgencyFilter" class="form-select">
                                <option value="">All </option>
                                <option value="Urgent">Urgent</option>
                                <option value="High">High</option>
                                <option value="Medium">Medium </option>
                            </select>
                        </div>
                        <!-- Category -->
                        <div class="col-md-2">
                            <label class="form-label">Category</label>

                            <select id="categoryFilter" class="form-select">
                                <option value="">All Categories</option>

                                <?php foreach ($filterCategories as $category): ?>

                                    <option value="<?= htmlspecialchars(
                                        strtolower(trim($category['category_name']))
                                    ) ?>">
                                        <?= htmlspecialchars($category['category_name']) ?>
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
                                <option value="draft po under discussion">Draft PO Under Discussion</option>
                                <option value="draft po approved">Draft PO Approved</option>
                                <option value="final po approved">Final PO Approved</option>
                                <option value="rejected">Rejected </option>
                                <!-- <option value="closed">Closed</option> -->
                            </select>
                        </div>
                        <!-- Order Status -->
                        <div class="col-md-2">
                            <label class="form-label">Order Status</label>

                            <select id="orderStatusFilter" class="form-select">

                                <option value="">All Order Status</option>

                                <option value="n/a">N/A</option>
                                <option value="order acknowledged">Order Acknowledged</option>
                                <!-- <option value="goods delivered">Goods Delivered</option> -->
                                <option value="goods received">Goods Received</option>
                                <option value="payment processing">Payment Processing</option>
                                <option value="payment issued">Payment Issued</option>
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

                <?php endif; ?>

        </div>


        <div class="table-responsive">
            <table id="requestsTable" class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>
                            <input type="checkbox" id="selectAllRequests">
                        </th>

                        <th>ID</th>
                        <th style="min-width: 160px; width: 180px;">LMR No.</th>
                        <th>PO</th>
                        <th>Requester</th>
                        <th>Department</th>
                        <th style="min-width: 100px; width: 120px;">Item</th>
                        <th style="min-width: 100px; width: 120px;">Category</th>
                        <!-- <th>Qty</th> -->
                        <!-- <th>UoM</th> -->
                        <th>Assigned to</th>
                        <th>Urgency</th>
                        <th style="min-width: 150px; width: 160px;">Status</th>
                        <th>Order Status</th>
                        <th>Date Created</th>
                        <th>Days Elapsed</th>
                        <th>Date Needed</th>
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
                            data-order-status="<?= htmlspecialchars(strtolower(trim($row['order_status'] ?? 'N/A')), ENT_QUOTES) ?>"
                            data-priority="<?= htmlspecialchars($row['priority'] ?? '', ENT_QUOTES) ?>"
                            data-category="<?= htmlspecialchars($row['category_name'] ?? '', ENT_QUOTES) ?>"
                            data-category-id="<?= (int)($row['category_id'] ?? 0) ?>"
                            data-po="<?= htmlspecialchars($row['po_no'] ?? '', ENT_QUOTES) ?>"
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
                    <td onclick="event.stopPropagation();">
                        <input 
                            type="checkbox"
                            class="request-checkbox"
                            value="<?= (int)$row['request_id'] ?>"
                        >
                    </td>
                        <td><?= htmlspecialchars($row['request_id']) ?></td>
                        <td style="min-width: 160px; width: 180px;"><?= htmlspecialchars($row['lmr_no']) ?></td>
                        <td><?= htmlspecialchars($row['po_no']) ?></td>
                        <td><?= htmlspecialchars($requestor_name) ?></td>
                        
                        <td><?= htmlspecialchars($row['department']) ?></td>
                        <td><?= htmlspecialchars($row['item']) ?></td>
                        <td>
                            <?= htmlspecialchars($row['category_name'] ?? 'N/A') ?>
                        </td>
                        <!-- <td><?= $row['quantity'] ?></td> -->
                        <!-- <td><?= htmlspecialchars($row['UoM']) ?></td> -->
                        <td>
                            <?php if ($purchaser_id == 1): ?>
                                <span class="unassigned-purchaser">
                                    Unassigned
                                </span>
                            <?php else: ?>
                                <?= htmlspecialchars($purchaser_name) ?>
                            <?php endif; ?>
                        </td>
                        <td class="priority-cell">
                            <?php
                                $priority = strtolower(trim($row['priority'] ?? ''));

                                $priorityClass = match ($priority) {
                                    'urgent' => 'priority-urgent',
                                    'high'   => 'priority-high',
                                    'medium' => 'priority-medium',
                                    default  => 'priority-default'
                                };

                                echo '<span class="priority-badge ' . $priorityClass . '">' .
                                    htmlspecialchars(ucfirst($priority)) .
                                    '</span>';
                            ?>
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
                                    case 'draft po under discussion':
                                        $statusClass = 'badge-negotiation';
                                        break;

                                    case 'draft po approved':
                                        $statusClass = 'badge-draft';
                                        break;

                                    case 'final po approved':
                                        $statusClass = 'badge-proceed';
                                        break;

                                    case 'pending':
                                        $statusClass = 'badge-pending';
                                        break;

                                    case 'end':
                                    case 'closed':
                                        $statusClass = 'badge-closed';
                                        break;

                                    case 'rejected':
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
                        <td>
                            <?php
                            $orderStatus = trim($row['order_status'] ?? 'N/A');
                            ?>

                            <span class="badge bg-secondary">
                                <?= htmlspecialchars($orderStatus) ?>
                            </span>
                        </td>
                    <td>
                        <?= date('m-d-Y', strtotime($row['date_created'])) ?>
                    </td>

                    <td>
                        <?php
                            $createdDate = new DateTime(
                                date('Y-m-d', strtotime($row['date_created']))
                            );

                            $today = new DateTime();

                            // Start counting AFTER the request date
                            $checkDate = clone $createdDate;
                            $checkDate->modify('+1 day');

                            $workingDaysElapsed = 0;

                            while ($checkDate < $today) {

                                $dayOfWeek = (int)$checkDate->format('N');

                                // Monday = 1, Friday = 5
                                if ($dayOfWeek <= 5) {
                                    $workingDaysElapsed++;
                                }

                                $checkDate->modify('+1 day');
                            }

                            // Red if more than 15 working days
                            $daysBadgeClass =
                                $workingDaysElapsed > 15
                                    ? 'bg-danger'
                                    : 'bg-warning text-dark';
                        ?>

                        <span class="badge <?= $daysBadgeClass ?>">
                            <?= $workingDaysElapsed ?>
                             day<?= $workingDaysElapsed != 1 ? 's' : '' ?>
                        </span>
                    </td>

                    <td>
                        <?= date('m-d-Y', strtotime($row['date_needed'])) ?>
                    </td>
                        <!-- <td><?= htmlspecialchars($row['remarks'] ?? '-') ?></td> -->
                        <td onclick="event.stopPropagation();">

                            <a href="?page=ticket/view_purch_request&request_id=<?= (int)$row['request_id'] ?>"
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

                            <?php if (!$isPurchasing): ?>
                                <button
                                    type="button"
                                    class="btn btn-sm btn-success btn-print"
                                    data-lmr="<?= htmlspecialchars($row['lmr_no']) ?>"
                                    data-status="<?= htmlspecialchars(strtolower(trim($row['status']))) ?>"
                                    title="Print">
                                    <i class="fas fa-print"></i>
                                </button>
                            <?php endif; ?>

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

                        <!-- <div class="col-md-4">
                            <label class="form-label fw-bold">LMR No.</label>
                            <input type="text"
                                id="modalLmr"
                                class="form-control"
                                readonly>
                        </div> -->
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Requested By</label>
                            <input type="text"
                                id="modalRequestor"
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

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Item</label>
                            <input type="text"
                                id="modalItem"
                                class="form-control"
                                readonly>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-bold">Quantity</label>
                            <input type="text"
                                id="modalQuantity"
                                class="form-control"
                                readonly>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-bold">UoM</label>
                            <input type="text"
                                id="modalUom"
                                class="form-control"
                                readonly>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-bold">Date Needed</label>
                            <input type="text"
                                id="modalDateNeeded"
                                class="form-control"
                                readonly>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-bold">Date Created</label>
                            <input type="text"
                                id="modalDateCreated"
                                class="form-control"
                                readonly>
                        </div>

                        <div class="col-6">
                            <label class="form-label fw-bold">Description</label>
                            <textarea id="modalDescription"
                                    class="form-control"
                                    rows="6"
                                    readonly></textarea>
                        </div>

                        <div class="col-6">
                            <label class="form-label fw-bold">Remarks</label>
                            <textarea id="modalRemarks"
                                    class="form-control"
                                    rows="6"
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

                            <div class="col-md-5">
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
                                                    <!-- CATEGORY -->
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Category</label>

                            <select id="modalCategory"
                                    class="form-select">

                                <option value="">N/A</option>

                                <?php foreach ($filterCategories as $category): ?>

                                    <option value="<?= (int)$category['category_id'] ?>">
                                        <?= htmlspecialchars($category['category_name']) ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>
                        </div>
                            <div class="col-md-3">

                                <label class="form-label fw-bold">
                                    Urgency
                                </label>

                                <select id="modalPriority"
                                        class="form-select">
                                    <option value="urgent">Urgent</option>
                                    <option value="high">High</option>
                                    <option value="medium">Medium</option>

                                </select>
                            </div>
                            <div class="col-md-4">

                                <label class="form-label fw-bold">
                                    Status
                                </label>

                                <select id="modalStatus"
                                        class="form-select">

                                    <option value="pending">Pending</option>
                                    <option value="checking requirements">Checking Requirements</option>
                                    <option value="canvassing">Canvassing</option>
                                    <option value="negotiation">Negotiation</option>
                                    <option value="draft po under discussion">Draft PO Under Discussion</option>
                                    <option value="draft po approved">Draft PO Approved</option>
                                    <option value="final po approved">Final PO Approved</option>
                                    <option value="rejected">Rejected</option>

                                </select>
                            </div>

                            <!-- ORDER STATUS -->
                            <div class="col-md-4">

                                <label class="form-label fw-bold">
                                    Order Status
                                </label>

                                <select id="modalOrderStatus"
                                        class="form-select"
                                        disabled>

                                    <option value="n/a">N/A</option>
                                    <option value="order acknowledged">Order Acknowledged</option>
                                    <!-- <option value="goods delivered">Goods Delivered</option> -->
                                    <option value="goods received">Goods Received</option>
                                    <option value="payment processing">Payment Processing</option>
                                    <option value="payment issued">Payment Issued</option>
                                    <option value="closed">Closed</option>

                                </select>
                            </div>

                            <!-- PO NUMBER -->
                            <div class="col-md-4">

                                <label class="form-label fw-bold">
                                    PO Number
                                </label>

                                <input type="text"
                                    id="modalPO"
                                    class="form-control"
                                    disabled>
                            </div>

                            <!-- CHANGE COMMENT -->
                            <div class="col-12">

                                <label class="form-label fw-bold">
                                    Comment
                                    <span class="text-muted fw-normal">(Required)</span>
                                </label>

                                <textarea
                                    id="modalChangeComment"
                                    class="form-control"
                                    rows="3"
                                    placeholder="Add a comment about this change..."
                                    maxlength="2000"></textarea>

                                <!-- <small class="text-muted">
                                    This comment will be included in the request history.
                                </small> -->

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
                            <div class="col-md-3">

                                <label class="form-label fw-bold">
                                    Urgency
                                </label>
                                <input type="text"
                                    id="modalPriorityDisplay"
                                    class="form-control"
                                    readonly>
                            </div>
                            <div class="col-md-3">

                                <label class="form-label fw-bold">
                                    Status
                                </label>

                                <input type="text"
                                    id="modalStatusDisplay"
                                    class="form-control"
                                    readonly>

                            </div>
                            <!-- <div class="col-md-2">
                                <label class="form-label fw-bold">PO Number</label>
                                <input type="text"
                                    id="modalPO"
                                    class="form-control"
                                    readonly>
                            </div> -->

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

    <!-- =========================================================
    BULK UPDATE MODAL
    ========================================================= -->
    <div class="modal fade" id="bulkUpdateModal" tabindex="-1"
        aria-labelledby="bulkUpdateModalLabel" aria-hidden="true">

        <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content">

            <div class="modal-header bg-gradient-primary text-white">

                <div>
                    <h5 class="modal-title" id="bulkUpdateModalLabel">
                        Bulk Update Requests
                    </h5>

                    <small>
                        Selected:
                        <strong id="bulkSelectedCount">0</strong>
                        requests
                    </small>
                </div>

                <button type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal">
                </button>

            </div>

            <div class="modal-body">

                <div class="alert alert-info">
                    Leave a field unchanged if you do not want to modify it.
                </div>

                <div class="row g-3">
                    <!-- PURCHASER -->
                    <div class="col-md-4">

                        <label class="form-label fw-bold">
                            Assigned Purchaser
                        </label>

                        <select id="bulkPurchaser"
                                class="form-select">

                            <option value="">
                                No Change
                            </option>

                            <option value="1">
                                Unassigned
                            </option>

                            <?php
                            $bulkPurchaserQuery = $conn->query("
                                SELECT user_id, fullname, company
                                FROM user_tb
                                WHERE department = 'Purchasing'
                                AND is_active = 1
                                ORDER BY company ASC, fullname ASC
                            ");

                            while ($bulkPurchaser = $bulkPurchaserQuery->fetch_assoc()):
                            ?>

                                <option value="<?= (int)$bulkPurchaser['user_id'] ?>">
                                    <?= htmlspecialchars($bulkPurchaser['company']) ?> -
                                    <?= htmlspecialchars($bulkPurchaser['fullname']) ?>
                                </option>

                            <?php endwhile; ?>

                        </select>

                    </div>

                        <!-- CATEGORY -->
                    <div class="col-md-4">

                        <label class="form-label fw-bold">
                            Category
                        </label>

                        <select id="bulkCategory"
                                class="form-select">

                            <option value="">
                                No Change
                            </option>

                            <?php foreach ($filterCategories as $category): ?>

                                <option value="<?= (int)$category['category_id'] ?>">
                                    <?= htmlspecialchars($category['category_name']) ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                        </div>
                        <!-- PRIORITY -->
                        <div class="col-md-4">

                            <label class="form-label fw-bold">
                                Priority
                            </label>

                            <select id="bulkPriority"
                                    class="form-select">

                                <option value="">
                                    No Change
                                </option>

                                <option value="urgent">
                                    Urgent
                                </option>

                                <option value="high">
                                    High
                                </option>

                                <option value="medium">
                                    Medium
                                </option>

                            </select>

                        </div>
                        <!-- STATUS -->
                        <div class="col-md-4">

                            <label class="form-label fw-bold">
                                Status
                            </label>

                            <select id="bulkStatus"
                                    class="form-select">

                                <option value="">
                                    No Change
                                </option>

                                <option value="pending">
                                    Pending
                                </option>

                                <option value="checking requirements">
                                    Checking Requirements
                                </option>

                                <option value="canvassing">
                                    Canvassing
                                </option>

                                <option value="negotiation">
                                    Negotiation
                                </option>

                                <option value="draft po under discussion">
                                    Draft PO Under Discussion
                                </option>

                                <option value="draft po approved">
                                    Draft PO Approved
                                </option>

                                <option value="final po approved">
                                    Final PO Approved
                                </option>

                                <option value="rejected">
                                    Rejected
                                </option>

                                <!-- <option value="closed">
                                    Closed
                                </option> -->

                            </select>

                        </div>
                        <!-- ORDER STATUS -->
                        <div class="col-md-4">

                            <label class="form-label fw-bold">
                                Order Status
                            </label>

                            <select id="bulkOrderStatus"
                                    class="form-select"
                                    disabled>

                                <option value="">
                                    No Change
                                </option>

                                <option value="n/a">
                                    N/A
                                </option>

                                <option value="order acknowledged">
                                    Order Acknowledged
                                </option>

                                <!-- <option value="goods delivered">
                                    Goods Delivered
                                </option> -->

                                <option value="goods received">
                                    Goods Received
                                </option>

                                <option value="payment processing">
                                    Payment Processing
                                </option>

                                <option value="payment issued">
                                    Payment Issued
                                </option>

                                <option value="closed">
                                    Closed
                                </option>

                            </select>

                        </div>

                        <!-- PO NUMBER -->
                        <div class="col-md-4">

                            <label class="form-label fw-bold">
                                PO Number
                            </label>

                            <input type="text"
                                id="bulkPO"
                                class="form-control"
                                placeholder="Required when status is Final PO Approved"
                                disabled>
                        </div>
                        <!-- COMMENT -->
                        <div class="col-12">

                            <label class="form-label fw-bold">
                                Comment
                                <span class="text-muted fw-normal">(Required)</span>
                            </label>

                            <textarea
                                id="bulkChangeComment"
                                class="form-control"
                                rows="3"
                                maxlength="2000"
                                placeholder="Add a comment about this bulk update..."
                            required></textarea>
        <!-- 
                    <small class="text-muted">
                        This comment will be added to the request history of every selected request.
                    </small> -->

                </div>

                </div>


                <div id="bulkUpdateMessage"
                    class="alert d-none mt-4 mb-0">
                </div>

            </div>


            <div class="modal-footer">

                <button type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">
                    Cancel
                </button>

                <button type="button"
                        class="btn btn-primary"
                        id="confirmBulkUpdate">

                    <i class="fas fa-save me-1"></i>
                    Update Selected

                </button>

            </div>

        </div>

    </div>
    </div>

<script>
$(document).ready(function () {

    const table = $('#requestsTable').DataTable({
        pageLength: 20,

        lengthMenu: [
            [10, 20, 50, 100, 500, 1000, 5000, 10000],
            [10, 20, 50, 100, 500, 1000, 5000, 10000]
        ],

        order: [[1, "desc"]],

        columnDefs: [
            {
                orderable: false,
                targets: [0, 14]
            }
        ]
    });

        // =====================================================
        // UPDATE ROW IN DATATABLES
        // =====================================================

        function refreshDataTableRow(row) {
            if (row && row.length) {
                table.row(row).invalidate('dom');
            }
        }
        // =====================================================
        // BULK FINAL PO CONTROLS
        // =====================================================

        function updateBulkFinalPOControls() {

            const status =
                ($('#bulkStatus').val() || '')
                    .toString()
                    .toLowerCase()
                    .trim();

            const isFinalPO =
                status === 'final po approved';

            $('#bulkPO').prop(
                'disabled',
                !isFinalPO
            );

            $('#bulkOrderStatus').prop(
                'disabled',
                !isFinalPO
            );

            if (!isFinalPO) {

                $('#bulkPO').val('');
                $('#bulkOrderStatus').val('');
            }
        }

        $('#bulkStatus').on('change', function () {

            updateBulkFinalPOControls();

        });
    // =====================================================
    // BULK SELECTION
    // =====================================================

    function updateSelectedCount() {

        let selectedCount = 0;

        table.rows().every(function () {

            const row = $(this.node());

            if (
                row.find('.request-checkbox').prop('checked')
            ) {
                selectedCount++;
            }

        });

        $('#selectedCount').text(selectedCount);
        $('#bulkSelectedCount').text(selectedCount);

        $('#bulkUpdateBtn').prop(
            'disabled',
            selectedCount === 0
        );
    }


    // =====================================================
    // INDIVIDUAL CHECKBOX
    // =====================================================

    $('#requestsTable tbody').on(
        'change',
        '.request-checkbox',
        function (e) {

            e.stopPropagation();

            updateSelectedCount();
            updateSelectAllState();
        }
    );

    // =====================================================
    // SELECT ALL CURRENTLY DISPLAYED/FILTERED ROWS
    // =====================================================

    $('#selectAllRequests').on('change', function () {

        const checked = $(this).prop('checked');

        // Only select rows currently visible after filters/search
        table.rows({
            search: 'applied'
        }).nodes().to$()
        .find('.request-checkbox')
        .prop('checked', checked);

        updateSelectedCount();
    });


    // =====================================================
    // UPDATE SELECT ALL STATE
    // =====================================================

    function updateSelectAllState() {

        const visibleCheckboxes =
            table.rows({
                search: 'applied'
            }).nodes().to$()
            .find('.request-checkbox');

        const checkedCount =
            visibleCheckboxes.filter(':checked').length;

        if (visibleCheckboxes.length === 0) {

            $('#selectAllRequests')
                .prop('checked', false)
                .prop('indeterminate', false);

            return;
        }

        $('#selectAllRequests').prop(
            'checked',
            checkedCount === visibleCheckboxes.length
        );

        $('#selectAllRequests').prop(
            'indeterminate',
            checkedCount > 0 &&
            checkedCount < visibleCheckboxes.length
        );
    }                                

    // =====================================================
    // CUSTOM FILTER
    // =====================================================

    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {

        // Only apply this filter to requestsTable
        if (settings.nTable.id !== 'requestsTable') {
            return true;
        }

        const row = table.row(dataIndex).node();

        if (!row) {
            return true;
        }

        const $row = $(row);

        // =====================================================
        // GET ROW DATA
        // Use attr() so AJAX-updated values are always current
        // =====================================================

        const rowStatus = (
            $row.attr('data-status') || ''
        ).toString().toLowerCase().trim();

        const rowCompany = (
            $row.attr('data-company') || ''
        ).toString().toLowerCase().trim();

        const rowDepartment = (
            $row.attr('data-department') || ''
        ).toString().toLowerCase().trim();

        const rowPriority = (
            $row.attr('data-priority') || ''
        ).toString().toLowerCase().trim();
        const rowCategory = (
            $row.attr('data-category') || ''
        )
        .toString()
        .toLowerCase()
        .trim();

        const rowOrderStatus = (
            $row.attr('data-order-status') || ''
        )
        .toString()
        .toLowerCase()
        .trim();
        const purchaserId = (
            $row.attr('data-purchaser-id') || ''
        ).toString().trim();

        const rowDateCreated = (
            $row.attr('data-date-created') || ''
        ).toString().trim();


    // =====================================================
    // GET FILTER VALUES
    // =====================================================

        const requestView =
            $('#requestViewFilter').val() || '';

        const department =
            ($('#departmentFilter').val() || '')
            .toString()
            .toLowerCase()
            .trim();

        const company =
            ($('#companyFilter').val() || '')
            .toString()
            .toLowerCase()
            .trim();

        const status =
            ($('#statusSelectFilter').val() || '')
            .toString()
            .toLowerCase()
            .trim();
        const category = (
            $('#categoryFilter').val() || ''
        )
        .toString()
        .toLowerCase()
        .trim();

        const orderStatus = (
            $('#orderStatusFilter').val() || ''
        )
        .toString()
        .toLowerCase()
        .trim();
        const urgency =
            ($('#statusUrgencyFilter').val() || '')
            .toString()
            .toLowerCase()
            .trim();

        const dateFrom =
            $('#dateFrom').val() || '';

        const dateTo =
            $('#dateTo').val() || '';


        // =====================================================
        // ASSIGNED TO ME
        // =====================================================

        if (requestView === 'assigned') {

            const currentUserId =
                '<?= (int)$_SESSION['user_id'] ?>';

            if (purchaserId !== currentUserId) {
                return false;
            }
        }


        // =====================================================
        // COMPANY
        // =====================================================

        if (company && rowCompany !== company) {
            return false;
        }


        // =====================================================
        // DEPARTMENT
        // =====================================================

        if (department && rowDepartment !== department) {
            return false;
        }


        // =====================================================
        // URGENCY
        // =====================================================

        if (urgency && rowPriority !== urgency) {
            return false;
        }


        // =====================================================
        // STATUS
        // =====================================================

        if (status && rowStatus !== status) {
            return false;
        }

        // =====================================================
        // CATEGORY
        // =====================================================

        if (category && rowCategory !== category) {
            return false;
        }


        // =====================================================
        // ORDER STATUS
        // =====================================================

        if (orderStatus && rowOrderStatus !== orderStatus) {
            return false;
        }
        // =====================================================
        // DATE CREATED
        // =====================================================

        // Convert:
        // 2026-09-30 14:25:00
        //
        // into:
        // 2026-09-30

        const rowDate =
            rowDateCreated.substring(0, 10);


        // =====================================================
        // DATE FROM
        // =====================================================

        if (dateFrom && rowDate < dateFrom) {
            return false;
        }


        // =====================================================
        // DATE TO
        // =====================================================

        if (dateTo && rowDate > dateTo) {
            return false;
        }


        // =====================================================
        // PASSED ALL FILTERS
        // =====================================================

        return true;
    });


    // =====================================================
    // PURCHASING FILTERS - ON CHANGE
    // =====================================================

    $('#companyFilter, #requestViewFilter, #departmentFilter, #statusUrgencyFilter, #statusSelectFilter, #categoryFilter, #orderStatusFilter, #dateFrom, #dateTo')
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
        // GET BULK SELECTED ROWS
        // =====================================================

        function getBulkSelectedRows() {

            const selectedRows = [];

            table.rows().every(function () {

                const row = $(this.node());

                const checkbox =
                    row.find('.request-checkbox');

                if (checkbox.prop('checked')) {
                    selectedRows.push(row);
                }

            });

            return selectedRows;
        }


        // =====================================================
        // CHECK BULK SELECTED DETAILS
        // =====================================================

        function getBulkRowDetails(row) {

            return {

                purchaserId:
                    (row.attr('data-purchaser-id') || '')
                        .toString()
                        .trim(),

                categoryId:
                    (row.attr('data-category-id') || '')
                        .toString()
                        .trim(),

                priority:
                    (row.attr('data-priority') || '')
                        .toString()
                        .toLowerCase()
                        .trim(),

                status:
                    (row.attr('data-status') || '')
                        .toString()
                        .toLowerCase()
                        .trim(),

                orderStatus:
                    (row.attr('data-order-status') || 'n/a')
                        .toString()
                        .toLowerCase()
                        .trim(),

                po:
                    (row.attr('data-po') || '')
                        .toString()
                        .trim()
            };
        }


        // =====================================================
        // OPEN BULK UPDATE MODAL
        // =====================================================

        $('#bulkUpdateBtn').on('click', function () {

            const selectedRows =
                getBulkSelectedRows();

            const selectedCount =
                selectedRows.length;

            if (selectedCount === 0) {

                alert(
                    'Please select at least one request.'
                );

                return;
            }

            $('#bulkSelectedCount')
                .text(selectedCount);

            // =================================================
            // RESET MESSAGE
            // =================================================

            $('#bulkUpdateMessage')
                .addClass('d-none')
                .removeClass(
                    'alert-success alert-danger alert-warning'
                )
                .text('');

            // =================================================
            // GET FIRST SELECTED REQUEST
            // =================================================

            const firstRow =
                selectedRows[0];

            const firstData =
                getBulkRowDetails(firstRow);

            // =================================================
            // CHECK WHETHER SELECTED REQUESTS HAVE
            // DIFFERENT DETAILS
            // =================================================

            let hasDifferentDetails = false;

            selectedRows.forEach(function (row) {

                const currentData =
                    getBulkRowDetails(row);

                if (
                    currentData.purchaserId !==
                        firstData.purchaserId ||

                    currentData.categoryId !==
                        firstData.categoryId ||

                    currentData.priority !==
                        firstData.priority ||

                    currentData.status !==
                        firstData.status ||

                    currentData.orderStatus !==
                        firstData.orderStatus ||

                    currentData.po !==
                        firstData.po
                ) {

                    hasDifferentDetails = true;

                }

            });

            // =================================================
            // POPULATE FROM FIRST SELECTED REQUEST
            // =================================================

            $('#bulkPurchaser')
                .val(firstData.purchaserId);

            $('#bulkCategory')
                .val(firstData.categoryId);

            $('#bulkPriority')
                .val(firstData.priority);

            $('#bulkStatus')
                .val(firstData.status);

            $('#bulkOrderStatus')
                .val(firstData.orderStatus);

            $('#bulkPO')
                .val(firstData.po);

            // =================================================
            // UPDATE FINAL PO CONTROLS
            // =================================================

            updateBulkFinalPOControls();

            // =================================================
            // WARNING FOR DIFFERENT DETAILS
            // =================================================

            if (hasDifferentDetails) {

                $('#bulkUpdateMessage')
                    .removeClass(
                        'd-none alert-success alert-danger'
                    )
                    .addClass('alert-warning')
                    .text(
                        'Trying to edit request with different details.'
                    );
            }

            // =================================================
            // SHOW MODAL
            // =================================================

            const bulkModal =
                new bootstrap.Modal(
                    document.getElementById(
                        'bulkUpdateModal'
                    )
                );

            bulkModal.show();

        });

        
        // =====================================================
        // EXPORT PURCHASING REQUESTS TO CSV
        // Exports only the currently filtered requests
        // =====================================================

    function exportPurchasingCSV() {

            const rows = [];

            // -------------------------------------------------
            // CSV HEADERS
            // -------------------------------------------------

            const headers = [
                "Request ID",
                "LMR No.",
                "PO No.",
                "Company",
                "Requested By",
                "Department",
                "Item",
                "Category",
                "Description",
                "Quantity",
                "UoM",
                "Assigned To",
                "Urgency",
                "Status",
                "Order Status",
                "Date Created",
                "Date Needed",
                "Remarks"
            ];

            rows.push(headers);

            // -------------------------------------------------
            // GET FILTERED DATATABLE ROWS
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
                // GET DATA FROM DATA-* ATTRIBUTES
                // -------------------------------------------------

                const requestId =
                    $row.attr('data-request-id') || '';

                const lmrNo =
                    $row.attr('data-lmr-no') || '';

                const poNo =
                    $row.attr('data-po') || '';

                const company =
                    $row.attr('data-company') || '';

                const requestor =
                    $row.attr('data-requestor-name') || '';

                const department =
                    $row.attr('data-department') || '';

                const item =
                    $row.attr('data-item') || '';

                const category =
                    $row.attr('data-category') || '';

                const description =
                    $row.attr('data-description') || '';

                const quantity =
                    $row.attr('data-quantity') || '';

                const uom =
                    $row.attr('data-uom') || '';

                const purchaser =
                    $row.attr('data-purchaser-name') || 'Unassigned';

                const priority =
                    $row.attr('data-priority') || '';

                const status =
                    $row.attr('data-status') || '';

                const orderStatus =
                    $row.attr('data-order-status') || 'N/A';

                const dateCreated =
                    $row.attr('data-date-created') || '';

                const dateNeeded =
                    $row.attr('data-date-needed') || '';

                const remarks =
                    $row.attr('data-remarks') || '';

                // -------------------------------------------------
                // FORMAT TEXT
                // -------------------------------------------------

                function formatText(value) {

                    if (!value) {
                        return '';
                    }

                    return String(value)
                        .replace(/\b\w/g, function (letter) {
                            return letter.toUpperCase();
                        });
                }

                // -------------------------------------------------
                // FORMAT DATE
                // -------------------------------------------------

                function formatCSVDate(value) {

                    if (!value) {
                        return '';
                    }

                    // Get only YYYY-MM-DD portion when possible
                    const datePart = String(value).substring(0, 10);

                    // Handle YYYY-MM-DD directly
                    const match = datePart.match(
                        /^(\d{4})-(\d{2})-(\d{2})$/
                    );

                    if (match) {

                        return (
                            match[2] +
                            '-' +
                            match[3] +
                            '-' +
                            match[1]
                        );
                    }

                    // Fallback
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

                    const year =
                        date.getFullYear();

                    return (
                        month +
                        '-' +
                        day +
                        '-' +
                        year
                    );
                }

                // -------------------------------------------------
                // ADD ROW
                // -------------------------------------------------

                rows.push([
                    requestId,
                    lmrNo,
                    poNo,
                    company,
                    requestor,
                    department,
                    item,
                    category,
                    description,
                    quantity,
                    uom,
                    purchaser,
                    formatText(priority),
                    formatText(status),
                    formatText(orderStatus),
                    formatCSVDate(dateCreated),
                    formatCSVDate(dateNeeded),
                    remarks
                ]);

            });

            // -------------------------------------------------
            // CHECK IF THERE ARE RECORDS
            // -------------------------------------------------

            if (rows.length === 1) {

                alert(
                    'There are no purchasing requests to export.'
                );

                return;
            }

            // -------------------------------------------------
            // CSV ESCAPE
            // -------------------------------------------------

            function escapeCSV(value) {

                if (
                    value === null ||
                    value === undefined
                ) {
                    return '""';
                }

                value = String(value);

                // Escape double quotes
                value = value.replace(/"/g, '""');

                // Wrap value in quotes
                return '"' + value + '"';
            }

            // -------------------------------------------------
            // CREATE CSV CONTENT
            // -------------------------------------------------

            const csvContent = rows
                .map(function (row) {

                    return row
                        .map(escapeCSV)
                        .join(',');

                })
                .join('\r\n');

            // -------------------------------------------------
            // UTF-8 BOM
            // -------------------------------------------------

            const BOM = '\uFEFF';

            const blob = new Blob(
                [BOM + csvContent],
                {
                    type: 'text/csv;charset=utf-8;'
                }
            );

            // -------------------------------------------------
            // CREATE FILENAME
            // -------------------------------------------------

            const today = new Date();

            const year =
                today.getFullYear();

            const month =
                String(today.getMonth() + 1)
                    .padStart(2, '0');

            const day =
                String(today.getDate())
                    .padStart(2, '0');

            const filename =
                'purchasing_requests_' +
                year + '-' +
                month + '-' +
                day +
                '.csv';

            // -------------------------------------------------
            // DOWNLOAD
            // -------------------------------------------------

            const link =
                document.createElement('a');

            const url =
                URL.createObjectURL(blob);

            link.href = url;
            link.download = filename;

            document.body.appendChild(link);

            link.click();

            document.body.removeChild(link);

            // Revoke after download
            setTimeout(function () {
                URL.revokeObjectURL(url);
            }, 100);
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
    let originalPriority = '';
    let originalPO = '';
    let originalCategoryId = '';
    let originalOrderStatus = '';


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
        const po = row.data('po');
        const priority = row.data('priority');
        const remarks = row.data('remarks');

        const categoryId =
            (row.data('category-id') || '')
                .toString()
                .trim();

        const orderStatus =
            (row.data('order-status') || 'n/a')
                .toString()
                .toLowerCase()
                .trim();

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

        $('#modalChangeComment').val('');

        // =================================================
        // PURCHASING
        // =================================================

        if (isPurchasing) {

            originalStatus = status;
            originalPurchaserId = purchaserId;
            originalPriority = (priority || '').toString().toLowerCase().trim();
            originalPO = (po || '').toString().trim();
            originalCategoryId = categoryId;
            originalOrderStatus = orderStatus || 'n/a';

            $('#modalStatus').val(status);
            $('#modalPurchaser').val(purchaserId);
            $('#modalPO').val(originalPO);
            $('#modalPriority').val(originalPriority);
            $('#modalCategory').val(categoryId);
            $('#modalOrderStatus').val(originalOrderStatus);

            // Enable/disable PO and Order Status
            updateFinalPOControls();

            resetSaveButton();
        } else {

            // =================================================
            // REQUESTOR
            // =================================================

            $('#modalStatusDisplay').val(
                formatStatus(status)
            );
            $('#modalPriorityDisplay').val(
                formatStatus(priority)
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

            status = status.toString().trim();

            // N/A
            if (
                status.toLowerCase() === 'n/a' ||
                status.toLowerCase() === 'na'
            ) {
                return 'N/A';
            }

            // Sentence case
            status = status.toLowerCase();
            status = status.charAt(0).toUpperCase() + status.slice(1);

            // Always keep PO uppercase
            status = status.replace(/\bpo\b/gi, 'PO');

            return status;
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

        const currentPriority =
            ($('#modalPriority').val() || '')
                .toString()
                .toLowerCase()
                .trim();

        const currentPO =
            ($('#modalPO').val() || '')
                .toString()
                .trim();

        const currentCategoryId =
            ($('#modalCategory').val() || '')
                .toString()
                .trim();

        const currentOrderStatus =
            ($('#modalOrderStatus').val() || 'n/a')
                .toString()
                .toLowerCase()
                .trim();

        const isFinalPO =
            currentStatus === 'final po approved';

        // PO is required when Final PO Approved
        if (isFinalPO && currentPO === '') {

            $('#saveRequestChanges').prop(
                'disabled',
                true
            );

            return;
        }

        const hasChanges =
            currentStatus !== originalStatus ||
            currentPurchaser !== originalPurchaserId ||
            currentPriority !== originalPriority ||
            currentPO !== originalPO ||
            currentCategoryId !== originalCategoryId ||
            currentOrderStatus !== originalOrderStatus;
             comment !== '';

        $('#saveRequestChanges').prop(
            'disabled',
            !hasChanges
        );
    }
    // =====================================================
    // FINAL PO APPROVED CONTROLS
    // PO NUMBER + ORDER STATUS ONLY EDITABLE WHEN
    // STATUS = FINAL PO APPROVED
    // =====================================================

    function updateFinalPOControls() {

        const status =
            ($('#modalStatus').val() || '')
                .toString()
                .toLowerCase()
                .trim();

        const isFinalPO =
            status === 'final po approved';

        $('#modalPO').prop(
            'disabled',
            !isFinalPO
        );

        $('#modalOrderStatus').prop(
            'disabled',
            !isFinalPO
        );

        // If status is not Final PO Approved,
        // force order status to N/A
        if (!isFinalPO) {

            $('#modalOrderStatus').val('n/a');

        }
    }
    // =====================================================
    // STATUS CHANGE
    // =====================================================

    $('#modalStatus').on('change', function () {
        updateFinalPOControls();
        checkForChanges();
        });


        // =====================================================
        // PURCHASER CHANGE
        // =====================================================

        $('#modalPurchaser').on('change', function () {
            checkForChanges();
        });

        $('#modalPriority').on('change', function () {
            checkForChanges();
        });
        $('#modalCategory').on('change', function () {
            checkForChanges();
        });

        $('#modalOrderStatus').on('change', function () {
            checkForChanges();
        });

        $('#modalPO').on('input', function () {
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
        $('#modalChangeComment').on('input', function () {

            const comment = $(this).val().trim();

            const status = $('#modalStatus').val();
            const purchaserId = $('#modalPurchaser').val();
            const priority = $('#modalPriority').val();
            const categoryId = $('#modalCategory').val();
            const orderStatus = $('#modalOrderStatus').val();
            const poNumber = $('#modalPO').val().trim();

            const hasChanges =
                status !== originalStatus ||
                purchaserId !== originalPurchaserId ||
                priority !== originalPriority ||
                categoryId !== originalCategoryId ||
                orderStatus !== originalOrderStatus ||
                poNumber !== originalPO ||
                comment !== '';

            $('#saveRequestChanges').prop('disabled', !hasChanges);
        });

    $('#saveRequestChanges').on('click', function () {

            const button = $(this);

            const requestId =
                $('#modalRequestId').val();

            const status =
                $('#modalStatus').val();

            const purchaserId =
                $('#modalPurchaser').val();

            const priority =
                $('#modalPriority').val();

            const poNumber =
            $('#modalPO').val().trim();

            const categoryId =
                $('#modalCategory').val() || '';

            const orderStatus =
                $('#modalOrderStatus').val() || 'n/a';

            if (!requestId) {
                return;
            }

            if (status === 'final po approved' && poNumber === '') {

                showSaveError(
                    'PO Number is required when Status is Final PO Approved.'
                );

                return;
            }

            if (status !== 'final po approved') {

                // Force these values when status is not Final PO Approved
                // so the server also receives the correct values.
                $('#modalPO').val('');
                
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
                        purchaser_id: purchaserId,
                        priority: priority,
                        category_id: categoryId,
                        order_status: orderStatus,
                        po_no: poNumber,
                        comment: $('#modalChangeComment').val().trim()
                    },


                success: function (response) {

                    if (response.success) {

                        originalStatus = status;
                        originalPurchaserId = purchaserId;
                        originalPriority = priority;
                        originalPO = poNumber;
                        originalCategoryId = categoryId;
                        originalOrderStatus = orderStatus;

                        const row =
                            $('#requestsTable tbody tr[data-request-id="' +
                            requestId +
                            '"]');


                        row.attr('data-status', status);
                        row.attr('data-purchaser-id', purchaserId);

                        row.data('status', status);
                        row.data('purchaser-id', purchaserId);

                        row.attr('data-priority', priority);
                        row.data('priority', priority);

                        row.attr('data-category-id', categoryId);
                        row.data('category-id', categoryId);

                        row.attr('data-order-status', orderStatus);
                        row.data('order-status', orderStatus);

                        row.attr('data-po', poNumber);
                        row.data('po', poNumber);

                        row.find('td').eq(3).text(
                            poNumber
                        );
                        const priorityClass = {
                            urgent: 'priority-urgent',
                            high: 'priority-high',
                            medium: 'priority-medium'
                        };

                        row.find('td').eq(9).html(
                            `<span class="priority-badge ${priorityClass[priority] || 'priority-default'}">
                                ${priority.charAt(0).toUpperCase() + priority.slice(1)}
                            </span>`
                        );
                        
                        row.find('td').eq(7).text(
                            response.category_name || 'N/A'
                        );

                        row.find('td').eq(11).html(
                            '<span class="badge bg-secondary">' +
                            formatStatus(orderStatus) +
                            '</span>'
                        );
                        const statusBadge =
                            row.find('td').eq(10).find('.badge');

                        statusBadge.removeClass(
                            'badge-proceed badge-checking badge-pending badge-closed badge-canceled'
                        );

                        let statusClass = 'badge-pending';

                        if (status === 'checking requirements' ||
                            status === 'canvassing') {
                            statusClass = 'badge-checking';
                        }
                        else if (status === 'negotiation' ||
                                status === 'draft po under discussion') {
                            statusClass = 'badge-negotiation';
                        }
                        else if (status === 'draft po approved') {
                            statusClass = 'badge-draft';
                        }
                        else if (status === 'final po approved') {
                            statusClass = 'badge-proceed';
                        }
                        else if (status === 'rejected' || status === 'closed') {
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

                            row.find('td').eq(8).text(
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

                    // Reload the page so the table gets fresh DB data
                        // setTimeout(function () {
                        //     window.location.reload();
                        // }, 500);

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

        const status = ($(this).data('status') || '')
            .toString()
            .toLowerCase()
            .trim();

        const lmr = $(this).data('lmr');




        window.open(
            'ticket/includes/print_purch_request.php?lmr_no=' + encodeURIComponent(lmr),
            '_blank'
        );

    });
    
    // =====================================================
    // CONFIRM BULK UPDATE
    // =====================================================

    $('#confirmBulkUpdate').on('click', function () {

        const button = $(this);

        const selectedIds = [];

        table.rows().every(function () {

            const checkbox =
                $(this.node()).find('.request-checkbox');

            if (checkbox.prop('checked')) {

                selectedIds.push(
                    parseInt(checkbox.val(), 10)
                );

            }

        });

        if (selectedIds.length === 0) {

            alert('Please select at least one request.');

            return;
        }

        const status =
            $('#bulkStatus').val() || '';

        const priority =
            $('#bulkPriority').val() || '';

        const purchaserId =
            $('#bulkPurchaser').val() || '';

        const categoryId =
            $('#bulkCategory').val() || '';

        const orderStatus =
            $('#bulkOrderStatus').val() || '';

        const poNumber =
            $('#bulkPO').val().trim();
        const comment =
            $('#bulkChangeComment').val().trim();

            if (
                status === '' &&
                priority === '' &&
                purchaserId === '' &&
                categoryId === '' &&
                orderStatus === '' &&
                poNumber === '' &&
                comment === ''
            ) {

                alert(
                    'Please select or enter at least one field to update.'
                );

                return;
            }

            if (status === 'final po approved' && poNumber === '') {

                alert(
                    'PO Number is required when Status is Final PO Approved.'
                );

                return;
            }

            if (
                orderStatus !== '' &&
                status !== 'final po approved'
            ) {

                alert(
                    'Order Status can only be changed when Status is Final PO Approved.'
                );

                return;
            }

            if (
                poNumber !== '' &&
                status !== 'final po approved'
            ) {

                alert(
                    'PO Number can only be changed when Status is Final PO Approved.'
                );

                return;
            }
        if (!confirm(
            'Are you sure you want to update ' +
            selectedIds.length +
            ' selected request(s)?'
        )) {

            return;
        }

        console.log('========== BULK UPDATE ==========');
        console.log('Selected IDs:', selectedIds);
        console.log('Status:', status);
        console.log('Priority:', priority);
        console.log('Purchaser:', purchaserId);
        console.log('PO:', poNumber);

        button.prop('disabled', true);

        button.html(
            '<span class="spinner-border spinner-border-sm me-1"></span>' +
            'Updating...'
        );

        $.ajax({

            url: 'ticket/includes/bulk_update_requests.php',

            type: 'POST',

            dataType: 'json',

        data: {
            request_ids: selectedIds,
            status: status,
            priority: priority,
            purchaser_id: purchaserId,
            category_id: categoryId,
            order_status: orderStatus,
            po_no: poNumber,
            comment: comment
        },

            success: function (response) {

                console.log('Bulk response:', response);

                if (response.success) {

                    $('#bulkUpdateMessage')
                        .removeClass('d-none alert-danger')
                        .addClass('alert-success')
                        .text(
                            response.message ||
                            'Requests updated successfully.'
                        );

                    selectedIds.forEach(function (requestId) {

                        const row = $(
                            '#requestsTable tbody tr[data-request-id="' +
                            requestId +
                            '"]'
                        );

                        if (!row.length) {
                            return;
                        }
                        // =====================================
                        // CATEGORY
                        // =====================================

                        if (categoryId !== '') {

                            const categoryName =
                                $('#bulkCategory option:selected')
                                    .text()
                                    .trim();

                            row.attr(
                                'data-category-id',
                                categoryId
                            );

                            row.data(
                                'category-id',
                                categoryId
                            );

                            row.attr(
                                'data-category',
                                categoryName
                            );

                            row.data(
                                'category',
                                categoryName
                            );

                            row.find('td').eq(7).text(
                                categoryName || 'N/A'
                            );
                        }

                        // =====================================
                        // STATUS
                        // =====================================

                        if (status !== '') {

                            row.attr('data-status', status);
                            row.data('status', status);

                            const statusBadge =
                                row.find('td').eq(10).find('.badge');

                            statusBadge.removeClass(
                                'badge-proceed ' +
                                'badge-checking ' +
                                'badge-negotiation ' +
                                'badge-draft ' +
                                'badge-pending ' +
                                'badge-closed ' +
                                'badge-canceled'
                            );

                            let statusClass = 'badge-pending';

                            if (
                                status === 'checking requirements' ||
                                status === 'canvassing'
                            ) {

                                statusClass = 'badge-checking';

                            } else if (
                                status === 'negotiation' ||
                                status === 'draft po under discussion'
                            ) {

                                statusClass = 'badge-negotiation';

                            } else if (status === 'draft po approved') {

                                statusClass = 'badge-draft';

                            } else if (status === 'final po approved') {

                                statusClass = 'badge-proceed';

                            } else if (
                                status === 'rejected' ||
                                status === 'closed'
                            ) {

                                statusClass = 'badge-closed';
                            }

                            statusBadge
                                .addClass(statusClass)
                                .text(formatStatus(status));
                        }

                        // =====================================
                        // ORDER STATUS
                        // =====================================

                        // If a new status is provided and it is NOT
                        // Final PO Approved, force Order Status to N/A.

                        let updatedOrderStatus = orderStatus;

                        if (
                            status !== '' &&
                            status !== 'final po approved'
                        ) {
                            updatedOrderStatus = 'n/a';
                        }

                        // If Order Status was explicitly selected
                        // or Status changed away from Final PO Approved
                        if (
                            updatedOrderStatus !== '' ||
                            (status !== '' && status !== 'final po approved')
                        ) {

                            if (!updatedOrderStatus) {
                                updatedOrderStatus = 'n/a';
                            }

                            row.attr(
                                'data-order-status',
                                updatedOrderStatus
                            );

                            row.data(
                                'order-status',
                                updatedOrderStatus
                            );

                            row.find('td').eq(11).html(
                                '<span class="badge bg-secondary">' +
                                formatStatus(updatedOrderStatus) +
                                '</span>'
                            );
                        }
                        // =====================================
                        // PRIORITY
                        // =====================================

                        if (priority !== '') {

                            row.attr(
                                'data-priority',
                                priority
                            );

                            row.data(
                                'priority',
                                priority
                            );

                            const priorityClass = {

                                urgent: 'priority-urgent',
                                high: 'priority-high',
                                medium: 'priority-medium'

                            };

                            row.find('td').eq(9).html(

                                `<span class="priority-badge ${
                                    priorityClass[priority] ||
                                    'priority-default'
                                }">
                                    ${
                                        priority.charAt(0).toUpperCase() +
                                        priority.slice(1)
                                    }
                                </span>`

                            );
                        }


                        // =====================================
                        // PURCHASER
                        // =====================================

                        if (purchaserId !== '') {

                            row.attr(
                                'data-purchaser-id',
                                purchaserId
                            );

                            row.data(
                                'purchaser-id',
                                purchaserId
                            );

                            let purchaserName = 'Unassigned';

                            if (purchaserId !== '1') {

                                // Get only the purchaser's name
                                // from the option text:
                                // Company - Fullname
                                const purchaserText =
                                    $('#bulkPurchaser option:selected')
                                    .text()
                                    .trim();

                                const parts = purchaserText.split(' - ');

                                purchaserName =
                                    parts.length > 1
                                        ? parts.slice(1).join(' - ').trim()
                                        : purchaserText;
                            }

                            // Keep ONLY the name in the table/data attribute
                            row.attr(
                                'data-purchaser-name',
                                purchaserName
                            );

                            row.data(
                                'purchaser-name',
                                purchaserName
                            );

                            // Display ONLY purchaser name in table
                            row.find('td').eq(8).text(
                                purchaserName
                            );
                        }
                        // =====================================
                        // PO NUMBER
                        // =====================================

                        if (poNumber !== '') {

                            row.attr(
                                'data-po',
                                poNumber
                            );

                            row.data(
                                'po',
                                poNumber
                            );

                            row.find('td').eq(3).text(
                                poNumber
                            );
                        }

                        // =====================================
                        // SYNC DATATABLES INTERNAL DATA
                        // =====================================

                        refreshDataTableRow(row);
                        // =====================================
                        // UNCHECK
                        // =====================================

                        row.find('.request-checkbox')
                            .prop('checked', false);

                    });


                    $('#selectAllRequests')
                        .prop('checked', false)
                        .prop('indeterminate', false);

                    updateSelectedCount();

                    table.draw(false);


                    setTimeout(function () {

                        const modalElement =
                            document.getElementById(
                                'bulkUpdateModal'
                            );

                        const modal =
                            bootstrap.Modal.getInstance(
                                modalElement
                            );

                        if (modal) {
                            modal.hide();
                        }

                    }, 1000);


                } else {

                    $('#bulkUpdateMessage')
                        .removeClass('d-none alert-success')
                        .addClass('alert-danger')
                        .text(
                            response.message ||
                            'Unable to update requests.'
                        );
                }
            },

                error: function (xhr, status, error) {

                    console.error('========== BULK UPDATE ERROR ==========');
                    console.error('HTTP Status:', xhr.status);
                    console.error('AJAX Status:', status);
                    console.error('Error:', error);
                    console.error('Response Text:', xhr.responseText);

                    console.error(
                        'Response JSON parse test:',
                        function () {
                            try {
                                return JSON.parse(xhr.responseText);
                            } catch (e) {
                                return 'INVALID JSON: ' + e.message;
                            }
                        }()
                    );

                    $('#bulkUpdateMessage')
                        .removeClass('d-none alert-success')
                        .addClass('alert-danger')
                        .html(
                            '<strong>Server response:</strong><br>' +
                            $('<div>').text(xhr.responseText).html()
                        );
                },

            complete: function () {

                button.prop('disabled', false);

                button.html(
                    '<i class="fas fa-save me-1"></i>' +
                    ' Update Selected'
                );
            }

        });

    });

});                              
  

</script>


<?php $conn->close(); ?>
