<?php
include 'includes/db.php';

$token = $_GET['token'] ?? '';
$message = "";
$success = false;
$showForm = false;
$expiry = 0; // Initialize to avoid undefined variable

// Check if token exists
if ($token) {
    $stmt = $conn->prepare("SELECT user_id, reset_token_expiry FROM user_tb WHERE reset_token = ? LIMIT 1");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $expiry = strtotime($user['reset_token_expiry']);

        if ($expiry >= time()) {
            $showForm = true; // Token valid, show form
        } else {
            $message = "<div class='alert alert-danger'>Token has expired. Please request a new password reset.</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>Invalid token.</div>";
    }
} else {
    $message = "<div class='alert alert-danger'>No token provided.</div>";
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && $showForm) {
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($password !== $confirm) {
        $message = "<div class='alert alert-warning'>Passwords do not match.</div>";
    } else {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("
            UPDATE user_tb 
            SET password=?, reset_token=NULL, reset_token_expiry=NULL
            WHERE reset_token=?
        ");
        $stmt->bind_param("ss", $passwordHash, $token);

        if ($stmt->execute()) {
            $message = "<div class='alert alert-success'>Password reset successful. Redirecting to login...</div>";
            $success = true;
            $showForm = false;
            header("refresh:1;url=?page=admin/login.php"); 
            // Redirect after 3 seconds
        } else {
            $message = "<div class='alert alert-danger'>Failed to reset password. Try again.</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reset Password</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background: #f5f5f5; }
.reset-card {
    max-width: 450px;
    margin: 80px auto;
    padding: 30px;
    border-radius: 10px;
    background: #fff;
    box-shadow: 0 0 15px rgba(0,0,0,0.1);
}
#capsWarning, #capsWarningConfirm { display: none; }
</style>
</head>
<body>

<div class="reset-card">
    <h4 class="mb-3 text-center">Reset Password</h4>

    <?= $message ?>

    <?php if ($showForm): ?>
    <form method="POST">
        <div class="mb-3">
            <label for="password" class="form-label">New Password</label>
            <input type="password" id="password" name="password" class="form-control" required>
            <small id="capsWarning" class="text-warning">⚠ Caps Lock is ON</small>
        </div>

        <div class="mb-3">
            <label for="confirm_password" class="form-label">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
            <small id="capsWarningConfirm" class="text-warning">⚠ Caps Lock is ON</small>
        </div>

        <button type="submit" class="btn btn-primary w-100">Reset Password</button>
    </form>
    <?php endif; ?>
</div>

<script>
// Caps Lock detection
function addCapsLockWarning(inputId, warningId) {
    const input = document.getElementById(inputId);
    const warning = document.getElementById(warningId);

    if(input){
        input.addEventListener('keyup', function(event){
            warning.style.display = event.getModifierState('CapsLock') ? 'block' : 'none';
        });
        input.addEventListener('blur', function(){ warning.style.display = 'none'; });
    }
}

addCapsLockWarning('password', 'capsWarning');
addCapsLockWarning('confirm_password', 'capsWarningConfirm');
</script>

</body>
</html>
