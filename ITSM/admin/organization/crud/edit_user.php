<?php
  include __DIR__ . '/../../../includes/auth.php';
  include __DIR__ . '/../../../includes/db.php';

// Get user_id from URL
if (!isset($_GET['user_id'])) {
    // header("Location: users.php");
              echo "<script>
            window.location.href='?page=organization/users';
        </script>";
    exit;
}
$user_id = intval($_GET['user_id']);

// Fetch user data
$sql = "SELECT * FROM user_tb WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "<div class='alert alert-danger'>User not found!</div>";
    exit;
}

// Handle form submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $emp_id     = $_POST['emp_id'];
    $fullname   = $_POST['fullname'];
    $position   = $_POST['position'];
    $email      = $_POST['email'];
    $department = $_POST['department'];
    $company    = $_POST['company'];
    $area       = $_POST['area'];
    $date_hired = !empty($_POST['date_hired']) ? $_POST['date_hired'] : NULL;
    $user_type  = $_POST['user_type'];

    $update_sql = "UPDATE user_tb 
                   SET emp_id=?, fullname=?, position=?, email=?, department=?, company=?, area=?, user_type=?, date_hired=? 
                   WHERE user_id=?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("sssssssssi", $emp_id, $fullname, $position, $email, $department, $company, $area, $user_type, $date_hired, $user_id);

    if ($stmt->execute()) {
        // header("Location: ../users.php?updated=1");
           echo "<script>
            window.location.href='?page=organization/users';
        </script>";
        exit;
    } else {
        echo "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
    }
}
?>
    <!-- Main Content -->
    <div class="main-content" id="mainContent">
      <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center"> 
      <div class="d-flex justify-content-between align-items-center text-white">
        <h2>Update User</h2>
      </div></div>
      <div class="card-body">

        <form method="POST" action="">
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Employee ID</label>
              <input type="text" name="emp_id" class="form-control" value="<?= htmlspecialchars($user['emp_id']) ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Full Name</label>
              <input type="text" name="fullname" class="form-control" value="<?= htmlspecialchars($user['fullname']) ?>" required>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Position</label>
              <input type="text" name="position" class="form-control" value="<?= htmlspecialchars($user['position']) ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Email</label>
              <input type="text" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>">
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-4">
              <label class="form-label">Department</label>
              <input type="text" name="department" class="form-control" value="<?= htmlspecialchars($user['department']) ?>" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Company</label>
              <select name="company" class="form-select" required>
                <option name="" value="Koppel Inc." id="">Koppel Inc.</option>
                <option name="" value="HIMC" id="">HIMC</option>
                <option name="" value="HEEC" id="">HEEC</option>
                <option name="" value="HI-AIRE" id="">HI-AIRE</option>
              </select>
              <!-- <input type="text" name="company" class="form-control" required> -->
            </div>
            <!-- <div class="col-md-4">
              <label class="form-label">Company</label>
              <input type="text" name="company" class="form-control" value="<?= htmlspecialchars($user['company']) ?>" required>
            </div> -->
            <div class="col-md-4">
              <label class="form-label">Area</label>
              <input type="text" name="area" class="form-control" value="<?= htmlspecialchars($user['area']) ?>">
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Date Hired</label>
              <!-- <input type="date" name="date_hired" class="form-control" value="<?= $user['date_hired'] ?>"> -->
               <input type="date" name="date_hired" class="form-control"
            value="<?= !empty($user['date_hired']) ? date('Y-m-d', strtotime($user['date_hired'])) : '' ?>">

            </div>
            <div class="col-md-6">
              <label class="form-label">User Type</label>
              <select name="user_type" class="form-select" required>
                <option value="user" <?= ($user['user_type'] == 'user') ? 'selected' : '' ?>>User</option>
                <option value="admin" <?= ($user['user_type'] == 'admin') ? 'selected' : '' ?>>Admin</option>
              </select>
            </div>
          </div>

          <button type="submit" class="btn btn-primary"> Update User</button>
              <a href="#" onclick="window.history.back(); return false;" class="btn btn-secondary">Back</a>
        </form>
    </div></div>
  </div>
</body>
</html>
<?php $conn->close(); ?>
