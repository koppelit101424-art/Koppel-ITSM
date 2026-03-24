<?php
include __DIR__ . '/../../includes/auth.php';
include __DIR__ . '/../../includes/db.php';

/* Get unique companies */
$query = $conn->query("
    SELECT DISTINCT company 
    FROM user_tb 
    WHERE company IS NOT NULL AND company != ''
    ORDER BY company DESC
");

/* Company descriptions */
$companyDescriptions = [
    'Koppel Inc' => 'Always Cool',
    'HIMC' => 'Hyatt Industrial and Manufacturing Corporation',
    'HI-AIRE' => 'Hi-Aire Aircon Services',
    'HEEC' => 'Hyatt Escalators and Elevators Corporation'
];
?>

<div class="container-fluid">

<div class="row g-4">

<?php while($row = $query->fetch_assoc()): ?>
<?php 
$company = $row['company'];
$description = $companyDescriptions[$company] ?? 'Company department and operations.';
?>

<div class="col-xl-3 col-lg-4 col-md-6">

<!-- <a href="?company=departments<?= urlencode($company) ?>" class="company-link"> -->
    <a href="index.php?page=organization/departments&company=<?= urlencode($company) ?>" class="company-link">

<div class="card company-card h-100">

<div class="card-body">

<div class="d-flex justify-content-between align-items-start mb-3">

<div class="company-icon">
<i class="bi bi-building"></i>
</div>

<!-- <span class="badge company-badge">
Active
</span> -->

</div>

<h5 class="company-title">
<?= htmlspecialchars($company) ?>
</h5>

<p class="company-desc">
<?= htmlspecialchars($description) ?>
</p>

<hr>

<div class="company-footer">
<small class="text-muted">
Company
</small>
</div>

</div>
</div>
</a>

</div>

<?php endwhile; ?>

</div>
</div>

<style>
    .company-link{
text-decoration:none;
color:inherit;
display:block;
}

.company-card{
border-radius:16px;
border:1px solid #e5e7eb;
background:white;
transition:all .25s ease;
overflow:hidden;
}

.company-card:hover{
transform:translateY(-6px);
box-shadow:0 15px 35px rgba(0,0,0,0.08);
border-color:#dbeafe;
}

.company-icon{
width:50px;
height:50px;
display:flex;
align-items:center;
justify-content:center;
border-radius:14px;
background:linear-gradient(135deg,#4f7cff,#2a5bd7);
color:white;
font-size:20px;
box-shadow:0 6px 15px rgba(79,124,255,0.35);
}

.company-title{
font-weight:600;
font-size:18px;
margin-bottom:6px;
}

.company-desc{
font-size:14px;
color:#6b7280;
line-height:1.5;
min-height:40px;
}

.company-badge{
background:#dcfce7;
color:#15803d;
font-weight:500;
padding:6px 10px;
border-radius:20px;
font-size:12px;
}

.company-footer{
display:flex;
justify-content:space-between;
align-items:center;
}
</style>