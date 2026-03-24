<?php
include '../../auth/auth.php';
include '../../db/db.php';

// Fetch all credentials, newest first
$sql = "SELECT * FROM credential_tb ORDER BY cred_id DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Print Credentials</title>
  <style>
    body { 
        font-family: Arial, sans-serif; 
        font-size: 11px; /* smaller font */
    }
    table { 
        border-collapse: collapse; 
        width: 100%; 
        table-layout: fixed; /* fixed layout for wrapping */
    }
    th, td { 
        border: 1px solid #000; 
        padding: 4px; 
        text-align: left; 
        word-wrap: break-word; 
        overflow-wrap: break-word;
    }
    th { 
        background-color: #eee; 
    }
    th.system { width: 10%; }
    th.description { width: 15%; }
    th.url { width: 15%; }
    th.username { width: 10%; }
    th.password { width: 10%; }
    th.recovery { width: 15%; }
    th.remarks { width: 15%; }
    th.created { width: 10%; }
    th.updated { width: 10%; }
    th.updatedby { width: 10%; }
    @media print {
        button { display: none; }
    }
  </style>
</head>
<body>

<h2>Credentials List</h2>

<table>
  <thead>
    <tr>
      <th class="system">System</th>
      <th class="description">Description</th>
      <th class="url">URL</th>
      <th class="username">Username</th>
      <th class="password">Password</th>
      <th class="recovery">Recovery Email</th>
      <th class="remarks">Remarks</th>
      <th class="created">Date Created</th>
      <th class="updated">Last Updated</th>
      <th class="updatedby">Updated By</th>
    </tr>
  </thead>
  <tbody>
    <?php if ($result->num_rows > 0): ?>
      <?php while($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($row['system']) ?></td>
          <td><?= htmlspecialchars($row['description']) ?></td>
          <td><a href="<?= htmlspecialchars($row['url_link']) ?>" target="_blank"><?= htmlspecialchars($row['url_link']) ?></a></td>
          <td><?= htmlspecialchars($row['username']) ?></td>
          <td><?= htmlspecialchars($row['password']) ?></td>
          <td><?= htmlspecialchars($row['recovery_email']) ?></td>
          <td><?= htmlspecialchars($row['remarks']) ?></td>
          <td><?= date('m-d-Y', strtotime($row['date_created'])) ?></td>
          <td><?= date('m-d-Y', strtotime($row['date_updated'])) ?></td>
          <td><?= htmlspecialchars($row['updated_by']) ?></td>
        </tr>
      <?php endwhile; ?>
    <?php else: ?>
      <tr><td colspan="10" class="text-center">No credentials found</td></tr>
    <?php endif; ?>
  </tbody>
</table>

<script>
  // Auto print on page load
  window.onload = function() {
    window.print();
  };
</script>

</body>
</html>

<?php $conn->close(); ?>