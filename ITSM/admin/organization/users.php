<?php
include __DIR__ . '/../../includes/auth.php';
include __DIR__ . '/../../includes/db.php';
include __DIR__ . '/includes/user_sql.php';

// Get filter values from GET
$filterCompany = $_GET['company'] ?? '';
$filterDepartment = $_GET['department'] ?? '';
$filterArea = $_GET['area'] ?? '';
$filterStatus = $_GET['status'] ?? '';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';

// Fetch unique companies, departments, areas for filter buttons
$companiesArr = [];
$departmentsArr = [];
$areasArr = [];

$companies = $conn->query("SELECT DISTINCT company FROM user_tb WHERE company IS NOT NULL AND company != '' ORDER BY company DESC");
while($c = $companies->fetch_assoc()) $companiesArr[] = $c['company'];

$departments = $conn->query("SELECT DISTINCT department FROM user_tb WHERE department IS NOT NULL AND department != '' ORDER BY department ASC");
while($d = $departments->fetch_assoc()) $departmentsArr[] = $d['department'];

$areas = $conn->query("SELECT DISTINCT area FROM user_tb WHERE area IS NOT NULL AND area != '' ORDER BY area ASC");
while($a = $areas->fetch_assoc()) $areasArr[] = $a['area'];

// Build SQL query with filters
$sql = "SELECT * FROM user_tb WHERE 1=1";
$params = [];
$types = '';

if($filterCompany){
    $sql .= " AND company = ?";
    $params[] = $filterCompany;
    $types .= 's';
}

if($filterDepartment){
    $sql .= " AND department = ?";
    $params[] = $filterDepartment;
    $types .= 's';
}

if($filterArea){
    $sql .= " AND area = ?";
    $params[] = $filterArea;
    $types .= 's';
}

if($filterStatus){
    if($filterStatus === 'active'){
        $sql .= " AND is_active = 1";
    } elseif($filterStatus === 'disabled'){
        $sql .= " AND is_active = 0";
    }
}

// Date range filter
if($dateFrom && $dateTo){
    $sql .= " AND date_hired BETWEEN ? AND ?";
    $params[] = $dateFrom;
    $params[] = $dateTo;
    $types .= 'ss';
}

$sql .= " ORDER BY fullname ASC";

