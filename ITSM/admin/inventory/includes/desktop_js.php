
    <script>
    $(document).ready(function () {
        var table = $('#desktopTable').DataTable({
            "pageLength": 25,
            "lengthMenu": [10, 25, 50, 100, 250, 500],
            "columnDefs": [
                { "visible": false, "targets":1},
                { "orderable": false, "targets": [9] }
            ],
            "order": [[0, 'desc']],
            "autoWidth": false,
            "language": {
                "search": "Search within table:"
            }
        });

        $('.area-filter').on('click', function(){
            $('.area-filter').removeClass('active');
            $(this).addClass('active');
            const area = $(this).data('area');
            table.column(1).search(area).draw();
            table.order([0, 'desc']).draw();
        });

 // CLICK: open modal on row click
$('#desktopTable tbody').on('click', 'tr.desktop-row', function () {
    const row = $(this);

    $('#modalDesktopId').text(row.data('id'));
    $('#modalCpu').text(row.data('cpu'));
    $('#modalRam').text(row.data('ram'));
    $('#modalRom').text(row.data('rom'));
    $('#modalMotherboard').text(row.data('motherboard'));
    $('#modalMonitor').text(row.data('monitor'));
    $('#modalIp').text(row.data('ip'));
    $('#modalMac').text(row.data('mac'));
    $('#modalComputerName').text(row.data('computer'));
    $('#modalWindowsKey').text(row.data('windows-key'));
    $('#modalKeyboard').text(row.data('keyboard'));
    $('#modalMouse').text(row.data('mouse'));
    $('#modalAvr').text(row.data('avr'));
    $('#modalAntivirus').text(row.data('antivirus'));
    $('#modalTagNumber').text(row.data('tag'));
    $('#modalAreaId').text(row.data('area-id'));
    $('#modalRemarks').text(row.data('remarks'));

    $('#editDesktopLink').attr('href', '?page=inventory/crud/edit_desktop&id=' + encodeURIComponent(row.data('id')));

    const modal = new bootstrap.Modal(document.getElementById('desktopDetailsModal'));
    modal.show();
});

    let selectedDesktopId = null;
        const contextMenu = $('#contextMenu');

        $('#desktopTable tbody').on('contextmenu', 'tr', function(e){
            e.preventDefault();
            selectedDesktopId = $(this).find('td:first').text();
            contextMenu.css({ top: e.pageY + 'px', left: e.pageX + 'px' }).show();
        });

        $(document).on('click', function(e){
            if(!$(e.target).closest('#contextMenu').length){
                contextMenu.hide();
            }
        });
            $('#editOption').on('click', function(e){
            e.preventDefault();
            // if(selectedDesktopId) window.location.href = 'edit_desktop.php?id=' + encodeURIComponent(selectedDesktopId);
            if(selectedDesktopId) window.location.href = '?page=inventory/crud/edit_desktop&id=' + encodeURIComponent(selectedDesktopId);
                //    $('#editLink').attr('href', '?page=inventory/crud/edit_item&item_id=' + encodeURIComponent(itemId));
            contextMenu.hide();
        });

        $('#assignOption').on('click', function(e){
            e.preventDefault();
            if(selectedDesktopId) window.location.href = '?page=inventory/includes/assign_desktop&id=' + encodeURIComponent(selectedDesktopId);
            contextMenu.hide();
        });
    });
    </script>
    <!-- 
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> 