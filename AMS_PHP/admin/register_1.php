<?php
session_start();
include 'db/db.php';

$message = "";
$success = false;

// Handle registration form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = trim($_POST['fullname']);
    $emp_id = trim($_POST['emp_id']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Basic validation
    if (empty($fullname) || empty($emp_id) || empty($email) || empty($password)) {
        $message = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $message = "Passwords do not match.";
    } elseif (strlen($password) < 8) {
        $message = "Password must be at least 8 characters long.";
    } else {
        // Check if employee ID already exists
        $check_emp = $conn->prepare("SELECT user_id FROM user_tb WHERE emp_id = ?");
        $check_emp->bind_param("s", $emp_id);
        $check_emp->execute();
        $emp_result = $check_emp->get_result();
        
        if ($emp_result->num_rows > 0) {
            $message = "Employee ID already exists.";
        } else {
            // Check if email already exists
            $check_email = $conn->prepare("SELECT user_id FROM user_tb WHERE email = ?");
            $check_email->bind_param("s", $email);
            $check_email->execute();
            $email_result = $check_email->get_result();
            
            if ($email_result->num_rows > 0) {
                $message = "Email address already exists.";
            } else {
                // Hash the password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Get additional fields with defaults
                $position = !empty($_POST['position']) ? trim($_POST['position']) : 'Administrator';
                $department = !empty($_POST['department']) ? trim($_POST['department']) : 'IT Department';
                $company = !empty($_POST['company']) ? trim($_POST['company']) : 'Company Name';
                $area = !empty($_POST['area']) ? trim($_POST['area']) : 'Head Office';
                $user_type = 'admin'; // Default to admin
                $date_hired = !empty($_POST['date_hired']) ? $_POST['date_hired'] : date('Y-m-d');
                $date_resigned = NULL; // NULL for active users
                $created_at = date('Y-m-d H:i:s');
                
                // Insert new user
                $sql = "INSERT INTO user_tb (emp_id, fullname, position, email, department, company, area, user_type, date_hired, date_resigned, created_at, password) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssssssssss", 
                    $emp_id, 
                    $fullname, 
                    $position, 
                    $email, 
                    $department, 
                    $company, 
                    $area, 
                    $user_type, 
                    $date_hired, 
                    $date_resigned, 
                    $created_at, 
                    $hashed_password
                );
                
                if ($stmt->execute()) {
                    $success = true;
                    $message = "Account created successfully! You can now login.";
                } else {
                    $message = "Error creating account: " . $conn->error;
                }
                $stmt->close();
            }
            $check_email->close();
        }
        $check_emp->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register - Asset Management System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="asset/css/register.css">
</head>
<body>
  <div class="register-container">
    <div class="register-card">
      <div class="register-header">
        <div class="logo-placeholder">
          <i class="fas fa-user-plus"></i>
        </div>
        <h1>Asset Management System</h1>
        <p>Create Admin Account</p>
      </div>
      
      <div class="register-body">
        <?php if ($message): ?>
          <div class="alert <?php echo $success ? 'alert-success' : 'alert-danger'; ?> shake">
            <i class="fas fa-<?php echo $success ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
            <?php echo htmlspecialchars($message); ?>
          </div>
        <?php endif; ?>

        <form method="POST">
          <!-- Basic Information Section -->
          <div class="form-section">
            <h5><i class="fas fa-user me-2"></i>Basic Information</h5>
            
            <div class="mb-3">
              <label for="fullname" class="form-label">Full Name *</label>
              <div class="input-group">
                <i class="fas fa-user-tag"></i>
                <input 
                  type="text" 
                  name="fullname" 
                  id="fullname"
                  class="form-control" 
                  placeholder="Enter your full name"
                  value="<?php echo isset($_POST['fullname']) ? htmlspecialchars($_POST['fullname']) : ''; ?>"
                  required
                >
              </div>
            </div>

            <div class="mb-3">
              <label for="emp_id" class="form-label">Employee ID *</label>
              <div class="input-group">
                <i class="fas fa-id-badge"></i>
                <input 
                  type="text" 
                  name="emp_id" 
                  id="emp_id"
                  class="form-control" 
                  placeholder="Enter your employee ID"
                  value="<?php echo isset($_POST['emp_id']) ? htmlspecialchars($_POST['emp_id']) : ''; ?>"
                  required
                >
              </div>
            </div>

            <div class="mb-3">
              <label for="email" class="form-label">Email Address *</label>
              <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input 
                  type="email" 
                  name="email" 
                  id="email"
                  class="form-control" 
                  placeholder="Enter your email address"
                  value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                  required
                >
              </div>
            </div>
          </div>

          <!-- Organization Details Section -->
          <div class="form-section">
            <h5><i class="fas fa-building me-2"></i>Organization Details</h5>
            
            <div class="mb-3">
              <label for="position" class="form-label">Position</label>
              <div class="input-group">
                <i class="fas fa-briefcase"></i>
                <input 
                  type="text" 
                  name="position" 
                  id="position"
                  class="form-control" 
                  placeholder="Enter your position"
                  value="<?php echo isset($_POST['position']) ? htmlspecialchars($_POST['position']) : 'Administrator'; ?>"
                >
              </div>
            </div>

            <div class="mb-3">
              <label for="department" class="form-label">Department</label>
              <div class="input-group">
                <i class="fas fa-door-open"></i>
                <input 
                  type="text" 
                  name="department" 
                  id="department"
                  class="form-control" 
                  placeholder="Enter your department"
                  value="<?php echo isset($_POST['department']) ? htmlspecialchars($_POST['department']) : 'IT Department'; ?>"
                >
              </div>
            </div>

            <div class="mb-3">
              <label for="company" class="form-label">Company</label>
              <div class="input-group">
                <i class="fas fa-building"></i>
                <select name="company" class="form-select" required>
                  <option name="" selected>Select Company</option>
                  <option name="" value="Koppel Inc." id="">Koppel Inc.</option>
                  <option name="" value="HIMC" id="">HIMC</option>
                  <option name="" value="HEEC" id="">HEEC</option>
                  <option name="" value="HI-AIRE" id="">HI-AIRE</option>
                </select >
                <!-- <input 
                  type="text" 
                  name="company" 
                  id="company"
                  class="form-control" 
                  placeholder="Enter company name"
                  value="<?php echo isset($_POST['company']) ? htmlspecialchars($_POST['company']) : 'Company Name'; ?>"
                > -->
              </div>
            </div>

            <div class="mb-3">
              <label for="area" class="form-label">Area/Location</label>
              <div class="input-group">
                <i class="fas fa-map-marker-alt"></i>
                <input 
                  type="text" 
                  name="area" 
                  id="area"
                  class="form-control" 
                  placeholder="Enter area or location"
                  value="<?php echo isset($_POST['area']) ? htmlspecialchars($_POST['area']) : 'Mandaluyong'; ?>"
                >
              </div>
            </div>

            <div class="mb-3">
              <label for="date_hired" class="form-label">Date Hired</label>
              <div class="input-group">
                <i class="fas fa-calendar-check"></i>
                <input 
                  type="date" 
                  name="date_hired" 
                  id="date_hired"
                  class="form-control" 
                  value="<?php echo isset($_POST['date_hired']) ? htmlspecialchars($_POST['date_hired']) : date('Y-m-d'); ?>"
                >
              </div>
            </div>
          </div>

          <!-- Security Section -->
          <div class="form-section">
            <h5><i class="fas fa-lock me-2"></i>Security</h5>
            
            <div class="mb-3">
              <label for="password" class="form-label">Password *</label>
              <div class="input-group">
                <i class="fas fa-lock"></i>
                <input 
                  type="password" 
                  name="password" 
                  id="password"
                  class="form-control" 
                  placeholder="Create a strong password"
                  required
                >
              </div>
              <div class="password-strength">
                <div id="strengthIndicator"></div>
              </div>
              <small class="text-muted">Password must be at least 8 characters long</small>
            </div>

            <div class="mb-3">
              <label for="confirm_password" class="form-label">Confirm Password *</label>
              <div class="input-group">
                <i class="fas fa-lock"></i>
                <input 
                  type="password" 
                  name="confirm_password" 
                  id="confirm_password"
                  class="form-control" 
                  placeholder="Confirm your password"
                  required
                >
              </div>
            </div>
          </div>

          <button type="submit" class="btn btn-register w-100">
            <i class="fas fa-user-plus me-2"></i>Create Admin Account
          </button>
        </form>

        <div class="login-link">
          <p>Already have an account? <a href="login.php"><i class="fas fa-sign-in-alt"></i> Sign In</a></p>
        </div>
      </div>
    </div>
    
    <div class="text-center text-white mt-4 opacity-75">
      <small>© <?php echo date('Y'); ?> Asset Management System. All rights reserved.</small>
    </div>
  </div>

  <script>
    // Password strength indicator
    document.getElementById('password').addEventListener('input', function() {
      const password = this.value;
      const strengthIndicator = document.getElementById('strengthIndicator');
      let strength = 0;
      let color = '#e9ecef';
      
      if (password.length >= 8) strength += 25;
      if (/[A-Z]/.test(password)) strength += 25;
      if (/[0-9]/.test(password)) strength += 25;
      if (/[^A-Za-z0-9]/.test(password)) strength += 25;
      
      if (strength >= 75) {
        color = '#28a745';
      } else if (strength >= 50) {
        color = '#ffc107';
      } else if (strength >= 25) {
        color = '#fd7e14';
      } else if (strength > 0) {
        color = '#dc3545';
      }
      
      strengthIndicator.style.width = strength + '%';
      strengthIndicator.style.backgroundColor = color;
    });
    
    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
      const password = document.getElementById('password').value;
      const confirmPassword = document.getElementById('confirm_password').value;
      
      if (password !== confirmPassword) {
        alert('Passwords do not match!');
        e.preventDefault();
        return false;
      }
      
      if (password.length < 8) {
        alert('Password must be at least 8 characters long!');
        e.preventDefault();
        return false;
      }
    });
    
    // Add shake animation to error messages
    document.addEventListener('DOMContentLoaded', function() {
      const alert = document.querySelector('.alert');
      if (alert) {
        alert.classList.add('shake');
      }
    });
  </script>
</body>
</html>