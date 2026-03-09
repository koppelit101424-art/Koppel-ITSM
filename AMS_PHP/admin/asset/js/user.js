$(document).ready(function () {
    const table = $('#usersTable').DataTable({
        "pageLength": 10,
        "lengthMenu": [5, 10, 25, 50, 100],
        "order": [[0, "desc"]]
    });

    // Global search (if you have a #globalSearch input)
    $('#globalSearch').on('keyup', function () {
        table.search(this.value).draw();
    });

    // === Context Menu Logic ===
    const contextMenu = document.getElementById('contextMenu');
    let selectedUserId = null;
    let selectedUserName = null;

    // Single contextmenu listener (remove the duplicate!)
    document.querySelectorAll('#usersTable tbody tr').forEach(row => {
        row.addEventListener('contextmenu', function (e) {
            e.preventDefault();

            selectedUserId = this.querySelector('td:first-child').innerText;
            selectedUserName = this.querySelectorAll('td')[2].innerText; // Full Name column

            // Set Edit link
            document.getElementById('editUserLink').href = 'user/edit_user.php?user_id=' + encodeURIComponent(selectedUserId);

            // Set Delete confirmation
            const deleteLink = document.getElementById('deleteUserLink');
            deleteLink.onclick = function (e) {
                e.preventDefault();
                if (confirm('Are you sure you want to delete ' + selectedUserName + '?')) {
                    window.location.href = 'user/delete_user.php?user_id=' + encodeURIComponent(selectedUserId);
                }
                contextMenu.style.display = 'none';
            };

            // Set Resigned action
            const resignLink = document.getElementById('resignedUserLink');
            resignLink.onclick = function (e) {
                e.preventDefault();
                openResignModal(selectedUserId);
                contextMenu.style.display = 'none';
            };

            // Show menu
            contextMenu.style.display = 'block';
            contextMenu.style.left = e.pageX + 'px';
            contextMenu.style.top = e.pageY + 'px';

            // Keep menu in viewport
            const rect = contextMenu.getBoundingClientRect();
            if (rect.right > window.innerWidth) {
                contextMenu.style.left = (e.pageX - rect.width) + 'px';
            }
            if (rect.bottom > window.innerHeight) {
                contextMenu.style.top = (e.pageY - rect.height) + 'px';
            }
        });
    });

    // Hide context menu on outside click
    document.addEventListener('click', function () {
        contextMenu.style.display = 'none';
    });

    // Prevent context menu from closing when clicking inside it
    contextMenu.addEventListener('click', function (e) {
        e.stopPropagation();
    });
});

