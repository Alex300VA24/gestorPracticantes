let modoEdicion = false;
let idEdicion = null;


document.addEventListener('DOMContentLoaded', function() {
    // 🔹 Aquí defines el área del usuario logueado (puede venir del backend o sesión)
    const nombreAreaUsuario = sessionStorage.getItem('nombreArea'); 
    // Ejemplo: "RRHH", "Sistemas", "Contabilidad", etc.

    // 🔹 Obtenemos el div del filtro de área
    const filtroAreaDiv = document.getElementById('filtroArea').closest('div');

    // 🔹 Si el usuario NO es de RRHH, ocultamos el filtro de área
    if (nombreAreaUsuario !== 'Gerencia de Recursos Humanos') {
        filtroAreaDiv.style.display = 'none';
    }

    // (Opcional) Puedes imprimir para verificar
    console.log('Área del usuario:', nombreAreaUsuario);
});



// --- FUNCIONES MODALES ---
function abrirModal() {
    document.getElementById("PracticanteModal").style.display = "flex";
}

function cerrarModalPracticante() {
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

// Cargar áreas para filtro
async function cargarAreasFiltro() {
    try {
        const response = await api.listarAreas();
        const data = response;
        console.log("Esta es la data: ", data);
        const select = document.getElementById('filtroArea');
        const selectEnvio = document.getElementById('areaDestino');
        
        data.data.forEach(area => {
            const option = document.createElement('option');
            option.value = area.AreaID;
            option.textContent = area.NombreArea;
            select.appendChild(option.cloneNode(true));
            if(selectEnvio) selectEnvio.appendChild(option);
        });
    } catch (error) {
        console.error('Error al cargar áreas:', error);
    }
}

// Aplicar filtros
document.getElementById('btnAplicarFiltros')?.addEventListener('click', async () => {
    const nombre = document.getElementById('filtroNombre').value || '';
    const areaID = document.getElementById('filtroArea').value;
    console.log("Nombre del practicante: ", nombre);
    console.log("Area del practicante: ", areaID);
    
    try {
        const params = new URLSearchParams();
        if(nombre) params.append('nombre', nombre);
        if(areaID) params.append('areaID', areaID);
        
        const response = await api.filtrarPracticantes(nombre, areaID);
        const data = response;
        console.log(data);
        
        if(data.success) {
            actualizarTablaPracticantes(data.data);
        }
    } catch (error) {
        console.error('Error al filtrar:', error);
    }
});

// Actualizar tabla de practicantes con el mismo filtrado que cargarPracticantes
function actualizarTablaPracticantes(practicantes) {
    const tbody = document.querySelector('#tablaPracticantes tbody');
    if (!tbody) {
        console.error('❌ No se encontró el tbody de la tabla de practicantes.');
        return;
    }

    tbody.innerHTML = '';

    if (!practicantes || practicantes.length === 0) {
        tbody.innerHTML = `<tr><td colspan="8" class="text-center">No hay practicantes registrados</td></tr>`;
        return;
    }

    // Obtener datos del usuario
    const nombreAreaUsuario = sessionStorage.getItem('nombreArea');
    const esRRHH = nombreAreaUsuario === 'Gerencia de Recursos Humanos';

    // Filtrar practicantes según el área si NO es RRHH
    const practicantesFiltrados = esRRHH
        ? practicantes
        : practicantes.filter(p => {
            const areaPracticante = p.NombreArea || p.Area;
            return areaPracticante === nombreAreaUsuario;
        });

    if (practicantesFiltrados.length === 0) {
        tbody.innerHTML = `<tr><td colspan="8" class="text-center">No hay practicantes registrados para tu área</td></tr>`;
        return;
    }

    practicantesFiltrados.forEach(p => {
        const fila = document.createElement('tr');

        // Normalizar nombres y propiedades
        const estadoDescripcion = p.EstadoDescripcion || p.Estado || 'Pendiente';
        const areaNombre = p.NombreArea || p.Area || '-';
        const nombreCompleto = p.NombreCompleto || `${p.Nombres || ''} ${p.ApellidoPaterno || ''} ${p.ApellidoMaterno || ''}`.trim();

        // Badge de estado
        const estadoClass = estadoDescripcion.toLowerCase();
        const estadoBadge = `<span class="status-badge status-${estadoClass}">${estadoDescripcion.toUpperCase()}</span>`;

        // Mostrar botón de aceptar solo si pertenece al área del usuario y es “Pendiente”
        const mostrarBotonAceptar = !esRRHH && areaNombre === nombreAreaUsuario && estadoDescripcion === 'Pendiente';

        // Construir fila
        fila.innerHTML = `
            <td>${p.PracticanteID}</td>
            <td>${p.DNI}</td>
            <td>${nombreCompleto}</td>
            <td>${p.Carrera || '-'}</td>
            <td>${p.Universidad || '-'}</td>
            <td>${p.FechaRegistro ? new Date(p.FechaRegistro).toLocaleDateString() : '-'}</td>
            <td>${areaNombre}</td>
            <td>${estadoBadge}</td>
            <td>
                <button class="btn-primary" title="Editar">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn-success" title="Ver">
                    <i class="fas fa-eye"></i>
                </button>
                ${mostrarBotonAceptar ? `
                    <button class="btn-warning" title="Aceptar">
                        <i class="fas fa-check"></i>
                    </button>` : ''}
                <button class="btn-danger" title="Eliminar">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;

        tbody.appendChild(fila);

        // Asignar eventos
        fila.querySelector('.btn-primary').addEventListener('click', () => abrirModalEditarPracticante(p.PracticanteID));
        fila.querySelector('.btn-success').addEventListener('click', () => verPracticante(p.PracticanteID));
        fila.querySelector('.btn-danger').addEventListener('click', () => eliminarPracticante(p.PracticanteID));
        if (mostrarBotonAceptar) {
            fila.querySelector('.btn-warning').addEventListener('click', () => abrirModalAceptar(p.PracticanteID));
        }
    });
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
            alert(modoEdicion ? "Practicante actualizado correctamente" : "Practicante creado con éxito");

            cerrarModalPracticante();
            await cargarPracticantes();

            location.reload();

        } else {
            alert("❌ Error: " + res.message);
        }
    } catch (error) {
        console.error("Error en formulario:", error);
        alert("Error en la operación");
    }
});



// Metodo para ver informacion del Practicante
async function verPracticante(id) {
    try {
        const res = await api.getPracticante(id);
        const p = res.data;

        // Crear un modal personalizado
        const modalHTML = `
            <div id="modalVerPracticante" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: flex; justify-content: center; align-items: center; z-index: 9999;">
                <div style="background: white; border-radius: 16px; max-width: 700px; width: 90%; max-height: 90vh; overflow-y: auto; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
                    <div style="background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); padding: 20px; border-radius: 16px 16px 0 0; color: white;">
                        <h3 style="margin: 0; font-size: 1.5rem;">Detalles del Practicante</h3>
                    </div>
                    
                    <div style="padding: 30px;">
                        <h4 style="text-align: center; color: #0f172a; font-size: 1.3rem; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #3b82f6;">
                            ${p.Nombres} ${p.ApellidoPaterno} ${p.ApellidoMaterno}
                        </h4>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <p style="margin: 8px 0;"><strong style="color: #1e40af;">DNI:</strong> ${p.DNI}</p>
                            <p style="margin: 8px 0;"><strong style="color: #1e40af;">Carrera:</strong> ${p.Carrera}</p>
                            <p style="margin: 8px 0;"><strong style="color: #1e40af;">Universidad:</strong> ${p.Universidad}</p>
                            <p style="margin: 8px 0;"><strong style="color: #1e40af;">Área:</strong> ${p.Area}</p>
                            <p style="margin: 8px 0;"><strong style="color: #1e40af;">Dirección:</strong> ${p.Direccion}</p>
                            <p style="margin: 8px 0;"><strong style="color: #1e40af;">Teléfono:</strong> ${p.Telefono}</p>
                            <p style="margin: 8px 0; grid-column: 1 / -1;"><strong style="color: #1e40af;">Email:</strong> ${p.Email}</p>
                            <p style="margin: 8px 0;"><strong style="color: #1e40af;">Fecha Registro:</strong> ${p.FechaRegistro}</p>
                            <p style="margin: 8px 0;"><strong style="color: #1e40af;">Fecha Entrada:</strong> ${p.FechaEntrada}</p>
                            <p style="margin: 8px 0;"><strong style="color: #1e40af;">Fecha Salida:</strong> ${p.FechaSalida}</p>
                            <p style="margin: 8px 0; grid-column: 1 / -1; text-align: center;">
                                <strong style="color: #1e40af;">Estado:</strong> 
                                <span style="padding: 6px 16px; border-radius: 20px; background: ${p.Estado === 'Activo' ? '#dcfce7' : '#fee2e2'}; color: ${p.Estado === 'Activo' ? '#166534' : '#991b1b'}; font-weight: 600; display: inline-block; margin-left: 8px;">
                                    ${p.Estado}
                                </span>
                            </p>
                        </div>
                        
                        <div style="text-align: center; margin-top: 25px;">
                            <button onclick="document.getElementById('modalVerPracticante').remove()" style="background: #3b82f6; color: white; border: none; padding: 12px 30px; border-radius: 8px; font-size: 1rem; cursor: pointer; transition: background 0.3s;">
                                Cerrar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Insertar el modal en el body
        document.body.insertAdjacentHTML('beforeend', modalHTML);

        // Cerrar al hacer clic fuera del modal
        document.getElementById('modalVerPracticante').addEventListener('click', function(e) {
            if (e.target === this) {
                this.remove();
            }
        });

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
// Cargar practicantes desde el backend y renderizar tabla
async function cargarPracticantes() {
    try {
        const response = await api.getPracticantes();
        const practicantes = response.data || [];
        console.log('Data de practicantes: ', response.data);

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

        // Obtener datos del usuario en sesión
        const nombreAreaUsuario = sessionStorage.getItem('nombreArea');
        const esRRHH = nombreAreaUsuario === 'Gerencia de Recursos Humanos';

        // Filtrar practicantes según el área si NO es RRHH
        const practicantesFiltrados = esRRHH
            ? practicantes
            : practicantes.filter(p => {
                const areaPracticante = p.NombreArea || p.Area;
                return areaPracticante === nombreAreaUsuario;
            });

        if (practicantesFiltrados.length === 0) {
            tbody.innerHTML = `<tr><td colspan="8" class="text-center">No hay practicantes registrados para tu área</td></tr>`;
            return;
        }

        practicantesFiltrados.forEach(p => {
            const fila = document.createElement('tr');

            // Normalizar nombres y propiedades
            const estadoDescripcion = p.EstadoDescripcion || p.Estado || 'Pendiente';
            const areaNombre = p.NombreArea || p.Area || '-';
            const nombreCompleto = p.NombreCompleto || `${p.Nombres || ''} ${p.ApellidoPaterno || ''} ${p.ApellidoMaterno || ''}`.trim();

            // Badge de estado
            const estadoClass = estadoDescripcion.toLowerCase();
            const estadoBadge = `<span class="status-badge status-${estadoClass}">${estadoDescripcion.toUpperCase()}</span>`;

            console.log(esRRHH,
                         areaNombre, 
                         nombreAreaUsuario,
                         estadoDescripcion);
            // Mostrar botón de aceptar solo si pertenece al área del usuario y es “Pendiente”
            let mostrarBotonAceptar;
            if (esRRHH) {
                mostrarBotonAceptar = esRRHH && areaNombre === nombreAreaUsuario && estadoDescripcion === 'Pendiente';
            } else {
                mostrarBotonAceptar = areaNombre === nombreAreaUsuario && estadoDescripcion === 'Pendiente';
            }
            // Construir fila
            fila.innerHTML = `
                <td>${p.PracticanteID}</td>
                <td>${p.DNI}</td>
                <td>${nombreCompleto}</td>
                <td>${p.Carrera || '-'}</td>
                <td>${p.Universidad || '-'}</td>
                <td>${p.FechaRegistro ? new Date(p.FechaRegistro).toLocaleDateString() : '-'}</td>
                <td>${areaNombre}</td>
                <td>${estadoBadge}</td>
                <td>
                    <button class="btn-primary" title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn-success" title="Ver">
                        <i class="fas fa-eye"></i>
                    </button>
                    ${mostrarBotonAceptar ? `
                        <button class="btn-warning" title="Aceptar">
                            <i class="fas fa-check"></i>
                        </button>` : ''}
                    <button class="btn-danger" title="Eliminar">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;

            tbody.appendChild(fila);

            // Asignar eventos
            fila.querySelector('.btn-primary').addEventListener('click', () => abrirModalEditarPracticante(p.PracticanteID));
            fila.querySelector('.btn-success').addEventListener('click', () => verPracticante(p.PracticanteID));
            fila.querySelector('.btn-danger').addEventListener('click', () => eliminarPracticante(p.PracticanteID));
            if (mostrarBotonAceptar) {
                fila.querySelector('.btn-warning').addEventListener('click', () => abrirModalAceptar(p.PracticanteID));
            }
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