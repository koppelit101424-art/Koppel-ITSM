<?php
// session_start(); 
include '../includes/db.php';
// include '../includes/auth.php';

$message = "";
// $_SESSION['LAST_ACTIVITY'] = time();

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $sql = "SELECT user_id, emp_id, email, fullname, user_type, password, is_active
            FROM user_tb 
            WHERE (emp_id = ? OR email = ?) AND user_type IN ('admin', 'agent', 'manager')
            LIMIT 1";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows == 1) {

        $user = $result->fetch_assoc();

        if ($user['is_active'] == 0) {

            $message = "This account has been disabled.";

        } elseif (password_verify($password, $user['password'])) {

            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['user_type'] = $user['user_type'];
            $_SESSION['LAST_ACTIVITY'] = time();

            // after successful login
            header("Location: index.php?page=dashboard"); // redirect to router
            exit;

        } else {
            $message = "Invalid credentials.";
        }

    } else {
        $message = "User not found.";
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
    <title>ITSM - Admin Login</title>
  <link rel="icon" type="image/x-icon" href="../assets/img/Koppel_bip.ico">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- <link rel="stylesheet" href="../assets/css/login.css"> -->
</head>
<style>

    body {
        background: white;
        min-height: 100vh;
        display: flex;
        align-items: center;
        padding: 20px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .login-container {
        max-width: 450px;
        width: 100%;
        margin: 0 auto;
    }

    .login-card {
        background: rgba(255, 255, 255, 0.95);
        border-radius: 20px;
        box-shadow: 0 15px 50px rgba(0, 0, 0, 0.3);
        overflow: hidden;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .login-header {
        background: linear-gradient(to right, #1E3A8A, #1E3A8A);
        color: white;
        padding: 30px 20px;
        text-align: center;
    }

    .login-header h1 {
        font-weight: 700;
        font-size: 1.8rem;
        margin-bottom: 5px;
    }

    .login-header p {
        opacity: 0.9;
        font-size: 0.95rem;
    }

    .login-body {
        padding: 30px;
    }

    .input-group {
        position: relative;
        margin-bottom: 24px;
    }

    .input-group i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
        font-size: 1.1rem;
    }

    .form-control {
        padding-left: 45px;
        border-radius: 12px;
        border: 2px solid #e9ecef;
        height: 52px;
        font-size: 1rem;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: #1E3A8A;
        box-shadow: 0 0 0 0.25rem rgba(51, 161, 224, 0.25);
        outline: none;
    }

    .form-label {
        font-weight: 600;
        color: #1E3A8A;
        margin-bottom: 8px;
        display: block;
    }

    .btn-login {
        background: linear-gradient(to right, #1E3A8A, #1E3A8A);
        border: none;
        color: white;
        padding: 14px;
        font-size: 1.1rem;
        font-weight: 600;
        border-radius: 12px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(51, 161, 224, 0.4);
    }

    .btn-login:hover {
        background: linear-gradient(to right, #1e8ac5, #33A1E0);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(51, 161, 224, 0.6);
    }

    .alert {
        border-radius: 12px;
        border: none;
        padding: 15px 20px;
        font-weight: 500;
    }

    .logo-placeholder {
        width: 70px;
        height: 70px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        font-size: 28px;
        color: white;
    }

    @media (max-width: 576px) {
        .login-card {
        border-radius: 16px;
        }
        
        .login-header {
        padding: 25px 15px;
        }
        
        .login-body {
        padding: 25px;
        }
    }

    /* Animation for error message */
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        20%, 60% { transform: translateX(-5px); }
        40%, 80% { transform: translateX(5px); }
    }

    .shake {
        animation: shake 0.5s ease-in-out;
    }
    .text-primary{
        color: #1E3A8A !important;
    }
</style>
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
        <p>Admin Portal</p>
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

        <div class="text-primary mb-3">
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