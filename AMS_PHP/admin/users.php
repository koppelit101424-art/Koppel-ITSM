<?php
include 'auth/auth.php';
include 'db/db.php';

// Handle resignation FIRST
$resign_success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Resignation
    if (isset($_POST['date_resigned']) && isset($_POST['user_id'])) {
        $user_required = (int)$_POST['user_id'];
        $date_resigned = $_POST['date_resigned'];

        if ($user_required > 0 && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_resigned)) {
            $stmt = $conn->prepare("UPDATE user_tb SET date_resigned = ? WHERE user_id = ?");
            $stmt->bind_param("si", $date_resigned, $user_required);
            if ($stmt->execute()) {
                $resign_success = true;
            }
            $stmt->close();
        }
    }

    // Enable / Disable Account
    if (isset($_POST['toggle_status_user_id']) && isset($_POST['new_status'])) {
        $user_id = (int)$_POST['toggle_status_user_id'];
        $new_status = $_POST['new_status'] === '1' ? 1 : 0; // 1=Active, 0=Disabled
        $stmt = $conn->prepare("UPDATE user_tb SET is_active = ? WHERE user_id = ?");
        $stmt->bind_param("ii", $new_status, $user_id);
        $stmt->execute();
        $stmt->close();
    }
}

// Fetch users
$sql = "SELECT * FROM user_tb ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ITSM - Users</title>
    <link rel="icon" href="asset/img/Koppel_bip.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="asset/css/main.css">
    <link rel="stylesheet" href="asset/css/menu.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
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
            font-size: 0.9rem;
        }
        .custom-menu a:hover {
            background-color: #f8f9fa;
        }
        .custom-menu a.text-danger:hover {
            background-color: #f8d7da;
        }
    </style>
</head>
<body>
<!-- Sidebar -->
<?php include 'sidebar.php'; ?>

    <div class="main-content" id="mainContent">
        <?php include 'header.php'; ?>

        <!-- Users Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>User Management</span>
                <a href="user/add_user.php">
                    <button class="btn btn-blue btn-sm">
                        <i class="fas fa-plus me-1"></i> Add User
                    </button>
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
                            <tr>
                                <td><?= $row['user_id'] ?></td>
                                <td><?= htmlspecialchars($row['emp_id']) ?></td>
                                <td><?= htmlspecialchars($row['fullname']) ?></td>
                                <td><?= htmlspecialchars($row['email']) ?></td>
                                <td style="width: 90px;"><?= htmlspecialchars($row['position']) ?></td>
                                <td><?= htmlspecialchars($row['department']) ?></td>
                                <td><?= htmlspecialchars($row['company']) ?></td>
                                <td><?= htmlspecialchars($row['area']) ?></td>
                                <!-- <td style="width: 150px;"><?= !empty($row['date_hired']) && $row['date_hired'] != '0000-00-00' ? date('m-d-Y', strtotime($row['date_hired'])) : '' ?></td>
                                <td style="width: 150px;">
                                    <?php
                                        $resigned = $row['date_resigned'] ?? '';
                                        if (empty($resigned) || $resigned === '0000-00-00' || strtolower($resigned) === 'awol') {
                                            echo '<span class="text-success">Employed</span>';
                                        } else {
                                            echo date('m-d-Y', strtotime($resigned));
                                        }
                                    ?>
                                </td> -->
                                <td>
                                    <?php
                                        echo isset($row['is_active']) && $row['is_active'] == 1
                                            ? '<span class="badge bg-success">Active</span>'
                                            : '<span class="badge bg-secondary">Disabled</span>';
                                    ?>
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

        <!-- Context Menu -->
        <div id="contextMenu" class="custom-menu">
