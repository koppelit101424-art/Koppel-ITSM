
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const toggleBtn = document.getElementById('toggleSidebar');
        let isCollapsed = false;
        
        toggleBtn.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
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
        
        window.addEventListener('resize', function() {
            if (window.innerWidth <= 768) {
                sidebar.classList.remove('collapsed');
                mainContent.classList.remove('sidebar-collapsed');
                toggleBtn.innerHTML = '<i class="fas fa-chevron-left"></i>';
                isCollapsed = false;
            }
        });
    });




   $(document).ready(function() {


        // === Global Search ===
        $('#globalSearch').on('keyup', function() {
            table.search(this.value).draw();
        });

        // === Filter by Item Name ===
        $('#filterByName').on('change', function() {
            var val = $(this).val();
            table.column(2).search(val ? '^' + $.fn.dataTable.util.escapeRegex(val) + '$' : '', true, false).draw();
        });

    // Type filter (buttons)
        $('.type-filter').on('click', function() {
        // Reset all buttons to outline
        $('.type-filter')
            .removeClass('btn-blue active')
            .addClass('btn-outline-blue');

        // Set only the clicked one to blue
        $(this)
            .removeClass('btn-blue')
            .addClass('btn-blue active');

            const type = $(this).data('type');
            if (type) {
            table.column(10).search('^' + type + '$', true, false).draw(); 
            } else {
            table.column(10).search('').draw();
            }
        });

        $(document).on('click', function() {
            $('#contextMenu').hide();
        });

        $('#contextMenu').on('click', function(e) {
            e.stopPropagation();
        });
    });