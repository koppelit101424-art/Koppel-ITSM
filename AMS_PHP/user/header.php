    <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="text-primary" style="font-family:  'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif;">IT System Management</h2>
        <div class="d-flex align-items-center">
            <div class="dropdown">
                <button class="btn btn-sm dropdown-toggle d-flex align-items-center" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 36px; height: 36px; font-size: 0.9rem; font-weight: 600;">
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
                    <span class="d-none d-md-inline fw-semibold" style="color: #495057; font-size: 0.95rem;">
                        <?php if (isset($_SESSION['fullname'])): ?>
                            <?= htmlspecialchars($_SESSION['fullname']) ?>
                        <?php else: ?>
                            Guest
                        <?php endif; ?>
                    </span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="<?= BASE_URL ?>/user/profile/edit.php">
                            <i class="fas fa-user me-2 text-primary"></i>
                            My Profile
                        </a>
                     

                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center text-danger"
                        href="<?= BASE_URL ?>/user/logout.php"
                        onclick="return confirm('Are you sure you want to log out?');">
                            <i class="fas fa-sign-out-alt me-2"></i>
                            Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
