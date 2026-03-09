<?php
include 'db.php';

// Fetch users
$sql = "SELECT * FROM user_tb ORDER BY created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Users - Inventory System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- <link href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css" rel="stylesheet"> -->
  <link href="style.css" rel="stylesheet">

  <!-- ✅ DataTables CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
  
</head>
<body>
<div class="container-fluid">
  <div class="row">
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Content -->
    <div class="col-md-10 main">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">👨‍💼 Users</h2>
        <a href="add_user.php" class="btn btn-primary btn-sm">➕ Add New User</a>

      </div>

      <table id="usersTable" class="table table-bordered table-striped table-sm">
        <thead class="table-dark">
          <tr>
            <th>ID</th>
            <th>EMPID</th>
            <th>Name</th>
            <th>Position</th>
            <!-- <th>Email</th> -->
            <th>Department</th>
            <th>Company</th>
            <!-- <th>Area</th> -->
            <th>Hired</th>
            <!-- <th>Resigned</th> -->
           
            <!-- <th>Type</th> -->
          </tr>
        </thead>
        <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
          <?php while($row = $result->fetch_assoc()): ?>
            <tr>
              <td><?= $row['user_id'] ?></td>
              <td><?= $row['emp_id'] ?></td>
              <td><?= $row['fullname'] ?></td>
              <td><?= $row['position'] ?></td>
              <!-- <td><?= $row['email'] ?></td> -->
              <td><?= $row['department'] ?></td>
              <td><?= $row['company'] ?></td>
              <!-- <td><?= $row['area'] ?></td> -->
              <td><?= !empty($row['date_hired']) && $row['date_hired'] != '0000-00-00' ? date('m-d-Y', strtotime($row['date_hired'])) : '' ?></td>
            <!-- <td><?= !empty($row['date_resigned']) && $row['date_resigned'] != '0000-00-00' ? date('m-d-Y', strtotime($row['date_resigned'])) : '' ?></td> -->

              <!-- <td><?= $row['user_type'] ?></td> -->
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="7" class="text-center">No users found</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Bootstrap + DataTables JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function () {
  $('#usersTable').DataTable({
    "pageLength": 8,
    "lengthMenu": [5, 10, 25, 50, 100],
    "order": [[0, "desc"]] // Sort by ID descending
  });
});
</script>
</body>
</html>
<?php $conn->close(); ?>
