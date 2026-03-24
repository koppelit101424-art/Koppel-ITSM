
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function () {
    var table = $('#desktopTable').DataTable({
        "pageLength": 15,
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

// CLICK
    $(document).on('click', '.desktop-row', function () {

        let desktopId = $(this).data('id');

        $.ajax({
            url: 'get_desktop_details.php',
            type: 'POST',
            data: { id: desktopId },
            dataType: 'json',
            success: function (response) {

                $('#modalDesktopId').text(response.desktop_id);
                $('#modalCpu').text(response.cpu);
                $('#modalRam').text(response.ram);
                $('#modalRom').text(response.rom_w_serial);
                $('#modalMotherboard').text(response.motherboard);
                $('#modalMonitor').text(response.monitor_w_serial);
                $('#modalIp').text(response.ip_address);
                $('#modalMac').text(response.mac_address);
                $('#modalComputerName').text(response.computer_name);
                $('#modalWindowsKey').text(response.windows_key);
                $('#modalKeyboard').text(response.keyboard);
                $('#modalMouse').text(response.mouse);
                $('#modalAvr').text(response.avr);
                $('#modalAntivirus').text(response.antivirus);
                $('#modalTagNumber').text(response.tag_number);
                $('#modalAreaId').text(response.desktop_area_id);
                $('#modalRemarks').text(response.remarks);

                $('#editDesktopLink').attr('href', 'edit_desktop.php?id=' + response.desktop_id);

                new bootstrap.Modal(document.getElementById('desktopDetailsModal')).show();
            }
        });

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
        if(selectedDesktopId) window.location.href = 'edit_desktop.php?id=' + encodeURIComponent(selectedDesktopId);
        contextMenu.hide();
    });

    $('#assignOption').on('click', function(e){
        e.preventDefault();
        if(selectedDesktopId) window.location.href = 'assign_desktop.php?id=' + encodeURIComponent(selectedDesktopId);
        contextMenu.hide();
    });
});
</script>
