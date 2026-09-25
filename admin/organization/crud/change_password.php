<?php
include __DIR__ . '/../../../includes/db.php';
// Validate user_id from URL
if (!isset($_GET['user_id']) || !is_numeric($_GET['user_id'])) {
    header("Location: ../users.php");
    exit;
}

$user_id = (int)$_GET['user_id'];

// Get user info
$stmt = $conn->prepare("SELECT fullname, emp_id FROM user_tb WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: ../users.php");
    exit;
}

$user = $result->fetch_assoc();
$stmt->close();

$success = false;
$error = "";

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if (empty($password) || empty($confirm)) {
        $error = "All fields are required.";
    }
    elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    }
    elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    }
    else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("UPDATE user_tb SET password = ? WHERE user_id = ?");
        $stmt->bind_param("si", $hashed, $user_id);

        if ($stmt->execute()) {
            $success = true;
        } else {
            $error = "Failed to update password.";
        }

        $stmt->close();
    }
}
?>

    <div class="main-content" id="mainContent">
        <div class="card shadow-sm">
            <div class="card-header text-white">
                Change Password for <?= htmlspecialchars($user['fullname']) ?>
            </div>
            <div class="card-body">

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        Password updated successfully.
                    </div>
                    <a href="?page=organization/users" class="btn btn-secondary">Back to Users</a>
                <?php else: ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>

                        <div >
                            <button type="submit" class="btn btn-primary">Update Password</button>
                            <a href="#" onclick="window.history.back(); return false;" class="btn btn-secondary">Back</a>
                        </div>
                    </form>

                <?php endif; ?>

            </div>
        </div>
    </div>

<?php $conn->close(); ?>
