<?php
include '../auth/auth.php';
include '../db/db.php';
include '../../config/config.php';

$user_id = $_SESSION['user_id'] ?? 0;

// Fetch current user data
$sql = "SELECT emp_id, fullname, position, email, department, company, area, user_type, date_hired 
        FROM user_tb WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname    = $_POST['fullname'] ?? '';
    $position    = $_POST['position'] ?? '';
    $email       = $_POST['email'] ?? '';
    $department  = $_POST['department'] ?? '';
    $company     = $_POST['company'] ?? '';
    $area        = $_POST['area'] ?? '';
    $date_hired  = $_POST['date_hired'] ?? '';

    // NOTE: user_type is intentionally NOT updated
    $update_sql = "UPDATE user_tb 
                   SET fullname=?, position=?, email=?, department=?, company=?, area=?, date_hired=? 
                   WHERE user_id=?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param(
        "sssssssi",
        $fullname,
        $position,
        $email,
        $department,
        $company,
        $area,
        $date_hired,
        $user_id
    );

    if ($update_stmt->execute()) {
        $_SESSION['fullname'] = $fullname;
        $success = "Profile updated successfully!";
        // Refresh user data (without user_type change)
        $user = array_merge($user, $_POST);
    } else {
        $error = "Failed to update profile.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Profile</title>

<link rel="icon" href="../asset/img/Koppel_bip.ico">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="../asset/css/main.css" rel="stylesheet">
<link href="../asset/css/menu.css" rel="stylesheet">
</head>
<body>
<div class="main-content d-flex" id="mainContent">
    <?php include '../sidebar.php'; ?>

    <div class="content flex-grow-1">
        <?php include '../header.php'; ?>

        <div class="container-fluid mt-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-user me-2"></i>Edit Profile</h5>
                </div>
                <div class="card-body">
                    <?php if(isset($success)): ?>
                        <div class="alert alert-success"><?= $success ?></div>
                    <?php endif; ?>
                    <?php if(isset($error)): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="emp_id" class="form-label">Employee ID</label>
                                <input type="text" class="form-control" id="emp_id"
                                       value="<?= htmlspecialchars($user['emp_id']) ?>" readonly>
                            </div>

                            <div class="col-md-6">
                                <label for="fullname" class="form-label">Full Name</label>
                                <input type="text" class="form-control" name="fullname" id="fullname"
                                       value="<?= htmlspecialchars($user['fullname']) ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label for="position" class="form-label">Position</label>
                                <input type="text" class="form-control" name="position" id="position"
                                       value="<?= htmlspecialchars($user['position']) ?>">
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" id="email"
                                       value="<?= htmlspecialchars($user['email']) ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label for="department" class="form-label">Department</label>
                                <input type="text" class="form-control" name="department" id="department"
                                       value="<?= htmlspecialchars($user['department']) ?>">
                            </div>

                            <div class="col-md-6">
                                <label for="company" class="form-label">Company</label>
                                <input type="text" class="form-control" name="company" id="company"
                                       value="<?= htmlspecialchars($user['company']) ?>">
                            </div>

                            <div class="col-md-6">
                                <label for="area" class="form-label">Area</label>
                                <input type="text" class="form-control" name="area" id="area"
                                       value="<?= htmlspecialchars($user['area']) ?>">
                            </div>

                            <div class="col-md-6">
                                <label for="date_hired" class="form-label">Date Hired</label>
                                <input type="text" class="form-control" name="date_hired" id="date_hired" readonly
                                       value="<?= !empty($user['date_hired']) ? date('F j, Y', strtotime($user['date_hired'])) : '' ?>"
                                       placeholder="January 20, 2026">
                            </div>
                        </div>

                        <div class="mt-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Save Changes
                            </button>
                            <a href="../index.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>
