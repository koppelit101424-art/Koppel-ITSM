
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
    // Initialize DataTable
    var table = $('#inventoryTable').DataTable({
        "pageLength": 10,
        "lengthMenu": [5, 10, 25, 50, 100, 250],
        "columnDefs": [
            { "visible": false, "targets": [0, 6, 10] },
            { "orderable": false, "targets": [9] }
        ],
        "order": [[8, 'desc']],
        "autoWidth": false,
        "language": {
            "search": "Search within table:"
        }
    });

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

    // // === TOOLTIP on HOVER ===
    // $('#inventoryTable tbody').on('mouseenter', 'tr', function() {
    //     var desc = $(this).data('desc') || '';
    //     if (desc.trim() !== '') {
    //         // Store original title to avoid duplication
    //         if (!$(this).data('original-title')) {
    //             $(this).data('original-title', desc);
    //         }
    //         $(this).attr('title', desc)
    //                .attr('data-bs-toggle', 'tooltip')
    //                .attr('data-bs-placement', 'top');
            
    //         // Initialize tooltip if not already
    //         if (!$(this).data('bs.tooltip')) {
    //             new bootstrap.Tooltip(this);
    //         }
    //         // Trigger show
    //         bootstrap.Tooltip.getInstance(this)?.show();
    //     }
    // });

    //   // Add tooltip (hover description) for each row
    //   $('#inventoryTable tbody tr').each(function () {
    //     const desc = $(this).find('td:eq(6)').text(); // description column
    //     $(this).attr('data-bs-toggle', 'tooltip');
    //     $(this).attr('data-bs-placement', 'top');
    //     $(this).attr('title', desc);
    //   });

        // === MODAL on CLICK ===
        $('#inventoryTable tbody').on('click', 'tr', function(e) {

            var $row = $(this);
            var itemId = $row.data('item-id');   // ✅ FIXED

            $('#modalItemId').text($row.data('item-id')|| '—');
            $('#modalItemCode').text($row.data('code') || '—');
            $('#modalItemName').text($row.data('name') || '—');
            $('#modalItemBrand').text($row.data('brand') || '—');
            $('#modalItemModel').text($row.data('model') || '—');
            $('#modalItemSerial').text($row.data('serial') || '—');
            $('#modalItemDesc').text($row.data('desc') || '—');
            $('#modalItemQty').text($row.data('qty') || '0');
            $('#modalItemReceived').text($row.data('received') || '—');
            $('#modalItemType').text($row.data('type') || '—');

            // ✅ IMPORTANT — Set Edit Link
            $('#editLink').attr('href', 'inv/edit_item.php?item_id=' + encodeURIComponent(itemId));
            var statusHtml = (parseInt($row.data('qty')) > 0)
                ? '<span class="status-active">In Stock</span>'
                : '<span class="status-inactive">In Use</span>';

            $('#modalItemStatus').html(statusHtml);

            var modal = new bootstrap.Modal(document.getElementById('itemDetailsModal'));
            modal.show();
        });


    // === CONTEXT MENU (Right-click) ===
    $('#inventoryTable tbody').on('contextmenu', 'tr', function(e) {
        e.preventDefault();
        var $row = $(this);
        var itemId = $row.data('item-id');
        var qty = parseInt($row.data('qty'));

        $('#editLink').attr('href', 'inv/edit_item.php?item_id=' + encodeURIComponent(itemId));
        
        if (qty > 0) {
            $('#borrowLink').attr('href', 'inv/borrow_item.php?item_id=' + encodeURIComponent(itemId)).show();
            $('#issueLink').attr('href', 'inv/issue_item.php?item_id=' + encodeURIComponent(itemId)).show();
        } else {
            $('#borrowLink, #issueLink').hide();
        }

        $('#contextMenu')
            .css({
                display: 'block',
                left: e.pageX + 'px',
                top: e.pageY + 'px'
            });
    });

    $(document).on('click', function() {
        $('#contextMenu').hide();
    });

    $('#contextMenu').on('click', function(e) {
        e.stopPropagation();
    });
});