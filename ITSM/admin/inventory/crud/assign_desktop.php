<?php
include '../../auth/auth.php';
include '../../db/db.php';

$desktop_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($desktop_id <= 0) {
    die('Invalid desktop ID.');
}

// Fetch desktop info
$desk_sql = "SELECT cpu, tag_number FROM desktop_tb WHERE desktop_id = ?";
$desk_stmt = $conn->prepare($desk_sql);
$desk_stmt->bind_param("i", $desktop_id);
$desk_stmt->execute();
$desktop = $desk_stmt->get_result()->fetch_assoc();
$desk_stmt->close();

// Fetch current assignment
$current_user = null;
$current_user_id = null;
$curr_sql = "SELECT u.user_id, u.fullname FROM user_desktop_tb ud JOIN user_tb u ON ud.user_id = u.user_id WHERE ud.desktop_id = ?";
$curr_stmt = $conn->prepare($curr_sql);
$curr_stmt->bind_param("i", $desktop_id);
$curr_stmt->execute();
$curr_result = $curr_stmt->get_result();
if ($row = $curr_result->fetch_assoc()) {
    $current_user = $row['fullname'];
    $current_user_id = $row['user_id'];
}
$curr_stmt->close();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['remove_assignment'])) {
        // Remove current assignment
        $del_stmt = $conn->prepare("DELETE FROM user_desktop_tb WHERE desktop_id = ?");
        $del_stmt->bind_param("i", $desktop_id);
        if ($del_stmt->execute()) {
            $message = "<div class='alert alert-success'>Desktop unassigned successfully!</div>";
            $current_user = null;
            $current_user_id = null;
        } else {
            $message = "<div class='alert alert-danger'>Failed to remove assignment. Please try again.</div>";
        }
        $del_stmt->close();
    } else {
        // Assign / Reassign
        $user_id = (int)($_POST['user_id'] ?? 0);
        if ($user_id > 0) {
            $conn->autocommit(FALSE);
            try {
                // Delete old assignment
                $del_stmt = $conn->prepare("DELETE FROM user_desktop_tb WHERE desktop_id = ?");
                $del_stmt->bind_param("i", $desktop_id);
                $del_stmt->execute();
                $del_stmt->close();

                // Insert new assignment
                $ins_stmt = $conn->prepare("INSERT INTO user_desktop_tb (user_id, desktop_id, date_created) VALUES (?, ?, NOW())");
                $ins_stmt->bind_param("ii", $user_id, $desktop_id);
                $ins_stmt->execute();
                $ins_stmt->close();

                $conn->commit();
                $conn->autocommit(TRUE);

                // Get user name
                $u_stmt = $conn->prepare("SELECT fullname FROM user_tb WHERE user_id = ?");
                $u_stmt->bind_param("i", $user_id);
                $u_stmt->execute();
                $u_name = $u_stmt->get_result()->fetch_assoc()['fullname'] ?? 'Unknown';
                $u_stmt->close();

                $message = "<div class='alert alert-success'>Desktop successfully assigned to <strong>" . htmlspecialchars($u_name) . "</strong>!</div>";
                $current_user = $u_name;
                $current_user_id = $user_id;

                echo "<script>
                    window.location.href='?page=inventory/desktops';
                </script>";
            } catch (Exception $e) {
                $conn->rollback();
                $conn->autocommit(TRUE);
                $message = "<div class='alert alert-danger'>Failed to assign desktop. Please try again.</div>";
            }
        } else {
            $message = "<div class='alert alert-warning'>Please select a user.</div>";
        }
    }
}

