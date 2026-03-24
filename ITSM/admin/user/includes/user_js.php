  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>

    <!-- Context Menu + Resign + Enable/Disable JS -->
    <script>
    $(document).ready(function () {

        var table = $('#usersTable').DataTable({
            "pageLength": 10,
            "lengthMenu": [5, 10, 25, 50, 100],
            "order": [[0, "desc"]]
        });

        const contextMenu = document.getElementById('contextMenu');
        let selectedUserId = null;
        let selectedUserRow = null;

        // 🔥 THIS WORKS ON ALL PAGES
        $('#usersTable tbody').on('contextmenu', 'tr', function (e) {
            e.preventDefault();

            selectedUserRow = this;
            selectedUserId = $(this).find('td:first').text().trim();

            // Edit
            $('#editUserLink').attr('href',
                'user/edit_user.php?user_id=' + encodeURIComponent(selectedUserId)
            );

            // Change Password
            $('#changePasswordLink').attr('href',
                'user/change_password.php?user_id=' + encodeURIComponent(selectedUserId)
            );

            // Enable / Disable
            $('#toggleStatusLink').off('click').on('click', function(e){
                e.preventDefault();

                const statusText = $(selectedUserRow).find('td:last').text();

                const currentStatus = statusText.includes('Active') ? 1 : 0;
                const newStatus = currentStatus === 1 ? 0 : 1;

                $('#toggleStatusUserId').val(selectedUserId);
                $('#toggleStatusValue').val(newStatus);
                $('#toggleStatusForm').submit();
            });

            // Delete
            $('#deleteUserLink').off('click').on('click', function (e) {
                e.preventDefault();

                const userName = $(selectedUserRow).find('td').eq(2).text();

                if (confirm('Are you sure you want to delete ' + userName + '?')) {
                    window.location.href =
                        'user/delete_user.php?user_id=' + encodeURIComponent(selectedUserId);
                }

                contextMenu.style.display = 'none';
            });

            // Resign
            $('#resignedUserLink').off('click').on('click', function (e) {
                e.preventDefault();

                $('#resignUserId').val(selectedUserId);

                const modal = new bootstrap.Modal(
                    document.getElementById('resignModal')
                );
                modal.show();

                contextMenu.style.display = 'none';
            });

            // Show menu
            contextMenu.style.display = 'block';
            contextMenu.style.left = e.pageX + 'px';
            contextMenu.style.top = e.pageY + 'px';

            const rect = contextMenu.getBoundingClientRect();

            if (rect.right > window.innerWidth)
                contextMenu.style.left = (e.pageX - rect.width) + 'px';

            if (rect.bottom > window.innerHeight)
                contextMenu.style.top = (e.pageY - rect.height) + 'px';
        });

        // Hide on click outside
        $(document).on('click', function () {
            contextMenu.style.display = 'none';
        });

        $('#contextMenu').on('click', function (e) {
            e.stopPropagation();
        });
        // === Global Search ===
        $('#globalSearch').on('keyup', function() {
            table.search(this.value).draw();
        });

    });
    </script>