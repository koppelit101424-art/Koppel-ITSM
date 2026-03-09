<?php
// ============================================
// FETCH CREDENTIAL DATA FOR TABLE
// ============================================
// Join with user_tb to get fullname instead of just user_id
$cred_sql = "SELECT c.*, u.fullname as updater_name 
        FROM credential_tb c 
        LEFT JOIN user_tb u ON c.updated_by = u.user_id 
        ORDER BY c.cred_id DESC";
$cred_result = $conn->query($cred_sql);
?>

<!-- Credential Table Card -->
<div class="card shadow-sm rounded-3 mb-4">
    <div class="card-header d-flex justify-content-between align-items-center bg-white border-bottom-0">
        <h5 class="mb-0 text-primary fw-semibold">Credentials</h5>
        <div>
            <a href="?page=inventory/crud/add_credential" class="btn btn-sm btn-primary me-2">
                <i class="fas fa-plus me-1"></i> Add Credentials
            </a>
            <!-- <a href="print_inventory.php" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-file-csv me-1"></i> Export CSV
            </a> -->
        </div>
    </div>

                <div class="card-body">
        <div class="table-responsive">
            <table id="credentialTable" class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="display:none;">ID</th>
                        <th>System</th>
                        <th>Description</th>
                        <th>Username</th>
                        <th>Password</th>
                        <th>Recovery Email</th>
                            <th style="width: 160px; max-width: 160px;">URL</th>
                        <th>Date</th>
                        <th>Updatedby</th>
                    </tr>
                </thead>
                
                <tbody id="credentialTableBody">
                    <?php if ($cred_result && $cred_result->num_rows > 0): ?>
                        <?php while($row = $cred_result->fetch_assoc()): ?>
                        <tr data-id="<?= $row['cred_id'] ?>" class="credential-row">
                            <td style="display:none;"><?= $row['cred_id'] ?></td>
                            <td><?= htmlspecialchars($row['system']) ?></td>
                            <td><?= htmlspecialchars($row['description']) ?></td>
                            <td><?= htmlspecialchars($row['username']) ?></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <input type="password"
                                           class="form-control form-control-sm password-field"
                                           value="<?= htmlspecialchars($row['password']) ?>"
                                           readonly
                                           style="max-width:160px;">
                                    <i class="fas fa-eye text-primary cursor-pointer"
                                       onclick="togglePassword(this)"></i>
                                    <i class="fas fa-copy text-success cursor-pointer"
                                       onclick="copyPassword(this)"></i>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($row['recovery_email']) ?></td>
                            <td style="width: 160px; max-width: 160px;" class="url-cell">
                                <?php if (!empty($row['url_link'])): ?>
                                    <a href="<?= htmlspecialchars($row['url_link']) ?>" 
                                    target="_blank" 
                                    class="text-decoration-none url-link"
                                    title="<?= htmlspecialchars($row['url_link']) ?>">
                                        <?= htmlspecialchars($row['url_link']) ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            </td>
                            <td><?= date('m-d-Y', strtotime($row['date_updated'])) ?></td>
                            <td><?= htmlspecialchars($row['updater_name'] ?? 'Unknown') ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td style="display:none;"></td>
                            <td colspan="8" class="text-center py-4 text-muted">No credentials found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div></div>

<?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin'): ?>
<!-- CONTEXT MENU FOR CREDENTIALS -->
<div id="credentialContextMenu" class="custom-menu">
    <ul class="list-unstyled mb-0">
        <li class="p-2 hover-bg-light cursor-pointer" id="ctxEdit">
            <i class="fas fa-edit text-primary me-2"></i>Edit
        </li>
        <li class="p-2 hover-bg-light cursor-pointer text-danger" id="ctxDelete">
            <i class="fas fa-trash me-2"></i>Delete
        </li>
    </ul>
</div>
<?php endif; ?>

<!-- Credential Table Specific Styles -->
<style>
    #credentialContextMenu {
        display: none;
        position: absolute;
        z-index: 1050;
        min-width: 140px;
        background: white;
        border: 1px solid rgba(0,0,0,0.15);
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
        border-radius: 0.375rem;
        overflow: hidden;
    }
    #credentialContextMenu li:hover {
        background-color: #f8f9fa;
    }
    .credential-row {
        cursor: context-menu;
    }
    .cursor-pointer {
        cursor: pointer;
    }
    .hover-bg-light:hover {
        background-color: #f8f9fa;
    }
    #credentialSearch:focus {
        box-shadow: 0 0 0 0.2rem rgba(51, 161, 224, 0.25);
        border-color: #33A1E0;
    }
