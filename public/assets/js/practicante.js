async function cargarPracticantes() {
    const resp = await api.listarPracticantes();
    const tbody = document.getElementById('tablePracticantesBody');
    tbody.innerHTML = '';

    if (!resp.success) {
        tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;">Error: ${resp.message}</td></tr>`;
        return;
    }

    const data = resp.data;
    if (Array.isArray(data) && data.length > 0) {
        data.forEach(p => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${p.PracticanteID}</td>
                <td>${p.NombreCompleto}</td>
                <td>${p.Universidad || '-'}</td>
                <td>${p.Area || '-'}</td>
                <td>${p.Estado || '-'}</td>
                <td>${p.FechaRegistro || '-'}</td>
                <td>
                    <button class="btn btn-sm" onclick="verPracticante(${p.PracticanteID})">Ver</button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    } else {
        tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;">No hay practicantes registrados</td></tr>`;
    }
}

async function verPracticante(id) {
    try {
        const res = await api.getPracticante(id);
        const p = res.data;

        alert(`👀 Detalles del practicante:
        Nombre: ${p.Nombres} ${p.ApellidoPaterno}
        Carrera: ${p.Carrera}
        Universidad: ${p.Universidad}`);
    } catch (err) {
        alert("Error al obtener practicante: " + err.message);
    }
}

async function editarPracticante(id) {
    const nuevoEmail = prompt("Ingrese nuevo correo:");
    if (!nuevoEmail) return;

    try {
        const res = await api.actualizarPracticante(id, { Email: nuevoEmail });
        alert(res.message);
        location.reload();
    } catch (err) {
        alert("Error al actualizar practicante: " + err.message);
    }
}

async function eliminarPracticante(id) {
    if (!confirm("¿Seguro que deseas eliminar este practicante?")) return;

    try {
        const res = await api.delete(`/practicantes/${id}`);
        alert(res.message);
        location.reload();
    } catch (err) {
        alert("Error al eliminar practicante: " + err.message);
    }
}

