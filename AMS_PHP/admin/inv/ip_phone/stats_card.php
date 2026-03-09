<?php
include '../../auth/auth.php';
include '../../db/db.php';
// Fetch counts
// Fetch counts
$itemCount = $conn->query("SELECT COUNT(*) as total FROM item_tb WHERE quantity >= 1")->fetch_assoc()['total'];
$userCount = $conn->query("SELECT COUNT(*) as total FROM user_tb WHERE is_active = 1")->fetch_assoc()['total'];
$transactionCount = $conn->query("SELECT COUNT(*) as total FROM transaction_tb")->fetch_assoc()['total'];
$requestCount = $conn->query("SELECT COUNT(*) as total FROM request_tb WHERE status = 'Pending'")->fetch_assoc()['total'];
$ticketCount = $conn->query("SELECT COUNT(*) as total FROM ticket_tb WHERE status != 'closed' && status != 'resolved' && status != 'canceled'")->fetch_assoc()['total'];

// Growth calculation
$currentMonth = date('Y-m-01');
$lastMonth = date('Y-m-01', strtotime('-1 month'));

$currentTransactions = $conn->query("
    SELECT COUNT(*) as total FROM transaction_tb 
    WHERE action_date >= '$currentMonth'
")->fetch_assoc()['total'];

$lastMonthTransactions = $conn->query("
    SELECT COUNT(*) as total FROM transaction_tb 
    WHERE action_date >= '$lastMonth' AND action_date < '$currentMonth'
")->fetch_assoc()['total'];

$growth = $lastMonthTransactions > 0
    ? round((($currentTransactions - $lastMonthTransactions) / $lastMonthTransactions) * 100, 1)
    : ($currentTransactions > 0 ? 100 : 0);

$growth = max($growth, 0);
?>


<!-- Stats Cards -->
<div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-5 g-3">
    <div class="col">
        <a href="../../inventory.php" class="stats-card-link">
            <div class="card stats-card h-100">
                <i class="fas fa-boxes"></i>
                <h3><?= number_format($itemCount) ?></h3>
                <p>Available Items</p>
            </div>
        </a>
    </div>

    <div class="col">
        <a href="../../users.php" class="stats-card-link">
            <div class="card stats-card h-100">
                <i class="fas fa-users"></i>
                <h3><?= number_format($userCount) ?></h3>
                <p>Active Users</p>
            </div>
        </a>
    </div>

    <div class="col">
        <a href="../../tickets.php" class="stats-card-link">
            <div class="card stats-card h-100">
                <i class="fas fa-ticket"></i>
                <h3><?= number_format($ticketCount) ?></h3>
                <p>Open Tickets</p>
            </div>
        </a>
    </div>

    <div class="col">
        <a href="../../requests.php" class="stats-card-link">
            <div class="card stats-card h-100">
                <i class="fas fa-chart-line"></i>
                <h3><?= number_format($requestCount) ?></h3>
                <p>Pending Requests</p>
            </div>
        </a>
    </div>

    <div class="col">
        <a href="../../transactions.php" class="stats-card-link">
            <div class="card stats-card h-100">
                <i class="fas fa-file-invoice-dollar"></i>
                <h3><?= number_format($transactionCount) ?></h3>
                <p>Transactions</p>
            </div>
        </a>
    </div>
</div><br>
<style>
    .stats-card {
    background: linear-gradient(135deg, #f5f7fa 0%, #e4edf9 100%);
    border: none;
    border-radius: 12px;
    padding: 20px;
    text-align: center;
    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 12px rgba(0,0,0,0.1);
    }
    .stats-card i {
        font-size: 28px;
        color: #33A1E0;
        margin-bottom: 12px;
    }
    .stats-card h3 {
        font-size: 24px;
        font-weight: 700;
        color: #2c3e50;
        margin: 10px 0;
    }
    .stats-card p {
        color: #7f8c8d;
        margin: 0;
        font-size: 14px;
    }
    /* Make cards fully clickable */
.stats-card-link {
    text-decoration: none;
    color: inherit;
    display: block;
    height: 100%;
}

.stats-card-link:hover .stats-card {
    transform: translateY(-5px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.12);
}

/* Ensure card fills container */
.stats-card {
    background: linear-gradient(135deg, #f5f7fa 0%, #e4edf9 100%);
    border: none;
    border-radius: 12px;
    padding: 20px;
    text-align: center;
    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}

.stats-card i {
    font-size: 28px;
    color: #33A1E0;
    margin-bottom: 12px;
}

.stats-card h3 {
    font-size: 24px;
    font-weight: 700;
    color: #2c3e50;
    margin: 10px 0;
}

.stats-card p {
    color: #7f8c8d;
    margin: 0;
    font-size: 14px;
}
</style>