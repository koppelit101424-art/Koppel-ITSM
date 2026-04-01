<?php
include __DIR__ . '/../../includes/auth.php';
include __DIR__ . '/../../includes/db.php';

// Generate base URL dynamically
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$scriptPath = dirname($_SERVER['SCRIPT_NAME']);
$basePath = rtrim($scriptPath, '/\\');
$baseUrl = "$protocol://$host$basePath";

// Logged in admin ID
$adminId = $_SESSION['user_id'];

/* ==============================
   FETCH ADMIN USERS ONLY
============================== */
$admins = [];
$adminQuery = "
    SELECT user_id, fullname 
    FROM user_tb 
    WHERE user_type = 'admin'
    ORDER BY fullname ASC
";
$adminResult = $conn->query($adminQuery);

if ($adminResult && $adminResult->num_rows > 0) {
    while ($row = $adminResult->fetch_assoc()) {
        $admins[] = $row;
    }
}

/* ==============================
   FETCH TICKETS
============================== */
$stmt = $conn->prepare("
SELECT 
    t.*,
    u.fullname AS user_fullname,
    a.fullname AS assigned_admin
FROM ticket_tb t
LEFT JOIN user_tb u ON t.user_id = u.user_id
LEFT JOIN user_tb a 
    ON t.assigned_to = a.user_id 
    AND a.user_type = 'admin'
WHERE t.assigned_to = ?
ORDER BY t.ticket_id DESC
");

$stmt->bind_param("i", $adminId);
$stmt->execute();
$result = $stmt->get_result();

/* ==============================
   FETCH DISTINCT SUBJECTS
============================== */
$subjects = [];

$subjectQuery = "SELECT DISTINCT subject FROM ticket_tb ORDER BY subject ASC";
$subjectResult = $conn->query($subjectQuery);

if ($subjectResult && $subjectResult->num_rows > 0) {
    while ($row = $subjectResult->fetch_assoc()) {
        $subjects[] = $row['subject'];
    }
}
?>


<style>
.table-hover tbody tr:hover {
    background-color: #f1f1f1;
    cursor: pointer;
}

/* PRIORITY COLORS */
.badge-priority-highest { background-color: #8b0a17; }
.badge-priority-high { background-color: #dc3545; }
.badge-priority-medium { background-color: #ffc107; }
.badge-priority-low { background-color: #0d6efd; }
.badge-priority-lowest { background-color: #6ea8fe; }

/* STATUS COLORS */
.badge-status-reopened,
.badge-status-waiting-for-support,
.badge-status-waiting-for-customer,
.badge-status-in-progress,
.badge-status-escalated,
.badge-status-pending {
    background-color: #0d6efd;
}


.badge-status-canceled,
.badge-status-closed {
    background-color: grey;
}
.badge-status-resolved{
    background-color: #198754;
}
.select-unassigned {
    background-color: white !important;
      
}
.status-waiting-for-support { background-color: #0d6efd; color: white; }
.status-waiting-for-customer { background-color: #0d6efd; color: white; }
.status-in-progress { background-color: #0d6efd; color: white; }
.status-pending { background-color: #ffc107; color: white; }
.status-escalated { background-color: #0d6efd; color: white; }

.status-resolved { background-color: #198754; color: white; }
.status-canceled { background-color: grey; color: white; }
.status-closed { background-color: grey; color: white; }
.status-reopened { background-color: #0d6efd; color: white; }
</style>
</head>


<div class="card shadow-sm mb-3">
  <div class="card-header bg-white d-flex justify-content-between align-items-center">
    <h6 class="mb-0 text-primary fw-semibold">
      <i class="fas fa-filter me-1"></i> Ticket Filter
    </h6>

    <div class="d-flex gap-2">
      <button id="resetFilters" class="btn btn-secondary btn-sm">
        <i class="fas fa-undo me-1"></i> Reset
      </button>

      <button id="applyFilters" class="btn btn-primary btn-sm">
        <i class="fas fa-search me-1"></i> Filter
      </button>

      <button onclick="printTickets()" class="btn btn-success btn-sm">
        <i class="fas fa-print me-1"></i> Print
      </button>
    </div>
  </div>

  <div class="card-body">
    <div class="row g-3 align-items-end">

    <div class="col-md">
        <label class="form-label">Impact</label>
        <select id="impactFilter" class="form-select">
            <option value="">All</option>
            <option value="individual">Individual</option>
            <option value="department">Department</option>
            <option value="organization">Organization</option>
        </select>
    </div>

    <div class="col-md">
        <label class="form-label">Category</label>
        <select id="categoryFilter" class="form-select">
            <option value="">All</option>
            <option value="incident">Incident</option>
            <option value="service">Service</option>
            <option value="change">Change</option>
        </select>
    </div>

      <!-- Priority -->
      <div class="col-md">
        <label class="form-label">Priority</label>
        <select id="priorityFilter" class="form-select">
          <option value="">All</option>
          <option value="highest">Highest</option>
          <option value="high">High</option>
          <option value="medium">Medium</option>
          <option value="low">Low</option>
          <option value="lowest">Lowest</option>
        </select>
      </div>

      <!-- Subject -->
    <div class="col-md-2">
        <label class="form-label">Subject</label>
        <select id="subjectFilter" class="form-select">
            <option value="">All</option>
            <?php foreach ($subjects as $sub): ?>
                <option value="<?= strtolower($sub) ?>">
                    <?= htmlspecialchars($sub) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

      <!-- Assigned To -->
      <!-- <div class="col-md-2">
        <label class="form-label">Assigned To</label>
        <select id="assignedToFilter" class="form-select" readonly>
          <option value="">All</option>
          <?php foreach ($admins as $admin): ?>
            <option value="<?= strtolower($admin['fullname']) ?>">
              <?= htmlspecialchars($admin['fullname']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div> -->

      <!-- Status -->
      <div class="col-md-2">
        <label class="form-label">Status</label>
        <select id="statusSelectFilter" class="form-select">
          <option value="">All</option>
          <option value="waiting for support">Waiting for Support</option>
          <option value="waiting for customer">Waiting for Customer</option>
          <option value="in progress">In Progress</option>
          <option value="pending">Pending</option>
          <option value="escalated">Escalated</option>
          <option value="resolved">Resolved</option>
          <option value="canceled">Canceled</option>
          <option value="closed">Closed</option>
        </select>
      </div>

      <!-- Date From -->
      <div class="col-md">
        <label class="form-label">Date From</label>
        <input type="date" id="dateFrom" class="form-control">
      </div>

      <!-- Date To -->
      <div class="col-md">
        <label class="form-label">Date To</label>
        <input type="date" id="dateTo" class="form-control">
      </div>

    </div>
  </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center text-white">
        <h5 class="mb-0 text-white fw-semibold">My Tickets</h5>
            <a href="?page=ticket/crud/add_ticket" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> Create Ticket
            </a>
    </div>
</div>

<div class="card-body table-responsive mt-3">

<table id="ticketsTable" class="table table-hover table-striped">
<thead>
<tr>
    <th>Ticket #</th>
    <th>User</th>
    <th>Category</th>
    <th>Impact</th>
    <th>Priority</th>
    <th>Subject</th>
    <th style="width: 135px;">Assigned To</th>
    <th style="width: 160px;">Status</th>
    <th>Date Created</th>
</tr>
</thead>
<!-- id="adminTicketsBody" -->
<tbody >
<?php while ($ticket = $result->fetch_assoc()): ?>
<tr
    data-ticket-id="<?= $ticket['ticket_id'] ?>"
    data-status="<?= strtolower($ticket['status']) ?>"
    data-assigned="<?= $ticket['assigned_to'] == $adminId ? 'yes' : 'no' ?>">

    <td><?= htmlspecialchars($ticket['ticket_number']) ?></td>
    <td><?= htmlspecialchars($ticket['user_fullname']) ?></td>
    <td><?= ucfirst($ticket['ticket_category']) ?></td>
    <td><?= ucfirst($ticket['impact']) ?></td>
    <td>
        <span class="badge badge-priority-<?= strtolower($ticket['priority']) ?>">
            <?= ucfirst($ticket['priority']) ?>
        </span>
    </td>



    <td><?= htmlspecialchars($ticket['subject']) ?></td>
    <!-- ASSIGNED TO DROPDOWN -->
<td onclick="event.stopPropagation();" style="width:135px;">

    <select class="form-select form-select-sm assign-admin 
            <?= empty($ticket['assigned_to']) ? 'select-unassigned' : '' ?>"
            data-ticket-id="<?= $ticket['ticket_id'] ?>"
            data-current="<?= $ticket['assigned_to'] ?>">

        <?php foreach ($admins as $admin): ?>
            <option value="<?= $admin['user_id'] ?>" 
                <?= ($ticket['assigned_to'] == $admin['user_id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($admin['fullname']) ?>
            </option>
        <?php endforeach; ?>

    </select>

</td>

<?php
// Define the workflow
$statusFlow = [
    'waiting for support' => ['waiting for support','waiting for customer','in progress','escalated','pending','canceled','resolved'],
    'pending' => ['pending','waiting for support','in progress','canceled','resolved'],
    'waiting for customer' => ['waiting for customer','waiting for support','escalated','canceled','resolved'],
    'in progress' => ['in progress','pending','canceled','resolved'],
    'escalated' => ['escalated','in progress'],
    'canceled' => ['canceled','closed', 'reopened'],
    'resolved' => ['resolved','closed', 'reopened'],
    'reopened' => ['reopened','waiting for support','waiting for customer','in progress','escalated','pending','canceled','resolved'],
    'closed' => ['closed']
];

$currentStatus = strtolower($ticket['status']); // current status of this ticket
$allowedStatuses = $statusFlow[$currentStatus] ?? [$currentStatus]; // fallback to current status only
?>

<td class="status-cell" onclick="event.stopPropagation();" style="width:150px;">
    <select class="form-select form-select-sm status-select status-<?= str_replace(' ', '-', $currentStatus) ?>"
            data-ticket-id="<?= $ticket['ticket_id'] ?>"
            data-current="<?= $currentStatus ?>">
        <?php foreach ($allowedStatuses as $statusOption): ?>
            <option value="<?= $statusOption ?>" <?= $statusOption == $currentStatus ? 'selected' : '' ?>>
                <?= ucwords($statusOption) ?>
            </option>
        <?php endforeach; ?>
    </select>
</td>

    <td><?= date('m-d-Y', strtotime($ticket['date_created'])) ?></td>
</tr>
<?php endwhile; ?>
</tbody>
</table>

</div>
</div>

<script>const BASE_URL = '<?= $baseUrl ?>';</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>

<script>

    // PRIORITY SORT ORDER
$.fn.dataTable.ext.type.order['priority-sort-pre'] = function (data) {

    let value = $('<div>').html(data).text().toLowerCase().trim();

    switch (value) {
        case 'highest': return 1;
        case 'high': return 2;
        case 'medium': return 3;
        case 'low': return 4;
        case 'lowest': return 5;
        default: return 99;
    }
};

// IMPACT SORT ORDER
$.fn.dataTable.ext.type.order['impact-sort-pre'] = function (data) {

    let value = data.toLowerCase();

    switch (value) {
        case 'organization': return 1;
        case 'department': return 2;
        case 'individual': return 3;
        default: return 99;
    }
};

    $(document).ready(function () {

const table = $('#ticketsTable').DataTable({
    pageLength: 25,
    lengthMenu: [10, 25, 50, 100, 250, 500, 1000, 3000, 5000],
    order: [
        [4, "asc"], // PRIORITY (highest first)
        [3, "asc"], // IMPACT (organization first)
        [8, "desc"] // DATE (latest)
    ],
columnDefs: [

    // PRIORITY (COLUMN 4)
    {
        targets: 4,
        render: function (data, type, row) {

            let text = $('<div>').html(data).text().toLowerCase().trim();

            let order = {
                'highest': 1,
                'high': 2,
                'medium': 3,
                'low': 4,
                'lowest': 5
            };

            return type === 'sort' ? (order[text] || 99) : data;
        }
    },

    // IMPACT (COLUMN 3)
    {
        targets: 3,
        render: function (data, type, row) {

            let text = data.toLowerCase().trim();

            let order = {
                'organization': 1,
                'department': 2,
                'individual': 3
            };

            return type === 'sort' ? (order[text] || 99) : data;
        }
    }

]
});
           $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {

    const row = table.row(dataIndex).node();

    const category = $('#categoryFilter').val().toLowerCase();
    const impact = $('#impactFilter').val().toLowerCase();
    const priority = $('#priorityFilter').val();
    const subject = $('#subjectFilter').val().toLowerCase();
    const assignedTo = $('#assignedToFilter').val();
    const status = $('#statusSelectFilter').val();
    const dateFrom = $('#dateFrom').val();
    const dateTo = $('#dateTo').val();

    // CATEGORY
    if (category && !data[2].toLowerCase().includes(category)) return false;

    // IMPACT
    if (impact && !data[3].toLowerCase().includes(impact)) return false;

    // PRIORITY
    if (priority && !data[4].toLowerCase().includes(priority)) return false;

    // SUBJECT
    if (subject && !data[5].toLowerCase().includes(subject)) return false;

// ASSIGNED TO (REAL-TIME)
if (assignedTo) {
    let assignedText = $(row).find('td:eq(6) select option:selected').text().toLowerCase();
    if (!assignedText.includes(assignedTo)) return false;
}

// STATUS (REAL-TIME)
if (status) {
    let statusText = $(row).find('td:eq(7) select option:selected').text().toLowerCase();
    if (!statusText.includes(status)) return false;
}

    // DATE FILTER (column index 8)
    let rowDate = data[8]; // format: mm-dd-yyyy
    if (rowDate) {
        let parts = rowDate.split('-');
        let formatted = `${parts[2]}-${parts[0]}-${parts[1]}`; // yyyy-mm-dd

        if (dateFrom && formatted < dateFrom) return false;
        if (dateTo && formatted > dateTo) return false;
    }

    return true;
});

// APPLY FILTER BUTTON
$('#applyFilters').on('click', function () {
    table.draw();
});

// RESET
$('#resetFilters').on('click', function () {

    $('#categoryFilter, #impactFilter, #subjectFilter').val('');
    $('#priorityFilter, #assignedToFilter, #statusSelectFilter').val('');
    $('#dateFrom, #dateTo').val('');

    table.draw();
});
    });
</script>
<script>
$(document).ready(function () {

    // Row click (view ticket)
    $('#ticketsTable tbody').on('click', 'tr', function () {
        const ticketId = $(this).data('ticket-id');
        if (ticketId) {
            window.location.href = '?page=ticket/view_ticket&ticket_id=' + ticketId;
        }
    });

    // Reassign ticket via AJAX
    $(document).on('change', '.assign-admin', function (e) {

        e.stopPropagation();

        let select = $(this);
        let ticketId = select.data('ticket-id');
        let currentValue = select.data('current') ?? '';
        let newValue = select.val();

        if (currentValue == newValue) return; // nothing changed

        let confirmChange = confirm("Are you sure you want to change the assigned admin?");
        if (!confirmChange) {
            select.val(currentValue); // revert selection
            return;
        }

        $.ajax({
            url: '?page=ticket/includes/reassign_ticket',
            type: 'POST',
            data: {
                ticket_id: ticketId,
                admin_id: newValue
            },
            success: function () {

                // Update select current value
                select.data('current', newValue);

                // Update background color for unassigned
                if (newValue === "" || newValue == "0") {
                    select.addClass('select-unassigned');
                } else {
                    select.removeClass('select-unassigned');
                }

                // Automatically update Status column instantly
                let row = select.closest('tr');
                let statusCell = row.find('td.status-cell');

                // Determine new status
                let statusValue = (newValue === "" || newValue == "0") ? 'waiting for support' : 'in progress';
                row.attr('data-status', statusValue); // update row data-status

                // Update the status dropdown in the table cell
                let statusSelect = statusCell.find('.status-select');
                statusSelect.val(statusValue);
                statusSelect.data('current', statusValue);

                // Optionally update badge background instantly (if using badges in place of select)
                // let className = 'badge-status-' + statusValue.replace(/ /g, '-');
                // statusCell.html('<span class="badge ' + className + '">' + statusValue.toUpperCase() + '</span>');

                // Refresh the DataTable row so filters and sorting are correct
                // $('#ticketsTable').DataTable().row(row).invalidate().draw(false);
                location.reload();
            },
            error: function () {
                alert('Failed to update assignment.');
                select.val(currentValue);
            }
        });

    });

});
</script>
<script>
$(document).on('change', '.status-select', function (e) {
    e.stopPropagation();

    let select = $(this);
    let ticketId = select.data('ticket-id');
    let currentValue = select.data('current');
    let newValue = select.val();

    if (currentValue === newValue) return; // no change

    let confirmChange = confirm("Are you sure you want to change the ticket status?");
    if (!confirmChange) {
        select.val(currentValue);
        return;
    }

    $.ajax({
        url: '?page=ticket/includes/update_ticket_status', // endpoint to update DB
        type: 'POST',
        data: {
            ticket_id: ticketId,
            status: newValue
        },
        success: function () {
            // Update stored current value
            select.data('current', newValue);

            // Update background color based on status
            // First remove old status classes
            select.removeClass(function(index, className) {
                return (className.match(/(^|\s)status-\S+/g) || []).join(' ');
            });
            // Add new status class
            select.addClass('status-' + newValue.toLowerCase().replace(/ /g, '-'));

            // Optionally update the row's data-status attribute
            select.closest('tr').attr('data-status', newValue.toLowerCase());

            // Optional: reload the row completely (uncomment if needed)
            // location.reload(); // full page reload
            // OR reload only the table data via DataTables API if using DT
            $('#ticketsTable').DataTable().row(select.closest('tr')).invalidate().draw(false);
        },
        error: function () {
            alert('Failed to update status.');
            select.val(currentValue);
        }
    });
});
</script>
<script>
function loadAdminTickets() {
    fetch("?page=ticket/includes/fetch_admin_tickets.php")
        .then(response => response.text())
        .then(data => {
            document.getElementById("adminTicketsBody").innerHTML = data;

            // Re-attach click event
            document.querySelectorAll(".ticket-row").forEach(row => {
                row.addEventListener("click", () => {
                    const ticketId = row.dataset.ticketId;
                    window.location.href = `view_ticket.php?ticket_id=${ticketId}`;
                });
            });
        });
}

// Load immediately
loadAdminTickets();

// Refresh every 2 seconds
setInterval(loadAdminTickets, 2000);
setInterval(() => {
    location.reload();
}, 30000);
</script>
<script>
    //print
    function printTickets() {

        let rows = document.querySelectorAll("#ticketsTable tbody tr");

        let html = `
        <h2 style="text-align:center;">Ticket Report</h2>
        <table border="1" cellspacing="0" cellpadding="6" width="100%">
        <thead>
            <tr>
                <th>Ticket #</th>
                <th>User</th>
                <th>Category</th>
                <th>Impact</th>
                <th>Priority</th>
                <th>Subject</th>
                <th>Assigned To</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
        `;

        rows.forEach(row => {

            if (row.style.display === "none") return; // only filtered rows

            let cols = row.querySelectorAll("td");

            let assigned = cols[6].querySelector("select");
            let status = cols[7].querySelector("select");

            html += `
            <tr>
                <td>${cols[0].innerText}</td>
                <td>${cols[1].innerText}</td>
                <td>${cols[2].innerText}</td>
                <td>${cols[3].innerText}</td>
                <td>${cols[4].innerText}</td>
                <td>${cols[5].innerText}</td>
                <td>${assigned ? assigned.options[assigned.selectedIndex].text : cols[6].innerText}</td>
                <td>${status ? status.options[status.selectedIndex].text : cols[7].innerText}</td>
                <td>${cols[8].innerText}</td>
            </tr>
            `;
        });

        html += "</tbody></table>";

        let win = window.open("", "", "width=1200,height=700");

        win.document.write(`
        <html>
        <head>
            <title>Ticket Report</title>
            <style>
                body{font-family:Arial;padding:20px}
                table{border-collapse:collapse;width:100%}
                th,td{border:1px solid #000;padding:6px}
                th{background:#eee}
            </style>
        </head>
        <body>${html}</body>
        </html>
        `);

        win.document.close();
        win.print();
    }
</script>
<?php $conn->close(); ?>
