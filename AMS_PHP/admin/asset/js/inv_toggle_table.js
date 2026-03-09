function toggleTable() {
    const tableContainer = document.getElementById('inventoryTableContainer');
    const toggleBtn = document.getElementById('toggleTableBtn');
    
    if (tableContainer.style.display === 'none' || tableContainer.style.display === '') {
        tableContainer.style.display = 'block';
        toggleBtn.innerHTML = '<i class="fas fa-table"></i> Hide Inventory ';
    } else {
        tableContainer.style.display = 'none';
        toggleBtn.innerHTML = '<i class="fas fa-table"></i> Show Inventory ';
    }
}