<a href="#" id="editUserLink"><i class="fas fa-edit text-primary me-1"></i> Edit</a>
<a href="#" id="deleteUserLink" class="text-danger"><i class="fas fa-trash me-1"></i> Delete</a>
<a href="#" id="toggleStatusLink"><i class="fas fa-toggle-on me-1"></i> Enable / Disable</a>
<a href="#" id="changePasswordLink"><i class="fas fa-key me-1"></i> Change Password</a>
          <!-- <a href="#" id="resignedUserLink">Mark Resigned</a> -->
        </div>

        <?php if (!empty($resign_success)): ?>
            <div class="alert alert-success alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 1050;">
                User marked as resigned successfully.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Resignation Modal -->
        <div class="modal fade" id="resignModal" tabindex="-1" aria-labelledby="resignModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="resignModalLabel">Mark User as Resigned</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" action="">
                        <div class="modal-body">
                            <input type="hidden" id="resignUserId" name="user_id">
                            <div class="mb-3">
                                <label for="resignDate" class="form-label">Resignation Date *</label>
                                <input type="date" class="form-control" id="resignDate" name="date_resigned" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <p class="text-muted">This will update the user's status to resigned.</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger">Confirm Resignation</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Toggle Status Form -->
        <form id="toggleStatusForm" method="POST" style="display:none;">
            <input type="hidden" name="toggle_status_user_id" id="toggleStatusUserId">
            <input type="hidden" name="new_status" id="toggleStatusValue">
        </form>
    </div>

    <!-- Load JS at the BOTTOM -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>

    <!-- Context Menu + Resign + Enable/Disable JS -->
<script>
$(document).ready(function () {

    var table = $('#usersTable').DataTable({
        "pageLength": 10,
        "lengthMenu": [5, 10, 25, 50, 100],
        "order": [[0, "desc"]]
    });

    const contextMenu = document.getElementById('contextMenu');
    let selectedUserId = null;
    let selectedUserRow = null;

    // 🔥 THIS WORKS ON ALL PAGES
    $('#usersTable tbody').on('contextmenu', 'tr', function (e) {
        e.preventDefault();

        selectedUserRow = this;
        selectedUserId = $(this).find('td:first').text().trim();

        // Edit
        $('#editUserLink').attr('href',
            'user/edit_user.php?user_id=' + encodeURIComponent(selectedUserId)
        );

        // Change Password
        $('#changePasswordLink').attr('href',
            'user/change_password.php?user_id=' + encodeURIComponent(selectedUserId)
        );

        // Enable / Disable
        $('#toggleStatusLink').off('click').on('click', function(e){
            e.preventDefault();

            const statusText = $(selectedUserRow).find('td:last').text();

            const currentStatus = statusText.includes('Active') ? 1 : 0;
            const newStatus = currentStatus === 1 ? 0 : 1;

            $('#toggleStatusUserId').val(selectedUserId);
            $('#toggleStatusValue').val(newStatus);
            $('#toggleStatusForm').submit();
        });

        // Delete
        $('#deleteUserLink').off('click').on('click', function (e) {
            e.preventDefault();

            const userName = $(selectedUserRow).find('td').eq(2).text();

            if (confirm('Are you sure you want to delete ' + userName + '?')) {
                window.location.href =
                    'user/delete_user.php?user_id=' + encodeURIComponent(selectedUserId);
            }

            contextMenu.style.display = 'none';
        });

        // Resign
        $('#resignedUserLink').off('click').on('click', function (e) {
            e.preventDefault();

            $('#resignUserId').val(selectedUserId);

            const modal = new bootstrap.Modal(
                document.getElementById('resignModal')
            );
            modal.show();

            contextMenu.style.display = 'none';
        });

        // Show menu
        contextMenu.style.display = 'block';
        contextMenu.style.left = e.pageX + 'px';
        contextMenu.style.top = e.pageY + 'px';

        const rect = contextMenu.getBoundingClientRect();

        if (rect.right > window.innerWidth)
            contextMenu.style.left = (e.pageX - rect.width) + 'px';

        if (rect.bottom > window.innerHeight)
            contextMenu.style.top = (e.pageY - rect.height) + 'px';
    });

    // Hide on click outside
    $(document).on('click', function () {
        contextMenu.style.display = 'none';
    });

    $('#contextMenu').on('click', function (e) {
        e.stopPropagation();
    });
    // === Global Search ===
    $('#globalSearch').on('keyup', function() {
        table.search(this.value).draw();
    });

});
</script>

</body>
</html>
<?php $conn->close(); ?>
