<?php
  include '../../auth/auth.php';
  include '../../db/db.php';
  
  $message = '';
  $edit_mode = false;
  $cred = null;
  
  // Handle form submission (ADD/EDIT)
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $system = $_POST['system'] ?? '';
      $description = $_POST['description'] ?? '';
      $username = $_POST['username'] ?? '';
      $password = $_POST['password'] ?? '';
      $recovery_email = $_POST['recovery_email'] ?? '';
      $url_link = $_POST['url_link'] ?? '';
      $updated_by = $_SESSION['user_id'] ?? 1;
      $date_updated = date('Y-m-d H:i:s');
      
      if (isset($_POST['cred_id']) && !empty($_POST['cred_id'])) {
          // EDIT existing credential
          $cred_id = $_POST['cred_id'];
          $stmt = $conn->prepare("UPDATE credential_tb SET system=?, description=?, username=?, password=?, recovery_email=?, url_link=?, date_updated=?, updated_by=? WHERE cred_id=?");
          $stmt->bind_param("sssssssii", $system, $description, $username, $password, $recovery_email, $url_link, $date_updated, $updated_by, $cred_id);
          if ($stmt->execute()) {
              $message = '<div class="alert alert-success">Credential updated successfully!</div>';
          } else {
              $message = '<div class="alert alert-danger">Error updating: ' . $stmt->error . '</div>';
          }
          $stmt->close();
      } else {
          // ADD new credential
          $stmt = $conn->prepare("INSERT INTO credential_tb (system, description, username, password, recovery_email, url_link, date_updated, updated_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
          $stmt->bind_param("sssssssi", $system, $description, $username, $password, $recovery_email, $url_link, $date_updated, $updated_by);
          if ($stmt->execute()) {
              $message = '<div class="alert alert-success">Credential added successfully!</div>';
          } else {
              $message = '<div class="alert alert-danger">Error adding: ' . $stmt->error . '</div>';
          }
          $stmt->close();
      }
  }
  
  // Load existing data if editing
  if (isset($_GET['cred_id']) && !empty($_GET['cred_id'])) {
      $edit_mode = true;
      $cred_id = intval($_GET['cred_id']);
      $stmt = $conn->prepare("SELECT * FROM credential_tb WHERE cred_id = ?");
      $stmt->bind_param("i", $cred_id);
      $stmt->execute();
      $result = $stmt->get_result();
      if ($result->num_rows > 0) {
          $cred = $result->fetch_assoc();
      }
      $stmt->close();
  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $edit_mode ? 'Edit' : 'Add' ?> Credential</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="../../asset/css/main.css" rel="stylesheet">
</head>
<body>
  <?php include '../sidebar.php'; ?>
  
  <!-- Main Content -->
  <div class="main-content" id="mainContent">
      <?php include '../../header2.php'; ?>
      
      <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
              <h4><?= $edit_mode ? 'Edit' : 'Add New' ?> Credential</h4>
          </div>
          <div class="card-body">
              
              <?= $message ?>
              
              <form method="POST" class="mt-3">
                  <?php if ($edit_mode && $cred): ?>
                      <input type="hidden" name="cred_id" value="<?= $cred['cred_id'] ?>">
                  <?php endif; ?>
                  
                  <div class="row mb-3">
                      <div class="col-md-6">
                          <label class="form-label">System Name <span class="text-danger">*</span></label>
                          <input type="text" name="system" class="form-control" 
                                 value="<?= htmlspecialchars($cred['system'] ?? '') ?>" required 
                                 placeholder="e.g., Active Directory, Email Server">
                      </div>
                      <div class="col-md-6">
                          <label class="form-label">Description</label>
                          <input type="text" name="description" class="form-control" 
                                 value="<?= htmlspecialchars($cred['description'] ?? '') ?>" 
                                 placeholder="Brief description">
                      </div>
                  </div>

                  <div class="row mb-3">
                      <div class="col-md-6">
                          <label class="form-label">Username <span class="text-danger">*</span></label>
                          <input type="text" name="username" class="form-control" 
                                 value="<?= htmlspecialchars($cred['username'] ?? '') ?>" required>
                      </div>
                      <div class="col-md-6">
                          <label class="form-label">Password <span class="text-danger">*</span></label>
                          <div class="input-group">
                              <input type="text" name="password" class="form-control" 
                                     value="<?= htmlspecialchars($cred['password'] ?? '') ?>" required>
                              <button class="btn btn-outline-secondary" type="button" onclick="togglePassword(this)">
                                  <i class="fas fa-eye"></i>
                              </button>
                          </div>
                      </div>
                  </div>

                  <div class="row mb-3">
                      <div class="col-md-6">
                          <label class="form-label">Recovery Email</label>
                          <input type="text" name="recovery_email" class="form-control" 
                                 value="<?= htmlspecialchars($cred['recovery_email'] ?? '') ?>" 
                                 >
                      </div>
                      <div class="col-md-6">
                          <label class="form-label">URL Link</label>
                          <input type="text" name="url_link" class="form-control" 
                                 value="<?= htmlspecialchars($cred['url_link'] ?? '') ?>" 
                                 placeholder="">
                      </div>
                  </div>

                  <div class="mb-3">
                      <label class="form-label text-muted small">
                          <i class="fas fa-info-circle me-1"></i>
                          This entry will be logged as updated by: <strong><?= htmlspecialchars($_SESSION['fullname'] ?? $_SESSION['user_name'] ?? 'Current User') ?></strong>
                      </label>
                  </div>

                  <button type="submit" class="btn btn-primary" style="background-color: #33A1E0">
                      <i class="fas fa-save me-1"></i>Save Credential
                  </button>
                  <a href="credential.php" class="btn btn-secondary">
                      <i class="fas fa-arrow-left me-1"></i>Back to List
                  </a>
              </form>
          </div>
      </div>
  </div>
  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
      // Toggle password visibility
      function togglePassword(btn) {
          const input = btn.parentElement.querySelector('input[name="password"]');
          const icon = btn.querySelector('i');
          
          if (input.type === "password") {
              input.type = "text";
              icon.classList.remove("fa-eye");
              icon.classList.add("fa-eye-slash");
          } else {
              input.type = "password";
              icon.classList.remove("fa-eye-slash");
              icon.classList.add("fa-eye");
          }
      }
  </script>
</body>
</html>