$stmt = $conn->prepare($sql);
if(!empty($params)){
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="container-fluid">

<!-- FILTER CARD -->
<div class="card shadow-sm mb-2">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold text-primary">
            <i class="fas fa-filter me-1"></i> Filter Users
        </h6>
        <div class="d-flex gap-2">
            <a href="?page=organization/users" class="btn btn-secondary btn-sm">
                <i class="fas fa-undo me-1"></i> Reset
            </a>
            <button type="submit" form="userFilterForm" class="btn btn-primary btn-sm">
                <i class="fas fa-search me-1"></i> Filter
            </button>
                <button type="button" onclick="printUsers()" class="btn btn-success btn-sm">
                    <i class="fas fa-print me-1"></i> Print
                </button>
                <button type="button" onclick="exportUsersCSV()" class="btn btn-info btn-sm">
                    <i class="fas fa-file-csv me-1"></i> Export CSV
                </button>
        </div>
    </div>

    <div class="card-body">
        <form id="userFilterForm" method="GET" class="row g-3 align-items-end">
            <input type="hidden" name="page" value="organization/users">

            <!-- Company -->
            <div class="col-md-2">
                <label for="company" class="form-label">Company</label>
                <select name="company" id="company" class="form-select">
                    <option value="">All Companies</option>
                    <?php foreach($companiesArr as $c): ?>
                        <option value="<?= htmlspecialchars($c) ?>" <?= $filterCompany == $c ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Department -->
            <div class="col-md-2">
                <label for="department" class="form-label">Department</label>
                <select name="department" id="department" class="form-select">
                    <option value="">All Departments</option>
                    <?php foreach($departmentsArr as $d): ?>
                        <option value="<?= htmlspecialchars($d) ?>" <?= $filterDepartment == $d ? 'selected' : '' ?>>
                            <?= htmlspecialchars($d) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Area -->
            <div class="col-md-2">
                <label for="area" class="form-label">Area</label>
                <select name="area" id="area" class="form-select">
                    <option value="">All Areas</option>
                    <?php foreach($areasArr as $a): ?>
                        <option value="<?= htmlspecialchars($a) ?>" <?= $filterArea == $a ? 'selected' : '' ?>>
                            <?= htmlspecialchars($a) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Status -->
            <div class="col-md-2">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="active" <?= $filterStatus == 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="disabled" <?= $filterStatus == 'disabled' ? 'selected' : '' ?>>Disabled</option>
                </select>
            </div>

            <!-- Date From -->
            <div class="col-md-2">
                <label for="date_from" class="form-label">Date From</label>
                <input type="date" name="date_from" value="<?= htmlspecialchars($dateFrom) ?>" class="form-control">
            </div>

            <!-- Date To -->
            <div class="col-md-2">
                <label for="date_to" class="form-label">Date To</label>
                <input type="date" name="date_to" value="<?= htmlspecialchars($dateTo) ?>" class="form-control">
            </div>
        </form>
    </div>
</div>

<!-- Users Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center bg-white border-bottom-0">
        <h5 class="mb-0 text-primary fw-semibold">User Management</h5>
        <a href="?page=organization/crud/add_user" class="btn btn-sm btn-primary me-2">
            <i class="fas fa-plus me-1"></i> Add User
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="usersTable" class="table table-hover">
                <thead class="table-header-blue">
                    <tr>
                        <th>ID</th>
                        <th>EMPID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th style="width: 90px;">Position</th>
                        <th>Department</th>
                        <th>Company</th>
                       <th>Area</th>
                         <!-- <th>Hired</th>
                        <th>Resigned</th> -->
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr onclick="window.location='?page=organization/includes/user_assets&user_id=<?= $row['user_id'] ?>'" style="cursor:pointer">
                        <td><?= $row['user_id'] ?></td>
                        <td><?= htmlspecialchars($row['emp_id']) ?></td>
                        <td><?= htmlspecialchars($row['fullname']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td style="width: 90px;"><?= htmlspecialchars($row['position']) ?></td>
                        <td><?= htmlspecialchars($row['department']) ?></td>
                        <td><?= htmlspecialchars($row['company']) ?></td>
                        <td><?= htmlspecialchars($row['area']) ?></td>
                        <td>
                            <?= isset($row['is_active']) && $row['is_active'] == 1
                                ? '<span class="badge bg-success">Active</span>'
                                : '<span class="badge bg-secondary">Disabled</span>'; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="10" class="text-center">No users found</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin'): ?>
<?php include __DIR__ . '/includes/user_menu.php'; ?>
<?php endif; ?>
<?php include __DIR__ . '/includes/resignation_modal.php'; ?>
<?php include __DIR__ . '/includes/toggle_status.php'; ?>
<?php include __DIR__ . '/includes/user_js.php'; ?>
<script>
    function printUsers() {
    var printContents = document.querySelector(".table-responsive").innerHTML;
    var originalContents = document.body.innerHTML;

    document.body.innerHTML = printContents;
    window.print();
    document.body.innerHTML = originalContents;

    location.reload();
}
</script>
<!-- print -->
 <script>
function printUsers() {

    const rows = document.querySelectorAll("#usersTable tbody tr");

    let html = `
        <html>
        <head>
            <title>User Report</title>
            <style>
                body { font-family: Arial; padding: 20px; }
                h2 { text-align: center; margin-bottom: 20px; }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    font-size: 12px;
                }
                th, td {
                    border: 1px solid #000;
                    padding: 6px;
                    text-align: left;
                }
                th { background: #f0f0f0; }
            </style>
        </head>
        <body>

        <h2>User Report</h2>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>EMPID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Position</th>
                    <th>Department</th>
                    <th>Company</th>
                    <th>Area</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
    `;

    rows.forEach(row => {

        const cells = row.querySelectorAll("td");
        if (!cells.length) return;

        html += `
            <tr>
                <td>${cells[0]?.innerText || ""}</td>
                <td>${cells[1]?.innerText || ""}</td>
                <td>${cells[2]?.innerText || ""}</td>
                <td>${cells[3]?.innerText || ""}</td>
                <td>${cells[4]?.innerText || ""}</td>
                <td>${cells[5]?.innerText || ""}</td>
                <td>${cells[6]?.innerText || ""}</td>
                <td>${cells[7]?.innerText || ""}</td>
                <td>${cells[8]?.innerText || ""}</td>
            </tr>
        `;
    });

    html += `
            </tbody>
        </table>

        </body>
        </html>
    `;

    const win = window.open("", "", "width=1000,height=700");
    win.document.write(html);
    win.document.close();
    win.print();
}
</script>
<!-- export -->
 <script>
function exportUsersCSV() {

    const rows = document.querySelectorAll("#usersTable tbody tr");

    const headers = [
        "ID","EMPID","Name","Email","Position",
        "Department","Company","Area","Status"
    ];

    let csv = [headers.join(",")];

    rows.forEach(row => {

        const cells = row.querySelectorAll("td");
        if (!cells.length) return;

        const id = cells[0]?.innerText.trim() || "";
        const empid = cells[1]?.innerText.trim() || "";
        const name = cells[2]?.innerText.trim() || "";
        const email = cells[3]?.innerText.trim() || "";
        const position = cells[4]?.innerText.trim() || "";
        const department = cells[5]?.innerText.trim() || "";
        const company = cells[6]?.innerText.trim() || "";
        const area = cells[7]?.innerText.trim() || "";
        const status = cells[8]?.innerText.trim() || "";

        const rowData = [
            id, empid, name, email, position,
            department, company, area, status
        ].map(val => `"${val.replace(/"/g, '""')}"`);

        csv.push(rowData.join(","));
    });

    const blob = new Blob([csv.join("\n")], { type: "text/csv;charset=utf-8;" });

    const link = document.createElement("a");
    link.href = URL.createObjectURL(blob);
    link.download = `users_${new Date().toISOString().slice(0,10)}.csv`;
    link.click();
}
</script>