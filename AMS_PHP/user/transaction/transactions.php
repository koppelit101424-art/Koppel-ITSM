<?php
include '../auth/auth.php';
include '../db/db.php';
include '../../config/config.php';


$user_id = $_SESSION['user_id'];

// Fetch assets issued/borrowed to the logged-in user
$sql = "
    SELECT 
        t.transaction_id,
        t.action,
        t.quantity,
        t.action_date,
        t.date_returned,
        t.remarks,
        i.item_code,
        i.name,
        i.brand,
        i.model,
        i.serial_number
    FROM transaction_tb t
    INNER JOIN item_tb i ON t.item_id = i.item_id
    WHERE t.user_id = ?
    ORDER BY t.action_date DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Assets</title>

<link rel="icon" href="../asset/img/Koppel_bip.ico">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="../asset/css/main.css" rel="stylesheet">
<link href="../asset/css/menu.css" rel="stylesheet">

<style>
.table-hover tbody tr:hover {
    background-color: #f1f1f1;
}
.badge-issue { background-color: #0d6efd; }
.badge-borrow { background-color: #6f42c1; }
.badge-return { background-color: #198754; }
</style>
</head>
<body>

<div class="main-content d-flex" id="mainContent">
    <?php include '../sidebar.php'; ?>

    <div class="content flex-grow-1">
        <?php include '../header.php'; ?>

        <div class="card mt-4">
            <div class="card-header">
                <i class="fas fa-box-open me-2"></i>Issued / Borrowed Assets
            </div>

            <div class="card-body table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Item Code</th>
                            <th>Asset</th>
                            <th>Brand / Model</th>
                            <th>Serial No.</th>
                            <!-- <th>Action</th> -->
                            <th>Qty</th>
                            <th>Date Issued</th>
                            <!-- <th>Date Returned</th> -->
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php $i = 1; while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td><?= htmlspecialchars($row['item_code']) ?></td>
                                    <td><?= htmlspecialchars($row['name']) ?></td>
                                    <td><?= htmlspecialchars($row['brand'] . ' ' . $row['model']) ?></td>
                                    <td><?= htmlspecialchars($row['serial_number']) ?></td>
                                    <!-- <td>
                                        <span class="badge 
                                            <?= $row['action'] == 'issue' ? 'badge-issue' : '' ?>
                                            <?= $row['action'] == 'borrow' ? 'badge-borrow' : '' ?>
                                            <?= $row['action'] == 'return' ? 'badge-return' : '' ?>
                                        ">
                                            <?= ucfirst($row['action']) ?>
                                        </span>
                                    </td> -->
                                    <td><?= $row['quantity'] ?></td>
                                    <td><?= date('m-d-Y', strtotime($row['action_date'])) ?></td>
                                    <!-- <td>
                                        <?= $row['date_returned']
                                            ? date('m-d-Y', strtotime($row['date_returned']))
                                            : '<span class="text-muted">—</span>' ?>
                                    </td> -->
                                    <td><?= htmlspecialchars($row['remarks'] ?? '-') ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" class="text-center">
                                    No assets have been issued to you.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
