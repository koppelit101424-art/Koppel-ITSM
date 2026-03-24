 <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
         <script>
          $(document).ready(function () {

          var table = $('#transactionTable').DataTable({
        "pageLength": 25,
        "lengthMenu": [10, 25, 50, 100, 250, 500],
        "columnDefs": [
            // { "visible": false, "targets": [0, 6, 10] },
            { "orderable": false, "targets": [9] }
        ],
        "order": [[0, 'desc']],
        "autoWidth": false,
        "language": {
            "search": "Search within table:"
        }
          });

          
          /* ===============================
            GLOBAL SEARCH
          =============================== */
          $('#globalSearch').on('keyup', function () {
            table.search(this.value).draw();
          });

          /* ===============================
            ITEM FILTER (Dropdown)
          =============================== */
          $('#filterByName').on('change', function () {
            table.column(2).search(this.value).draw();
          });

          /* ===============================
            TYPE FILTER (Buttons)
          =============================== */
          $('.type-filter').on('click', function () {

            $('.type-filter')
              .removeClass('btn-blue active')
              .addClass('btn-outline-blue');

            $(this)
              .removeClass('btn-outline-blue')
              .addClass('btn-blue active');

            const type = $(this).data('type');

            if (type) {
              table.column(11).search('^' + type + '$', true, false).draw();
            } else {
              table.column(11).search('').draw();
            }
          });

          /* ===============================
            AFTER TABLE DRAW (IMPORTANT)
            Fix context menu + tooltip
          =============================== */
          table.on('draw', function () {

            // Remove old tooltips
            $('[data-bs-toggle="tooltip"]').tooltip('dispose');

            // Reapply tooltip
            $('#transactionTable tbody tr').each(function () {
              const remarks = $(this).data('remarks');
              $(this).attr('data-bs-toggle', 'tooltip');
              $(this).attr('data-bs-placement', 'top');
              $(this).attr('title', remarks);
            });

            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
              return new bootstrap.Tooltip(tooltipTriggerEl);
            });

          });

          /* ===============================
            ROW CLICK → MODAL
          =============================== */
          $('#transactionTable tbody').on('click', 'tr', function (e) {

            if (e.button !== 0) return;

            $('#transactionTable tbody tr').removeClass('table-primary');
            $(this).addClass('table-primary');

            const row = $(this);

            $('#modalTransId').text(row.data('transaction'));
            $('#modalUser').text(row.data('user'));
            $('#modalItem').text(row.data('item'));
            $('#modalBrand').text(row.data('brand'));
            $('#modalModel').text(row.data('model'));
            $('#modalQty').text(row.data('qty'));
            $('#modalDate').text(row.data('date'));
            $('#modalReturned').text(row.data('returned-label'));
            $('#modalRemarks').text(row.data('remarks'));
            $('#modalStatus').text(row.data('status'));

            const modal = new bootstrap.Modal(document.getElementById('transactionModal'));
            modal.show();
          });

          /* ===============================
            CONTEXT MENU (RIGHT CLICK)
          =============================== */
          const contextMenu = document.getElementById('contextMenu');
          const returnLink = document.getElementById('returnLink');
          const printLink = document.getElementById('printLink');
          const printReturnedLink = document.getElementById('printReturnedLink');
          const editLink = document.getElementById('editLink');

          $('#transactionTable tbody').on('contextmenu', 'tr', function (e) {

            e.preventDefault();

            const transactionId = this.dataset.transaction;
            const notReturned = this.dataset.returned === '0';

            printLink.href = '?page=inventory/print/print_transaction&id=' + transactionId;

            if (notReturned) {
              returnLink.style.display = 'block';
              returnLink.href = '?page=inventory/crud/return_item&id=' + transactionId;
              printReturnedLink.style.display = 'none';
            } else {
              returnLink.style.display = 'none';
              printReturnedLink.style.display = 'block';
              printReturnedLink.href = '?page=inventory/print/print_returned&id=' + transactionId;
            }

            editLink.href = '?page=inventory/crud/edit_transaction&id=' + transactionId;

            contextMenu.style.top = e.pageY + 'px';
            contextMenu.style.left = e.pageX + 'px';
            contextMenu.style.display = 'block';
          });

          $(document).on('click', function () {
            contextMenu.style.display = 'none';
          });

        });
         </script>

