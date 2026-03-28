<?php
include __DIR__ . '/../includes/db.php';

// Get item ID from QR
$item_id = $_GET['id'] ?? null;

if (!$item_id) {
    die("Invalid request.");
}

// Query with joins
$sql = "
SELECT 
    i.*, 
    q.qr_code_path,
    c.condition_name,
    
    s.cpu,
    s.ram,
    s.rom,
    s.motherboard,
    s.os,
    s.`key`,
    s.antivirus,
    s.comp_name

FROM qr_tb q
JOIN item_tb i ON i.item_id = q.item_id
LEFT JOIN item_condition_tb c ON i.condition_id = c.condition_id
LEFT JOIN laptop_pc_specs s ON s.item_id = i.item_id

WHERE q.item_id = ?
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param("i", $item_id); // item_id is INT
$stmt->execute();

$result = $stmt->get_result();
$item = $result->fetch_assoc();

if (!$item) {
    die("Item not found.");
}

// Condition badge color
$condition = strtolower($item['condition_name'] ?? '');
$badgeClass = 'bg-secondary';

if ($condition == 'good') $badgeClass = 'bg-success';
elseif ($condition == 'damaged') $badgeClass = 'bg-danger';
elseif ($condition == 'maintenance') $badgeClass = 'bg-warning';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Item Details</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<!-- <link rel="stylesheet" href="../assets/css/bootstrap.min.css"> -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../assets/css/main.css">

<style>
body {
    background: #eef2f7;
    font-family: 'Segoe UI', sans-serif;
}

.page-wrapper {
    max-width: 1200px;
    margin: 50px auto;
}

.page-header {
    background: linear-gradient(135deg, #4e73df, #224abe);
    color: white;
    padding: 20px 30px;
    border-radius: 12px 12px 0 0;
}

.page-header h5 {
    font-size: 22px;
}

.page-content {
    background: #fff;
    padding: 30px;
    border-radius: 0 0 12px 12px;
    box-shadow: 0 12px 30px rgba(0,0,0,0.08);
}

.detail-card {
    background: #f9fafc;
    border: 1px solid #e3e6f0;
    transition: 0.25s;
}

.detail-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 18px rgba(0,0,0,0.08);
}

label {
    font-size: 13px;
    color: #858796;
}

p {
    font-size: 16px;
    font-weight: 600;
    color: #2e2e2e;
    margin-bottom: 12px;
}

h6 {
    font-size: 16px;
}

.badge {
    font-size: 13px;
    padding: 6px 10px;
    border-radius: 8px;
}

.text-primary-value {
    color: #4e73df;
}
</style>
</head>

<body>

<div class="page-wrapper">

    <!-- HEADER -->
    <div class="page-header">
        <h5 class="fw-bold mb-0">
            <i class="fas fa-info-circle me-2"></i>Item Details
        </h5>
    </div>

    <!-- CONTENT -->
    <div class="page-content">

        <div class="row g-4">

            <!-- BASIC INFO -->
            <div class="col-md-6">
                <div class="detail-card p-4 rounded-3">
                    <h6 class="text-primary fw-bold border-bottom pb-2 mb-3">
                        <i class="fas fa-tag me-2"></i>Basic Information
                    </h6>

                    <label>Code</label>
                    <p><?= htmlspecialchars($item['item_code']) ?></p>

                    <label>Item Name</label>
                    <p><?= htmlspecialchars($item['name']) ?></p>

                    <label>Brand</label>
                    <p><?= htmlspecialchars($item['brand']) ?></p>

                    <label>Model</label>
                    <p><?= htmlspecialchars($item['model']) ?></p>
                </div>
            </div>

            <!-- INVENTORY -->
            <div class="col-md-6">
                <div class="detail-card p-4 rounded-3">
                    <h6 class="text-primary fw-bold border-bottom pb-2 mb-3">
                        <i class="fas fa-cube me-2"></i>Inventory Details
                    </h6>

                    <label>Serial Number</label>
                    <p><?= htmlspecialchars($item['serial_number']) ?></p>

                    <label>Quantity</label>
                    <p><?= htmlspecialchars($item['quantity']) ?></p>

                    <label>Date Received</label>
                    <p><?= htmlspecialchars($item['date_received']) ?></p>

                    <label>Condition</label><br>
                    <span class="badge <?= $badgeClass ?>">
                        <?= htmlspecialchars($item['condition_name'] ?? 'N/A') ?>
                    </span>
                </div>
            </div>

            <!-- SPECS (ONLY IF LAPTOP) -->
            <?php if (!empty($item['cpu']) || !empty($item['ram'])): ?>
            <div class="col-12">
                <div class="detail-card p-4 rounded-3">
                    <h6 class="text-primary fw-bold border-bottom pb-2 mb-3">
                        <i class="fas fa-desktop me-2"></i>Laptop / PC Specs
                    </h6>

                    <div class="row">

                        <div class="col-md-3">
                            <label><i class="fas fa-microchip me-1"></i>CPU</label>
                            <p class="text-primary-value"><?= htmlspecialchars($item['cpu']) ?></p>
                        </div>

                        <div class="col-md-3">
                            <label><i class="fas fa-memory me-1"></i>RAM</label>
                            <p class="text-primary-value"><?= htmlspecialchars($item['ram']) ?></p>
                        </div>

                        <div class="col-md-3">
                            <label><i class="fas fa-hdd me-1"></i>Storage</label>
                            <p class="text-primary-value"><?= htmlspecialchars($item['rom']) ?></p>
                        </div>

                        <div class="col-md-3">
                            <label>Motherboard</label>
                            <p><?= htmlspecialchars($item['motherboard']) ?></p>
                        </div>

                        <div class="col-md-3">
                            <label>Operating System</label>
                            <p><?= htmlspecialchars($item['os']) ?></p>
                        </div>

                        <div class="col-md-3">
                            <label>OS Key</label>
                            <p><?= htmlspecialchars($item['key']) ?></p>
                        </div>

                        <div class="col-md-3">
                            <label>Antivirus</label>
                            <p><?= htmlspecialchars($item['antivirus']) ?></p>
                        </div>

                        <div class="col-md-3">
                            <label>Computer Name</label>
                            <p><?= htmlspecialchars($item['comp_name']) ?></p>
                        </div>

                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- DESCRIPTION -->
            <div class="col-12">
                <div class="detail-card p-4 rounded-3">
                    <h6 class="text-primary fw-bold border-bottom pb-2 mb-3">
                        <i class="fas fa-align-left me-2"></i>Description
                    </h6>
                    <p style="text-align: justify;">
                        <?= htmlspecialchars($item['description']) ?>
                    </p>
                </div>
            </div>

        </div>

    </div>
</div>

</body>
</html>