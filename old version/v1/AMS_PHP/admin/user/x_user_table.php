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
                        <th>Position</th>
                        <th>Department</th>
                        <th>Company</th>
                        <th>Area</th>
                        <th>Hired</th>
                        <th>Resigned</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['user_id'] ?></td>
                        <td><?= htmlspecialchars($row['emp_id']) ?></td>
                        <td><?= htmlspecialchars($row['fullname']) ?></td>
                        <td><?= htmlspecialchars($row['position']) ?></td>
                        <td><?= htmlspecialchars($row['department']) ?></td>
                        <td><?= htmlspecialchars($row['company']) ?></td>
                        <td><?= htmlspecialchars($row['area']) ?></td>
                        <td style="width: 150px;"><?= !empty($row['date_hired']) && $row['date_hired'] != '0000-00-00' ? date('m-d-Y', strtotime($row['date_hired'])) : '' ?></td>
                        <td style="width: 150px;">
                            <?php
                                $resigned = $row['date_resigned'] ?? '';
                                if (empty($resigned) || $resigned === '0000-00-00' || strtolower($resigned) === 'awol') {
                                    echo '<span class="text-success">Employed</span>';
                                } else {
                                    echo date('m-d-Y', strtotime($resigned));
                                }
                            ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="9" class="text-center">No users found</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Context Menu -->
<div id="contextMenu" class="custom-menu">
    <a href="#" id="editUserLink">Edit</a>
    <a href="#" id="deleteUserLink" class="text-danger">Delete</a>
    <a href="#" id="resignedUserLink">Mark Resigned</a>
</div>

<?php if (!empty($resign_success)): ?>
    <div class="alert alert-success alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 1050;">
        User marked as resigned successfully.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<style>
    .custom-menu {
        display: none;
        position: absolute;
        background: white;
        border: 1px solid #ddd;
        box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        z-index: 10000;
        min-width: 140px;
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
</style>