// Fetch all users
$user_result = $conn->query("SELECT user_id, fullname, position, department FROM user_tb ORDER BY fullname");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assign Desktop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../asset/css/main.css">
    <style>
        .searchable-select { position: relative; }
        .search-input {
            width: 100%; padding: 6px 12px; border: 1px solid #ced4da; border-radius: 4px; margin-bottom: 6px; font-size: 0.875rem;
        }
        .user-list {
            max-height: 200px; overflow-y: auto; border: 1px solid #ced4da; border-top: none; border-radius: 0 0 4px 4px;
        }
        .user-item { padding: 6px 12px; cursor: pointer; font-size: 0.875rem; }
        .user-item:hover, .user-item.selected { background-color: #e9ecef; }
        .user-info { font-weight: normal; color: #6c757d; font-size: 0.8125rem; }
    </style>
</head>
<body>
    <div class="sidebar col-md-2" id="sidebar" style="height: 100vh; width: 250px;">
        <h4 class="text-white px-3 sidebar-title">Asset Management</h4>
        <?php include '../sidebar.php'; ?>
    </div>
    <div class="main-content" id="mainContent">
        <?php include '../../header.php'; ?>

        <div class="container-fluid mt-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-user-plus me-2"></i> Assign / Reassign Desktop</h5>
                    <?php if ($desktop): ?>
                        <p class="mb-0 text-muted">
                            <small>Desktop: <?= htmlspecialchars($desktop['cpu'] ?? 'N/A') ?> | Tag #: <?= htmlspecialchars($desktop['tag_number'] ?? 'N/A') ?></small>
                        </p>
                    <?php endif; ?>
                    <?php if ($current_user): ?>
                        <p class="mb-0 text-warning">
                            <small><i class="fas fa-exclamation-circle me-1"></i> Currently assigned to: <strong><?= htmlspecialchars($current_user) ?></strong></small>
                        </p>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?= $message ?>

                    <form method="POST" id="assignForm">
                        <input type="hidden" name="user_id" id="selectedUserId" required>

                        <div class="mb-3">
                            <label class="form-label">Select User to Assign *</label>
                            <div class="searchable-select">
                                <input type="text" class="search-input" id="userSearch" placeholder="Type to search users...">
                                <div class="user-list" id="userList">
                                    <?php if ($user_result && $user_result->num_rows > 0): ?>
                                        <?php while ($user = $user_result->fetch_assoc()): ?>
                                            <div class="user-item"
                                                data-id="<?= $user['user_id'] ?>"
                                                data-name="<?= htmlspecialchars($user['fullname']) ?>">
                                                <div><?= htmlspecialchars($user['fullname']) ?></div>
                                                <div class="user-info"><?= htmlspecialchars($user['position']) ?> (<?= htmlspecialchars($user['department']) ?>)</div>
                                            </div>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <div class="user-item">No users found</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="mt-2">
                                <small>Selected: <span id="selectedUserText" class="text-primary">None</span></small>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success" id="assignBtn" <?= $current_user_id ? '' : 'disabled' ?>>
                                <i class="fas fa-sync-alt me-1"></i> Assign / Reassign
                            </button>

                            <?php if ($current_user): ?>
                                <button type="submit" name="remove_assignment" class="btn btn-danger">
                                    <i class="fas fa-trash me-1"></i> Remove Assignment
                                </button>
                            <?php endif; ?>

                            <a href="desktop.php" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('userSearch');
    const userList = document.getElementById('userList');
    const userItems = userList.querySelectorAll('.user-item');
    const selectedUserId = document.getElementById('selectedUserId');
    const selectedUserText = document.getElementById('selectedUserText');
    const assignBtn = document.getElementById('assignBtn');

    let selectedElement = null;

    userItems.forEach(item => {
        item.addEventListener('click', function () {
            if (selectedElement) selectedElement.classList.remove('selected');
            this.classList.add('selected');
            selectedElement = this;

            const userId = this.getAttribute('data-id');
            const userName = this.getAttribute('data-name');
            selectedUserId.value = userId;
            selectedUserText.textContent = userName;
            assignBtn.disabled = false;
        });
    });

    searchInput.addEventListener('input', function () {
        const query = this.value.toLowerCase().trim();
        userItems.forEach(item => {
            const text = item.textContent.toLowerCase();
            item.style.display = text.includes(query) ? 'block' : 'none';
        });
    });

    searchInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const visibleItems = Array.from(userItems).filter(item => item.style.display !== 'none');
            if (visibleItems.length > 0) {
                visibleItems[0].click();
                searchInput.value = visibleItems[0].getAttribute('data-name');
            }
        }
    });
});
</script>
</body>
</html>
<?php $conn->close(); ?>
