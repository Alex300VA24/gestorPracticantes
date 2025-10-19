let modoEdicion = false;
let idEdicion = null;

// --- FUNCIONES MODALES ---
function abrirModal() {
    document.getElementById("PracticanteModal").style.display = "flex";
}

function cerrarModal() {
    document.getElementById("PracticanteModal").style.display = "none";
    document.getElementById("formPracticante").reset();
}

// 🔹 Abrir modal para NUEVO
function abrirModalNuevoPracticante() {
    modoEdicion = false;
    document.getElementById("tituloModalPracticante").textContent = "Nuevo Practicante";
    document.getElementById("formPracticante").reset();
    document.getElementById("practicanteID").value = "";
    abrirModal();
}

// 🔹 Abrir modal para EDITAR
async function abrirModalEditarPracticante(id) {
    try {
        modoEdicion = true;
        document.getElementById("tituloModalPracticante").textContent = "Editar Practicante";

        const res = await api.getPracticante(id);
        idEdicion = id;
        const p = res.data;

        // Rellenar campos del formulario
        document.getElementById("practicanteID").value = p.ID || "";
        document.getElementById("Nombres").value = p.Nombres || "";
        document.getElementById("ApellidoPaterno").value = p.ApellidoPaterno || "";
        document.getElementById("ApellidoMaterno").value = p.ApellidoMaterno || "";
        document.getElementById("DNI").value = p.DNI || "";
        document.getElementById("Carrera").value = p.Carrera || "";
        document.getElementById("Universidad").value = p.Universidad || "";
        document.getElementById("Direccion").value = p.Direccion || "";
        document.getElementById("Telefono").value = p.Telefono || "";
        document.getElementById("Email").value = p.Email || "";

        abrirModal();
    } catch (err) {
        alert("❌ Error al obtener datos del practicante: " + err.message);
    }
}


// --- EVENTOS PRINCIPALES ---
document.getElementById("btnNuevoPracticante").addEventListener("click", abrirModalNuevoPracticante);

document.getElementById("formPracticante").addEventListener("submit", async (e) => {
    e.preventDefault();
    const formData = Object.fromEntries(new FormData(e.target).entries());
    let res;

    try {
        if (modoEdicion) {
            res = await api.actualizarPracticante(idEdicion, formData);
        } else {
            res = await api.crearPracticante(formData);
        }

        if (res.success) {
            alert(modoEdicion ? "✅ Practicante actualizado correctamente" : "✅ Practicante creado con éxito");
            cerrarModal();
            await cargarPracticantes();
        } else {
            alert("❌ Error: " + res.message);
        }
    } catch (error) {
        console.error("Error en formulario:", error);
        alert("⚠️ Error en la operación");
    }
});


// Metodo para ver informacion del Practicante
async function verPracticante(id) {
    try {
        const res = await api.getPracticante(id);
        const p = res.data;

        alert(`Detalles del practicante:
        Nombre: ${p.Nombres} ${p.ApellidoPaterno} ${p.ApellidoMaterno}
        DNI: ${p.DNI}
        Carrera: ${p.Carrera}
        Universidad: ${p.Universidad}
        Area: ${p.Area}
        Direccion: ${p.Direccion}
        Telefono: ${p.Telefono}
        Email: ${p.Email}
        Fecha de Registro: ${p.FechaRegistro}
        Fecha de Entrada: ${p.FechaEntrada}
        Fecha de Salida: ${p.FechaSalida}
        Estado: ${p.Estado}`);
    } catch (err) {
        alert("Error al obtener practicante: " + err.message);
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

// --- FUNCIÓN PARA CARGAR LA TABLA ---
async function cargarPracticantes() {
    try {
        const response = await api.getPracticantes();
        const practicantes = response.data || [];

        // Usa el tbody dentro de #tablaPracticantes
        const tbody = document.querySelector('#tablaPracticantes tbody');
        if (!tbody) {
            console.error('❌ No se encontró el tbody de la tabla de practicantes.');
            return;
        }

        tbody.innerHTML = '';

        if (practicantes.length === 0) {
            tbody.innerHTML = `<tr><td colspan="8" class="text-center">No hay practicantes registrados</td></tr>`;
            return;
        }

        practicantes.forEach(p => {
            const estadoClass = p.Estado ? p.Estado.toLowerCase() : 'pendiente';
            const estadoBadge = `<span class="status-badge status-${estadoClass}">${estadoClass.toUpperCase()}</span>`;

            const fila = document.createElement('tr');
            fila.innerHTML = `
                <td>${p.PracticanteID}</td>
                <td>${p.DNI}</td>
                <td>${p.NombreCompleto}</td>
                <td>${p.Carrera || '-'}</td>
                <td>${p.Universidad}</td>
                <td>${p.FechaRegistro ? new Date(p.FechaRegistro).toLocaleDateString() : '-'}</td>
                <td>${p.Area || '-'}</td>
                <td>${estadoBadge}</td>
                <td>
                    <button class="btn-primary" style="padding: 8px 12px; font-size: 0.8rem;">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn-success" style="padding: 8px 12px; font-size: 0.8rem;" >
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn-warning" style="padding: 8px 12px; font-size: 0.8rem;">
                        <i class="fas fa-check"></i>
                    </button>
                    <button class="btn-danger" style="padding: 8px 12px; font-size: 0.8rem;">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(fila);
            fila.querySelector('.btn-primary').addEventListener('click', () => abrirModalEditarPracticante(p.PracticanteID));
            fila.querySelector('.btn-success').addEventListener('click', () => verPracticante(p.PracticanteID));
            fila.querySelector('.btn-danger').addEventListener('click', () => eliminarPracticante(p.PracticanteID));

        });

    } catch (error) {
        console.error('❌ Error al listar practicantes:', error);
        const tbody = document.querySelector('#tablaPracticantes tbody');
        if (tbody) {
            tbody.innerHTML = `<tr><td colspan="8" class="text-center text-danger">Error al cargar los practicantes</td></tr>`;
        }
    }
}

cargarPracticantes();