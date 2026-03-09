<?php
  session_start(); 
  include 'db/db.php';
  $message = "";
  $_SESSION['LAST_ACTIVITY'] = time(); 

  // Handle login form
  if ($_SERVER["REQUEST_METHOD"] == "POST") {
      $username = trim($_POST['username']); // emp_id or email
      $password = $_POST['password'];

      $sql = "SELECT user_id, emp_id, email, fullname, user_type, password , is_active
              FROM user_tb 
              WHERE (emp_id = ? OR email = ?) AND user_type = 'user'
              LIMIT 1";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("ss", $username, $username);
      $stmt->execute();
      $result = $stmt->get_result();

      if ($result->num_rows == 1) {
          $user = $result->fetch_assoc();

          if ($user['is_active'] == 0) {
              $message = "This account has been disabled. Please contact the administrator.";
          } elseif (password_verify($password, $user['password'])) {
              // login success
              $_SESSION['user_id'] = $user['user_id'];
              $_SESSION['fullname'] = $user['fullname'];
              $_SESSION['user_type'] = $user['user_type'];
              $_SESSION['department'] = $user['department'];
              header("Location: index.php");
              exit;
          } else {
              $message = "Invalid credentials. Please try again.";
          }

      } else {
          $message = "Access denied or user not found.";
      }
      $stmt->close();
  }
  $conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ITSM - User Login</title>
    <link rel="icon" type="image/x-icon" href="asset/img/Koppel_bip.ico">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="asset/css/login.css">
</head>
<body>
  
  <div class="login-container">
    <?php if(isset($_GET['msg']) && $_GET['msg'] == "timeout"): ?>
    <div class="alert alert-warning">
        Session expired due to 15 minutes inactivity. Please login again.
    </div>
    <?php endif; ?>
    <div class="login-card">
      <div class="login-header">
        <div class="logo-placeholder">
          <i class="fas fa-boxes"></i>
        </div>
        <h1>Asset Management System</h1>
        <p>Users Portal</p>
      </div>
      
      <div class="login-body">
        <?php if ($message): ?>
          <div class="alert alert-danger shake">
            <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($message) ?>
          </div>
        <?php endif; ?>

        <form method="POST">
          <div class="mb-4">
            <label for="username" class="form-label">Username</label>
            <div class="input-group">
              <i class="fas fa-user"></i>
              <input 
                type="text" 
                name="username" 
                id="username"
                class="form-control" 
                placeholder="Employee ID or Email"
                required 
                autofocus
              >
            </div>
          </div>

     <div class="mb-4">
      <div class="row">
      <div class="col-6">
        <label for="password" class="form-label">Password</label>
      </div>
      <div class="col-6">
        <!-- Caps Lock warning -->
          <small id="capsWarning" class="text-primary d-none">
              ⚠ Caps Lock is ON
          </small>
      </div>
      </div>

      <div class="input-group">
        <i class="fas fa-lock"></i>

        <input 
          type="password"
          name="password"
          id="password"
          class="form-control"
          required
        >
      </div>

        
        </div>

        <div class="text-end mb-3">
          <a href="../forgot_password.php" class="text-decoration-none">
            Forgot Password?
          </a>
        </div>

          <button type="submit" class="btn btn-login w-100">
            <i class="fas fa-sign-in-alt me-2"></i>Sign In to Dashboard
          </button>
        </form>
      </div>
    </div>
    
    <div class="text-center text-white mt-4 opacity-75">
      <small>© <?= date('Y') ?> Asset Management System. All rights reserved.</small>
    </div>
  </div>
  <!-- Add shake animation to error message -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const alert = document.querySelector('.alert');
      if (alert) {
        alert.classList.add('shake');
      }
    });

    const password = document.getElementById("password");
    const capsWarning = document.getElementById("capsWarning");

    password.addEventListener("keyup", function(event) {

        if (event.getModifierState("CapsLock")) {
            capsWarning.classList.remove("d-none");
        } else {
            capsWarning.classList.add("d-none");
        }

    });

    password.addEventListener("blur", function() {
        capsWarning.classList.add("d-none");
    });

    </script>
</body>
</html>