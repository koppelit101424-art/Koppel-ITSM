```html
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
:root {
    --light-blue: #e3f2fd;
    --medium-blue: #bbdefb;
    --dark-blue: #90caf9;
    --accent-blue: #64b5f6;
    --text-blue: #1976d2;
}

body {
    background-color: #f8f9fa;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.sidebar {
    background: linear-gradient(135deg, var(--light-blue), var(--medium-blue));
    min-height: 100vh;
    box-shadow: 0 0 15px rgba(0,0,0,0.1);
    position: fixed;
    top: 0;
    left: 0;
    z-index: 1000;
    transition: all 0.3s ease;
}

.sidebar.collapsed {
    width: 70px !important;
    overflow: hidden;
}

.sidebar.collapsed .sidebar-text {
    display: none;
}

.sidebar.collapsed .sidebar-title {
    display: none;
}

.main-content {
    margin-left: 250px;
    padding: 20px;
    transition: margin-left 0.3s ease;
}

.main-content.sidebar-collapsed {
    margin-left: 70px;
}

.navbar-brand {
    font-weight: 700;
    color: var(--text-blue) !important;
    font-size: 1.5rem;
}

.sidebar a {
    color: var(--text-blue) !important;
    font-weight: 500;
    margin: 8px 0;
    padding: 12px 20px;
    border-radius: 8px;
    text-decoration: none;
    display: flex;
    align-items: center;
    transition: all 0.3s ease;
}

.sidebar a:hover, .sidebar a.active {
    background-color: var(--accent-blue);
    color: white !important;
    transform: translateX(5px);
}

.sidebar a i {
    margin-right: 15px;
    font-size: 1.1rem;
    min-width: 24px;
    text-align: center;
}

.sidebar h4 {
    padding: 20px 15px 15px 15px;
    margin-bottom: 20px;
    color: var(--text-blue);
    font-weight: 600;
}

.card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    margin-bottom: 25px;
    background: white;
    transition: transform 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
}

.card-header {
    background: linear-gradient(135deg, var(--light-blue), var(--medium-blue));
    border-bottom: none;
    padding: 20px;
    border-radius: 15px 15px 0 0 !important;
    color: var(--text-blue);
    font-weight: 600;
    font-size: 1.2rem;
}

.table th {
    background-color: var(--light-blue);
    color: var(--text-blue);
    font-weight: 600;
    border: none;
}

.table td {
    vertical-align: middle;
    border: none;
}

.table-hover tbody tr:hover {
    background-color: rgba(144, 202, 249, 0.1);
}

.btn-primary {
    background-color: var(--accent-blue);
    border: none;
    padding: 8px 16px;
    border-radius: 6px;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    background-color: var(--text-blue);
    transform: translateY(-2px);
}

.status-active {
    background-color: #e8f5e9;
    color: #2e7d32;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 500;
}

.status-inactive {
    background-color: #ffebee;
    color: #c62828;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 500;
}

.avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--accent-blue), var(--dark-blue));
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 1rem;
}

.search-box {
    background-color: white;
    border-radius: 25px;
    padding: 8px 15px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.search-box input {
    border: none;
    outline: none;
    width: 250px;
}

.stats-card {
    text-align: center;
    padding: 20px;
}

.stats-card i {
    font-size: 2.5rem;
    color: var(--accent-blue);
    margin-bottom: 15px;
}

.stats-card h3 {
    color: var(--text-blue);
    margin: 10px 0;
    font-size: 1.8rem;
    font-weight: 700;
}

.stats-card p {
    color: #666;
    margin: 0;
    font-size: 0.9rem;
}

.toggle-btn {
    position: absolute;
    top: 15px;
    right: -30px;
    background: var(--accent-blue);
    color: white;
    border: none;
    border-radius: 0 8px 8px 0;
    padding: 8px 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    z-index: 1001;
}

.toggle-btn:hover {
    background: var(--text-blue);
}

@media (max-width: 992px) {
    .sidebar {
        width: 220px;
    }
    .main-content {
        margin-left: 220px;
    }
}

@media (max-width: 768px) {
    .sidebar {
        width: 70px;
        overflow: hidden;
    }
    .sidebar .sidebar-text {
        display: none;
    }
    .sidebar .sidebar-title {
        display: none;
    }
    .main-content {
        margin-left: 70px;
    }
    .search-box input {
        width: 150px;
    }
}

@media (max-width: 576px) {
    .main-content {
        margin-left: 0;
        padding: 15px;
    }
    .search-box {
        display: none;
    }
    .table-responsive {
        font-size: 0.85rem;
    }
    .sidebar {
        width: 0 !important;
        opacity: 0;
        pointer-events: none;
    }
    .main-content.sidebar-collapsed {
        margin-left: 0;
    }
    .toggle-btn {
        right: 15px;
        border-radius: 8px;
    }
}
</style>
</head>
<body>
<!-- Sidebar -->
<div class="sidebar col-md-2" id="sidebar" style="height: 100vh; width: 250px;">
<button class="toggle-btn" id="toggleSidebar">
    <i class="fas fa-chevron-left"></i>
</button>
<h4 class="text-white px-3 sidebar-title">Inventory</h4>
<a href="index.php" class="active">
    <i class="fas fa-boxes"></i>
    <span class="sidebar-text">Inventory</span>
</a>
<a href="users.php">
    <i class="fas fa-user-tie"></i>
    <span class="sidebar-text">Users</span>
</a>
<a href="transactions.php">
    <i class="fas fa-file-invoice-dollar"></i>
    <span class="sidebar-text">Transactions</span>
</a>
<a href="#">
    <i class="fas fa-chart-line"></i>
    <span class="sidebar-text">Reports</span>
</a>
<a href="#">
    <i class="fas fa-cog"></i>
    <span class="sidebar-text">Settings</span>
</a>
<a href="logout.php" onclick="return confirm('Are you sure you want to log out?');">
    <i class="fas fa-right-from-bracket" style="color:white;"></i>
    <span class="sidebar-text">Logout</span>
</a>
</div>

<!-- Main Content -->
<div class="main-content" id="mainContent">
<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="text-primary">Dashboard</h2>
    <div class="d-flex align-items-center">
        <div class="search-box me-3">
            <i class="fas fa-search text-muted"></i>
            <input type="text" placeholder="Search..." class="form-control">
        </div>
        <div class="avatar">JD</div>
    </div>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stats-card">
            <i class="fas fa-boxes"></i>
            <h3>1,234</h3>
            <p>Inventory Items</p>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stats-card">
            <i class="fas fa-users"></i>
            <h3>567</h3>
            <p>Users</p>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stats-card">
            <i class="fas fa-file-invoice-dollar"></i>
            <h3>89</h3>
            <p>Transactions</p>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stats-card">
            <i class="fas fa-chart-line"></i>
            <h3>89%</h3>
            <p>Growth</p>
        </div>
    </div>
</div>

<!-- Users Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Inventory Management</span>
        <button class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> Add Item
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Category</th>
                        <th>Stock</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Last Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar me-3 bg-primary">L</div>
                                <div>
                                    <div class="fw-bold">Laptop Pro</div>
                                    <div class="text-muted small">SKU: LAP-001</div>
                                </div>
                            </div>
                        </td>
                        <td>Electronics</td>
                        <td>25</td>
                        <td>$1,299.99</td>
                        <td><span class="status-active">In Stock</span></td>
                        <td>Jan 15, 2024</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary me-2">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar me-3 bg-success">M</div>
                                <div>
                                    <div class="fw-bold">Monitor 27"</div>
                                    <div class="text-muted small">SKU: MON-002</div>
                                </div>
                            </div>
                        </td>
                        <td>Electronics</td>
                        <td>12</td>
                        <td>$299.99</td>
                        <td><span class="status-active">In Stock</span></td>
                        <td>Feb 20, 2024</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary me-2">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar me-3 bg-warning">K</div>
                                <div>
                                    <div class="fw-bold">Keyboard Pro</div>
                                    <div class="text-muted small">SKU: KB-003</div>
                                </div>
                            </div>
                        </td>
                        <td>Accessories</td>
                        <td>0</td>
                        <td>$89.99</td>
                        <td><span class="status-inactive">Out of Stock</span></td>
                        <td>Mar 10, 2024</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary me-2">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar me-3 bg-info">M</div>
                                <div>
                                    <div class="fw-bold">Mouse Wireless</div>
                                    <div class="text-muted small">SKU: MS-004</div>
                                </div>
                            </div>
                        </td>
                        <td>Accessories</td>
                        <td>45</td>
                        <td>$49.99</td>
                        <td><span class="status-active">In Stock</span></td>
                        <td>Apr 5, 2024</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary me-2">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar me-3 bg-danger">D</div>
                                <div>
                                    <div class="fw-bold">Docking Station</div>
                                    <div class="text-muted small">SKU: DS-005</div>
                                </div>
                            </div>
                        </td>
                        <td>Accessories</td>
                        <td>8</td>
                        <td>$199.99</td>
                        <td><span class="status-active">In Stock</span></td>
                        <td>May 12, 2024</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary me-2">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const toggleBtn = document.getElementById('toggleSidebar');
    let isCollapsed = false;
    
    toggleBtn.addEventListener('click', function() {
        if (window.innerWidth <= 768) {
            // On mobile, toggle visibility
            if (sidebar.style.width === '0px' || sidebar.style.width === '') {
                sidebar.style.width = '250px';
                sidebar.style.opacity = '1';
                sidebar.style.pointerEvents = 'auto';
                mainContent.classList.remove('sidebar-collapsed');
            } else {
                sidebar.style.width = '0px';
                sidebar.style.opacity = '0';
                sidebar.style.pointerEvents = 'none';
                mainContent.classList.add('sidebar-collapsed');
            }
        } else {
            // On desktop, collapse/expand
            if (isCollapsed) {
                sidebar.classList.remove('collapsed');
                mainContent.classList.remove('sidebar-collapsed');
                toggleBtn.innerHTML = '<i class="fas fa-chevron-left"></i>';
                isCollapsed = false;
            } else {
                sidebar.classList.add('collapsed');
                mainContent.classList.add('sidebar-collapsed');
                toggleBtn.innerHTML = '<i class="fas fa-chevron-right"></i>';
                isCollapsed = true;
            }
        }
    });
    
    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth <= 768) {
            sidebar.classList.remove('collapsed');
            mainContent.classList.remove('sidebar-collapsed');
            toggleBtn.innerHTML = '<i class="fas fa-chevron-left"></i>';
            isCollapsed = false;
        }
    });
});
</script>
</body>
</html>
```