<?php
include __DIR__ . '/../../includes/auth.php';
include __DIR__ . '/../../includes/db.php';
include __DIR__ . '/includes/user_sql.php';
?>
<!-- Users Table -->
    <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center bg-white border-bottom-0">
            <h5 class="mb-0 text-primary fw-semibold">User Management</h5>
            <a href="user/add_user.php" class="btn btn-sm btn-primary me-2">
                <i class="fas fa-plus me-1"></i>  Add User
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

    <?php include __DIR__ . '/includes/user_menu.php'; ?>
    <?php include __DIR__ . '/includes/resignation_modal.php'; ?>
    <?php include __DIR__ . '/includes/toggle_status.php'; ?>
</div>
    <?php include __DIR__ . '/includes/user_js.php'; ?>
