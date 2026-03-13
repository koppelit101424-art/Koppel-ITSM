<?php
session_start();
include 'db/db.php';

$message = "";

// Handle login form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']); // emp_id or email
    $password = $_POST['password'];

    $sql = "SELECT user_id, emp_id, email, fullname, user_type, password 
            FROM user_tb 
            WHERE (emp_id = ? OR email = ?) AND user_type = 'admin' 
            LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $user['password'])) {
            // Store session
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['user_type'] = $user['user_type'];

            // Redirect to dashboard
            header("Location: index.php");
            exit;
        } else {
            $message = "❌ Invalid credentials.";
        }
    } else {
        $message = "❌ Access denied or user not found.";
    }
    $stmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login - Inventory System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: #f8f9fa; }
    .login-box {
      max-width: 400px;
      margin: 80px auto;
      padding: 25px;
      background: #fff;
      border-radius: 10px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
  </style>
</head>
<body>
  <div class="login-box">
    <h3 class="text-center mb-4">Admin Login</h3>

    <?php if ($message): ?>
      <div class="alert alert-danger"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="mb-3">
        <label class="form-label">Username</label>
        <input type="text" name="username" class="form-control" required autofocus>
      </div>

      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" required>
      </div>

      <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>
  </div>
</body>
</html>


