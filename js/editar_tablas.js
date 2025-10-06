document.addEventListener('DOMContentLoaded', () => {
    const editBtn = document.getElementById('edit-btn');
    const table = document.getElementById('record-table');
    let editing = false;
    
    console.log("✅ Script cargado correctamente");
    editBtn.addEventListener('click', () => {
        editing = !editing;
        const rows = table.querySelectorAll('tbody tr');

        rows.forEach(row => {
            row.querySelectorAll('td').forEach((cell, index) => {
                // No hacemos editable la columna ID
                if (index !== 0) cell.contentEditable = editing;
            });

            // Cuando se deja de editar, colorear según estado
            if (!editing) {
                const estadoCell = row.cells[5];
                if (!estadoCell) return;

                const estado = estadoCell.textContent.trim().toLowerCase();
                row.classList.remove('estado-verde', 'estado-amarillo', 'estado-rojo');

                if (estado === 'devuelto') row.classList.add('estado-verde');
                else if (estado === 'prestado') row.classList.add('estado-amarillo');
                else if (estado === 'vencido') row.classList.add('estado-rojo');
            }
        });

        editBtn.textContent = editing ? "Guardar" : "Editar";
        if (!editing) saveTableData();
    });

    function saveTableData() {
        const rows = table.querySelectorAll('tbody tr');
        const data = [];

        rows.forEach(row => {
            const rowData = [];
            const id = row.getAttribute('data-id') || null;
            rowData.push(id);

            row.querySelectorAll('td').forEach((cell, index) => {
                if (index !== 0) rowData.push(cell.textContent);
            });

            // Solo guarda filas que tengan ID
            if (id) data.push(rowData);
        });

        fetch('registro.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(res => res.text())
        .then(res => {
            console.log("Respuesta del servidor:");
            console.log(res);
        })
        .catch(err => console.error("Error al guardar:", err));
    }
});
