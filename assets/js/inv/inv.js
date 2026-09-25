

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