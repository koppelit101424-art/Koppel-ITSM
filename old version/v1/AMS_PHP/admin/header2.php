
<style>

/* Search results dropdown */
#searchResults {
    border-radius: 6px;
    overflow: hidden;
    background: #fff;
    border: 1px solid #dee2e6;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.18);
    max-height: 280px;
    overflow-y: auto;
    margin-top: 4px;
    animation: fadeSlideIn 0.18s ease-out;
}

/* Smooth scrollbar (Chrome / Edge) */
#searchResults::-webkit-scrollbar {
    width: 6px;
}
#searchResults::-webkit-scrollbar-thumb {
    background: rgba(0, 0, 0, 0.25);
    border-radius: 4px;
}
#searchResults::-webkit-scrollbar-track {
    background: transparent;
}

/* Search item hover refinement */
#searchResults .list-group-item {
    border: none;
    padding: 10px 14px;
    font-size: 0.92rem;
    transition: background-color 0.15s ease, padding-left 0.15s ease;
}

#searchResults .list-group-item:hover {
    background-color: #f1f5ff;
    padding-left: 18px;
}

/* Animation */
@keyframes fadeSlideIn {
    from {
        opacity: 0;
        transform: translateY(-6px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}


</style>
<div class="d-flex justify-content-between align-items-center mb-3">
   <h2 class="text-primary" style="font-family:  'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif;">IT System Management</h2>

    <div class="d-flex align-items-center">
        <!-- Global Search -->
        <div class="search-box me-3 position-relative">
            <input type="text" placeholder="Search..." class="form-control" id="globalSearch" autocomplete="off">
            <div id="searchResults"
                 class="list-group position-absolute w-100 d-none"
                 style="z-index:1000;"></div>
        </div>

        <!-- User Dropdown -->
        <div class="dropdown">
            <button class="btn btn-sm dropdown-toggle d-flex align-items-center"
                    type="button"
                    id="userDropdown"
                    data-bs-toggle="dropdown"
                    aria-expanded="false">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2"
                     style="width:36px;height:36px;font-size:0.9rem;font-weight:600;">
                    <?php
                    if (isset($_SESSION['fullname'])) {
                        $initials = '';
                        $names = explode(' ', $_SESSION['fullname']);
                        foreach ($names as $name) {
                            $initials .= strtoupper(substr($name, 0, 1));
                        }
                        echo htmlspecialchars(substr($initials, 0, 2));
                    } else {
                        echo 'G';
                    }
                    ?>
                </div>
                <span class="d-none d-md-inline fw-semibold"
                      style="color:#495057;font-size:0.95rem;">
                    <?= isset($_SESSION['fullname']) ? htmlspecialchars($_SESSION['fullname']) : 'Guest' ?>
                </span>
            </button>

            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                <li>
                    <a class="dropdown-item d-flex align-items-center"
                       href="../profile/edit.php">
                        <i class="fas fa-user me-2 text-primary"></i>
                        My Profile
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item d-flex align-items-center text-danger"
                       href="logout.php"
                       onclick="return confirm('Are you sure you want to log out?');">
                        <i class="fas fa-sign-out-alt me-2"></i>
                        Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>


<!-- GLOBAL SEARCH SCRIPT -->
<script>
const searchInput = document.getElementById('globalSearch');
const resultsBox = document.getElementById('searchResults');

const searchMap = [
    // Users
    { label: 'Add User', keywords: ['add user', 'new user'], url: '../user/add_user.php' },
    { label: 'Users', keywords: ['users', 'user list'], url: '../users.php' },

    // Inventory (main)
    { label: 'Inventory', keywords: ['inventory', 'items'], url: '../index.php' },
        { label: 'Add Items', keywords: ['inventory', 'items list', 'add item'], url: '../inv/add_item.php' },

    // Inventory sub-items
    { label: 'Desktops', keywords: ['desktop', 'desktops'], url: '../inv/desktop/desktop.php' },
        { label: 'Add Desktop', keywords: ['desktop', 'desktops', 'add desktop'], url: '../inv/desktop/add_desktop.php' },
    { label: 'Laptops', keywords: ['laptop', 'laptops'], url: '../inv/laptop/laptop.php' },
    { label: 'Printers', keywords: ['printer', 'printers'], url: '../inv/printer/printer.php' },
    { label: 'IP Phones', keywords: ['ip phone', 'ip phones', 'phone'], url: '../inv/ip_phone/ip_phone.php' },
    { label: 'Biometrics', keywords: ['biometric', 'biometrics', 'fingerprint'], url: '../inv/biometrics/biometrics.php' },

    // Tickets & Requests
    { label: 'Tickets', keywords: ['ticket', 'tickets'], url: '../tickets.php' },
        { label: 'All Tickets', keywords: ['ticket', 'all_tickets'], url: '../tickets.php' },
    { label: 'Assigned Tickets', keywords: ['ticket', 'tickets', 'assigned'], url: '../ticket/assigned_tickets.php' },
    { label: 'SLA', keywords: ['ticket', 'tickets', 'sla'], url: '../ticket/sla.php' },
    { label: 'SLA Settings', keywords: ['ticket', 'tickets', 'sla settings'], url: '../ticket/sla_settings.php' },
    { label: 'Requests', keywords: ['request', 'requests'], url: '../requests.php' },
        { label: 'Add Request', keywords: ['request', 'add_request'], url: '../request/add_request.php' },

    // Transactions
    { label: 'Transactions', keywords: ['transaction', 'transactions'], url: '../transactions.php' },

    // Reports & Settings
    { label: 'Reports', keywords: ['report', 'reports'], url: '../reports.php' },
    { label: 'Settings', keywords: ['settings'], url: '../settings.php' },

    // Profile
    { label: 'My Profile', keywords: ['profile', 'account'], url: 'profile/edit.php' }
];


searchInput.addEventListener('input', function () {
    const query = this.value.toLowerCase().trim();
    resultsBox.innerHTML = '';

    if (!query) {
        resultsBox.classList.add('d-none');
        return;
    }

    const matches = searchMap.filter(item =>
        // item.keywords.some(k => k.includes(query))
        item.keywords.some(k => k.includes(query) || query.includes(k))

    );

    if (!matches.length) {
        resultsBox.classList.add('d-none');
        return;
    }

    matches.forEach(item => {
        const a = document.createElement('a');
        a.href = item.url;
        a.className = 'list-group-item list-group-item-action';
        a.textContent = item.label;
        resultsBox.appendChild(a);
    });

    resultsBox.classList.remove('d-none');
});

searchInput.addEventListener('keydown', function (e) {
    if (e.key === 'Enter') {
        const first = resultsBox.querySelector('a');
        if (first) window.location.href = first.href;
    }
});

// document.addEventListener('click', function (e) {
//     if (!searchInput.contains(e.target)) {
//         resultsBox.classList.add('d-none');
//     }
// });

document.addEventListener('click', function (e) {
    if (!e.target.closest('.search-box')) {
        resultsBox.classList.add('d-none');X`X`
    }
});
document.addEventListener('keydown', function (e) {
    // Ignore if user is typing in an input, textarea, or select
    const tag = document.activeElement.tagName.toLowerCase();
    if (tag === 'input' || tag === 'textarea' || tag === 'select') return;

    // Press "K" to focus global search
    if (e.key.toLowerCase() === 'f') {
        e.preventDefault();
        searchInput.focus();
        searchInput.select();
    }
           // Press "Q" → go back
    if (e.key.toLowerCase() === 'q') {
        e.preventDefault();
        if (window.history.length > 1) {
            window.history.back();
        }
    }
});
</script>