</style>

<!-- Credential Table JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle password visibility
    window.togglePassword = function(icon) {
        const input = icon.parentElement.querySelector('.password-field');
        if (input.type === "password") {
            input.type = "text";
            icon.classList.remove("fa-eye");
            icon.classList.add("fa-eye-slash");
        } else {
            input.type = "password";
            icon.classList.remove("fa-eye-slash");
            icon.classList.add("fa-eye");
        }
    }

    // Copy password to clipboard
    window.copyPassword = function(icon) {
        const input = icon.parentElement.querySelector('.password-field');
        navigator.clipboard.writeText(input.value).then(() => {
            const originalClass = icon.className;
            icon.classList.remove("fa-copy");
            icon.classList.add("fa-check");
            
            setTimeout(() => {
                icon.className = originalClass;
            }, 1500);
        }).catch(err => {
            console.error('Failed to copy: ', err);
        });
    }

    // ============================================
    // SEARCH FUNCTIONALITY
    // ============================================
    const credentialSearchInput = document.getElementById('credentialSearch');
    const clearSearchBtn = document.getElementById('clearCredentialSearch');
    let credTable = null;
    
    // Initialize DataTable if available
    if ($.fn.DataTable) {
        credTable = $('#credentialTable').DataTable({
            "pageLength": 10,
            "lengthMenu": [10, 25, 50, 100, 250, 500],
            "columnDefs": [
                { "visible": false, "targets": [0] },
                { "orderable": false, "targets": [4, 6] }
            ],
            "order": [[0, 'desc']],
            "autoWidth": false,

            "responsive": true
        });
        
        // Search on input
        if (credentialSearchInput) {
            credentialSearchInput.addEventListener('keyup', function() {
                if (credTable) {
                    credTable.search(this.value).draw();
                }
            });
        }
        
        // Clear search button
        if (clearSearchBtn) {
            clearSearchBtn.addEventListener('click', function() {
                if (credentialSearchInput) {
                    credentialSearchInput.value = '';
                    if (credTable) {
                        credTable.search('').draw();
                    }
                    credentialSearchInput.focus();
                }
            });
        }
    }

    // ============================================
    // CONTEXT MENU LOGIC
    // ============================================
    const credContextMenu = document.getElementById("credentialContextMenu");
    let selectedCredId = null;

    // Right-click handler for credential rows
    document.getElementById("credentialTableBody").addEventListener("contextmenu", function(e) {
        e.preventDefault();
        const row = e.target.closest("tr.credential-row");
        
        if (row && row.getAttribute("data-id")) {
            selectedCredId = row.getAttribute("data-id");
            
            // Position context menu
            const menuWidth = 140;
            const menuHeight = 80;
            
            let left = e.pageX;
            let top = e.pageY;
            
            // Keep menu within viewport
            if (left + menuWidth > window.innerWidth) {
                left = window.innerWidth - menuWidth - 10;
            }
            if (top + menuHeight > window.innerHeight) {
                top = window.innerHeight - menuHeight - 10;
            }
            
            credContextMenu.style.display = "block";
            credContextMenu.style.left = left + "px";
            credContextMenu.style.top = top + "px";
        }
    });

    // Hide context menu on click elsewhere
    document.addEventListener("click", function(e) {
        if (!credContextMenu.contains(e.target)) {
            credContextMenu.style.display = "none";
        }
    });

    // Edit: Link to add_credential.php with cred_id parameter
    document.getElementById("ctxEdit").addEventListener("click", function(e) {
        e.preventDefault();
        credContextMenu.style.display = "none";
        if (selectedCredId) {
            window.location.href = '?page=inventory/crud/add_credential&cred_id=' + encodeURIComponent(selectedCredId);
        }
    });
    // Delete: Simple confirm + redirect to delete handler
    document.getElementById("ctxDelete").addEventListener("click", function(e) {
        e.preventDefault();
        credContextMenu.style.display = "none";
        
        if (selectedCredId && confirm("Are you sure you want to delete this credential? This action cannot be undone.")) {
            window.location.href = '?page=inventory/crud/delete_credential&cred_id=' + encodeURIComponent(selectedCredId);
        }
    });
});
</script>