<?php
  include 'auth/auth.php';
  include 'db/db.php';
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
  <link href="asset/css/style.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
  <style>
    .custom-menu {
        display: none;
        position: absolute;
        z-index: 1000;
        background-color: #fff;
        border: 1px solid #ccc;
        padding: 5px;
        border-radius: 5px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.2);
    }
    .custom-menu a {
        display: block;
        padding: 5px 10px;
        color: #000;
        text-decoration: none;
    }
    .custom-menu a:hover {
        background-color: #f0f0f0;
    }
    .table-hover:hover {
      background-color: #33A1E0 !important;
      color: white !important;
    }
  </style>
</head>
<body>
  <div class="container-fluid">
    <div class="row">
      <?php include 'sidebar.php'; ?>
      <div class="col-md-10 main">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h3 class="mb-0" style="color: #33A1E0"><i class="fas fa-user-tie me-2" ></i>Users</h3>
          <a href="user/add_user.php" class="btn btn-primary btn-sm" style="background-color: #33A1E0"> Add New User</a>
        </div>
        <?php if (isset($_GET['deleted'])): ?>
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            ✅ User deleted successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endif; ?>
        <table id="usersTable" class="table table-bordered table-striped table-sm table-hover">
         <thead class="table-header-blue">
            <tr>
              <th>ID</th>
              <th>EMPID</th>
              <th>Name</th>
              <th>Position</th>
              <th>Department</th>
              <th>Company</th>
              <th>Hired</th>
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
                <td><?= $row['department'] ?></td>
                <td><?= $row['company'] ?></td>
                <td style="width: 150px;"><?= !empty($row['date_hired']) && $row['date_hired'] != '0000-00-00' ? date('m-d-Y', strtotime($row['date_hired'])) : '' ?></td>
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
  <!-- Context Menu -->
  <div id="contextMenu" class="custom-menu">
      <a href="#" id="editUserLink">Edit</a>
      <a href="#" id="deleteUserLink" class="text-danger">Delete</a>
  </div>

  <!-- Bootstrap + DataTables JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>

  <script>
    $(document).ready(function () {
      $('#usersTable').DataTable({
        "pageLength": 12,
        "lengthMenu": [5, 10, 25, 50, 100],
        "order": [[0, "desc"]] // Sort by ID descending
      });
    });

    // Context menu logic
    const contextMenu = document.getElementById('contextMenu');
    let selectedUserId = null;

    // Right-click on table row
    document.querySelectorAll('#usersTable tbody tr').forEach(row => {
        row.addEventListener('contextmenu', function(e) {
            e.preventDefault();

            selectedUserId = this.querySelector('td:first-child').innerText;

            // Set links dynamically
            document.getElementById('editUserLink').href = 'user/edit_user.php?user_id=' + selectedUserId;
            document.getElementById('deleteUserLink').href = 'delete_user.php?user_id=' + selectedUserId;

            // Show menu
            contextMenu.style.display = 'block';
            contextMenu.style.top = e.pageY + 'px';
            contextMenu.style.left = e.pageX + 'px';
        });
    });

    // Hide menu on click elsewhere
    document.addEventListener('click', function() {
        contextMenu.style.display = 'none';
    });

    // Right-click on table row
    document.querySelectorAll('#usersTable tbody tr').forEach(row => {
        row.addEventListener('contextmenu', function(e) {
            e.preventDefault();

            selectedUserId = this.querySelector('td:first-child').innerText;
            const userName = this.querySelectorAll('td')[2].innerText; // Full Name column

            // Set edit link
            document.getElementById('editUserLink').href = 'user/edit_user.php?user_id=' + selectedUserId;

            // Delete link triggers confirm popup
            document.getElementById('deleteUserLink').onclick = function(e) {
                e.preventDefault(); // Stop default link action
                if (confirm('Are you sure you want to delete ' + userName + '?')) {
                    window.location.href = 'user/delete_user.php?user_id=' + selectedUserId;
                }
                return false; // Prevent action if Cancel
            };

            // Show menu
            contextMenu.style.display = 'block';
            contextMenu.style.top = e.pageY + 'px';
            contextMenu.style.left = e.pageX + 'px';
        });
    });
  </script>
</body>
</html>
<?php $conn->close(); ?>
