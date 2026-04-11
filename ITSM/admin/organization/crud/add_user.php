<?php
  include __DIR__ . '/../../../includes/auth.php';
  include __DIR__ . '/../../../includes/db.php';

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
    $password   = password_hash("passw0rd", PASSWORD_DEFAULT); // auto default password

    $check = $conn->prepare("SELECT user_id FROM user_tb WHERE emp_id = ?");
    $check->bind_param("s", $emp_id);
    $check->execute();
    $check->store_result();

    if($check->num_rows > 0){
        echo "<div class='alert alert-danger'>Employee ID already exists.</div>";
        exit;
    }
    $sql = "INSERT INTO user_tb 
    (emp_id, fullname, position, email, department, company, area, user_type, password, date_hired, created_at) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ssssssssss",
        $emp_id,
        $fullname,
        $position,
        $email,
        $department,
        $company,
        $area,
        $user_type,
        $password,
        $date_hired
    );

    if ($stmt->execute()) {
        // header("Location: ../users.php?success=1");
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
        <h2> Add New User</h2>
      </div></div>
      <div class="card-body">
        <form method="POST" action="">
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Employee ID</label>
                <input type="text" name="emp_id" id="emp_id" class="form-control" required>
              <small id="empid_msg"></small>
            </div>
            <div class="col-md-6">
              <label class="form-label">Full Name</label>
              <input type="text" name="fullname" class="form-control" required>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Position</label>
              <input type="text" name="position" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Email</label>
              <input type="text" name="email" class="form-control">
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-4">
              <label class="form-label">Department</label>
              <input type="text" name="department" class="form-control" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Company</label>
              <select name="company" class="form-select" required>
                <option name="" value="Koppel Inc." id="">Koppel, Inc.</option>
                <option name="" value="HIMC" id="">HIMC</option>
                <option name="" value="HEEC" id="">HEEC</option>
                <option name="" value="HI-AIRE" id="">HI-AIRE</option>
              </select>
              <!-- <input type="text" name="company" class="form-control" required> -->
            </div>
            <div class="col-md-4">
              <label class="form-label">Area</label>
              <input type="text" name="area" class="form-control">
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Date Hired</label>
              <input type="date" name="date_hired" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label">User Type</label>
              <select name="user_type" class="form-select" required>
                <option value="user">User</option>
                <option value="agent">Agent</option>
                <option value="admin">Admin</option>
                <option value="manager">Manager</option>
              </select>
            </div>
          </div>
              <input type="hidden" name="password" class="form-control" value="passw0rd">
          <button type="submit" id="saveBtn" class="btn btn-primary">Save User</button>
        <a href="#" onclick="window.history.back(); return false;" class="btn btn-secondary">Back</a>
         
        </form>
    </div>
  </div></div>

<script>
  const empInput = document.getElementById("emp_id");
const msg = document.getElementById("empid_msg");
const saveBtn = document.getElementById("saveBtn");

let timer;

empInput.addEventListener("input", function(){

    clearTimeout(timer);

    timer = setTimeout(() => {

        const emp_id = empInput.value;

        if(emp_id.length === 0){
            msg.innerHTML = "";
            saveBtn.disabled = false;
            return;
        }

        fetch("./organization/crud/check_empid.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: "emp_id=" + encodeURIComponent(emp_id)
        })
        .then(res => res.text())
        .then(data => {

            if(data.trim() === "taken"){

                msg.innerHTML = "<span style='color:red'>Employee ID already taken</span>";
                saveBtn.disabled = true;

            } else {

                msg.innerHTML = "<span style='color:green'>Employee ID available</span>";
                saveBtn.disabled = false;

            }

        });

    }, 400);

});
</script>
</body>
</html>
<?php $conn->close(); ?>
