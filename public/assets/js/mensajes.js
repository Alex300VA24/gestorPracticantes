// ===================================== MENSAJES Y PRACTICANTES ====================================

// 🔹 Variables globales
let turnosDisponibles = [];
const areaID = () => sessionStorage.getItem('areaID');
const esRRHH = () => sessionStorage.getItem('rolArea') === 'RRHH';

// ===================================== INICIALIZACIÓN ====================================
document.addEventListener('DOMContentLoaded', async () => {
    await inicializarModulo();
    configurarEventListeners();
});

async function inicializarModulo() {
    await cargarAreasFiltro();
    await cargarTurnos();
}

// ===================================== MENSAJES ====================================

// 🔹 Abrir modal de mensajes
document.getElementById('btnMensajes')?.addEventListener('click', async () => {
    try {
        const areaUsuario = areaID();
        console.log("Cargando mensajes para área:", areaUsuario);
        
        const response = await api.listarMensajes(areaUsuario);
        
        if (response.success) {
            mostrarMensajes(response.data);
            openModal('modalMensajes');
        } else {
            alert('Error al cargar mensajes: ' + response.message);
        }
    } catch (error) {
        console.error('❌ Error al cargar mensajes:', error);
        alert('No se pudieron cargar los mensajes');
    }
});

// 🔹 Mostrar lista de mensajes
function mostrarMensajes(mensajes) {
    const container = document.getElementById('listaMensajes');
    
    if (!mensajes || mensajes.length === 0) {
        container.innerHTML = '<p class="empty-message">No hay mensajes disponibles</p>';
        return;
    }
    
    container.innerHTML = mensajes.map(msg => crearMensajeHTML(msg)).join('');
}

// 🔹 Crear HTML de un mensaje
function crearMensajeHTML(msg) {
    const estadoSolicitud = msg.EstadoSolicitud || 'En revisión';
    const estadoClass = obtenerClaseEstado(estadoSolicitud);
    
    return `
        <div class="mensaje-item" style="border: 1px solid #ddd; padding: 15px; margin-bottom: 10px; border-radius: 5px; position: relative;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <strong>${msg.NombrePracticante}</strong>
                <div>
                    <span class="badge ${estadoClass}">${estadoSolicitud}</span>
                    <button class="btn-eliminar" 
                            title="Eliminar mensaje" 
                            onclick="eliminarMensaje(${msg.MensajeID})"
                            style="margin-left: 10px;">
                        🗑️
                    </button>
                </div>
            </div>
            <p><strong>De:</strong> ${msg.AreaRemitente} <strong>Para:</strong> ${msg.AreaDestino}</p>
            <p>${msg.Contenido}</p>
            <small>${new Date(msg.FechaEnvio).toLocaleString()}</small>
            ${msg.TipoMensaje === 'solicitud' && !msg.Leido ? 
                `<button onclick="responderSolicitud(${msg.MensajeID}, ${msg.SolicitudID})" 
                         class="btn-primary" 
                         style="margin-top: 10px;">
                    Responder
                </button>` : ''}
        </div>
    `;
}

// 🔹 Eliminar mensaje
async function eliminarMensaje(mensajeID) {
    if (!confirm('¿Seguro que deseas eliminar este mensaje?')) return;

    try {
        console.log("🗑️ Eliminando mensaje:", mensajeID);
        const respuesta = await api.eliminarMensaje(mensajeID);
        
        if (respuesta.success) {
            alert(respuesta.message);
            
            // Recargar mensajes
            const response = await api.listarMensajes(areaID());
            if (response.success) {
                mostrarMensajes(response.data);
            }
        } else {
            alert(respuesta.message || "No se pudo eliminar el mensaje.");
        }
    } catch (error) {
        console.error("❌ Error al eliminar mensaje:", error);
        alert("Error al eliminar mensaje.");
    }
}

// 🔹 Cerrar modal de mensajes
function cerrarModalMensajes() {
    closeModal('modalMensajes');
}

// ===================================== PRACTICANTES ====================================

// 🔹 Actualizar tabla de practicantes
function actualizarTablaPracticantes(practicantes) {
    const tbody = document.querySelector('#tablaPracticantes tbody');
    
    if (!tbody) {
        console.warn('No se encontró el tbody de la tabla de practicantes');
        return;
    }
    
    tbody.innerHTML = '';
    
    if (!practicantes || practicantes.length === 0) {
        tbody.innerHTML = '<tr><td colspan="9" class="text-center">No hay practicantes registrados</td></tr>';
        return;
    }
    
    const areaUsuario = areaID();
    const esAreaRRHH = esRRHH();
    
    practicantes.forEach(p => {
        const tr = document.createElement('tr');
        const mostrarBotonAceptar = !esAreaRRHH && 
                                   p.AreaID == areaUsuario && 
                                   p.EstadoDescripcion === 'Pendiente';
        
        tr.innerHTML = crearFilaPracticante(p, mostrarBotonAceptar);
        tbody.appendChild(tr);
    });
}

// 🔹 Crear HTML de fila de practicante
function crearFilaPracticante(p, mostrarBotonAceptar) {
    return `
        <td>${p.PracticanteID}</td>
        <td>${p.DNI}</td>
        <td>${p.Nombres} ${p.ApellidoPaterno} ${p.ApellidoMaterno}</td>
        <td>${p.Carrera}</td>
        <td>${p.Universidad}</td>
        <td>${p.FechaRegistro ? new Date(p.FechaRegistro).toLocaleDateString() : '-'}</td>
        <td>${p.NombreArea || '-'}</td>
        <td><span class="badge ${obtenerClaseEstado(p.EstadoDescripcion)}">${p.EstadoDescripcion || 'Pendiente'}</span></td>
        <td>
            <button onclick="verPracticante(${p.PracticanteID})" 
                    class="btn-info btn-sm" 
                    title="Ver">
                <i class="fas fa-eye"></i>
            </button>
            <button onclick="editarPracticante(${p.PracticanteID})" 
                    class="btn-warning btn-sm" 
                    title="Editar">
                <i class="fas fa-edit"></i>
            </button>
            <button onclick="eliminarPracticante(${p.PracticanteID})" 
                    class="btn-danger btn-sm" 
                    title="Eliminar">
                <i class="fas fa-trash"></i>
            </button>
            ${mostrarBotonAceptar ? `
                <button onclick="abrirModalAceptar(${p.PracticanteID})" 
                        class="btn-success btn-sm" 
                        title="Aceptar/Rechazar">
                    <i class="fas fa-check"></i>
                </button>
            ` : ''}
        </td>
    `;
}

// ===================================== MODAL ACEPTAR/RECHAZAR ====================================

// 🔹 Abrir modal para aceptar/rechazar practicante
async function abrirModalAceptar(practicanteID) {
    try {
        // Obtener solicitud del practicante
        const responseSolicitud = await api.obtenerSolicitudPorPracticante(practicanteID);
        
        if (!responseSolicitud.success || !responseSolicitud.data) {
            alert('No se pudo obtener la solicitud del practicante');
            return;
        }
        
        const solicitudID = responseSolicitud.data.SolicitudID;
        
        // Asignar valores a los campos ocultos
        document.getElementById('aceptarPracticanteID').value = practicanteID;
        document.getElementById('aceptarSolicitudID').value = solicitudID;
        
        // Cargar información del practicante
        const responsePracticante = await api.getPracticante(practicanteID);
        
        if (responsePracticante.success) {
            mostrarInfoPracticante(responsePracticante.data);
        }
        
        // Abrir modal
        openModal('modalAceptarPracticante');
        
    } catch (error) {
        console.error('❌ Error al abrir modal:', error);
        alert('Error al cargar información del practicante');
    }
}

// 🔹 Mostrar información del practicante
function mostrarInfoPracticante(p) {
    const infoContainer = document.getElementById('infoPracticante');
    
    if (!infoContainer) {
        console.warn('No se encontró el contenedor de información del practicante');
        return;
    }
    
    infoContainer.innerHTML = `
        <h4>${p.Nombres} ${p.ApellidoPaterno} ${p.ApellidoMaterno}</h4>
        <p><strong>DNI:</strong> ${p.DNI}</p>
        <p><strong>Carrera:</strong> ${p.Carrera}</p>
        <p><strong>Universidad:</strong> ${p.Universidad}</p>
        <p><strong>Email:</strong> ${p.Email}</p>
        <p><strong>Teléfono:</strong> ${p.Telefono}</p>
    `;
}

// 🔹 Cerrar modal de aceptar
function cerrarModalAceptar() {
    closeModal('modalAceptarPracticante');
    document.getElementById('formAceptarPracticante')?.reset();
    document.getElementById('camposAceptacion').style.display = 'none';
    document.getElementById('contenedorTurnos').innerHTML = '';
}

// ===================================== TURNOS ====================================

// 🔹 Cargar turnos disponibles
async function cargarTurnos() {
    try {
        const response = await api.listarTurnos();
        
        if (response.success) {
            turnosDisponibles = response.data;
            console.log("Turnos cargados:", turnosDisponibles.length);
        } else {
            console.warn('No se pudieron cargar los turnos');
        }
    } catch (error) {
        console.error('Error al cargar turnos:', error);
    }
}

// 🔹 Agregar turno al formulario
function agregarTurno() {
    const contenedor = document.getElementById('contenedorTurnos');
    const template = document.getElementById('templateTurno');
    
    if (!contenedor || !template) {
        console.error('❌ No se encontró el contenedor de turnos o el template');
        return;
    }
    
    const clone = template.content.cloneNode(true);
    
    // Llenar select de turnos
    const select = clone.querySelector('.select-turno');
    turnosDisponibles.forEach(turno => {
        const option = document.createElement('option');
        option.value = turno.TurnoID;
        option.textContent = `${turno.Descripcion} (${turno.RangoHorario})`;
        select.appendChild(option);
    });
    
    // Agregar evento para eliminar
    const btnEliminar = clone.querySelector('.btn-eliminar-turno');
    if (btnEliminar) {
        btnEliminar.addEventListener('click', function() {
            this.closest('.turno-item').remove();
        });
    }
    
    contenedor.appendChild(clone);
    console.log("➕ Turno agregado. Total turnos:", contenedor.children.length);
}

// 🔹 Obtener turnos seleccionados del formulario
function obtenerTurnosSeleccionados() {
    const contenedorTurnos = document.getElementById('contenedorTurnos');
    const turnos = [];

    contenedorTurnos.querySelectorAll('.turno-item').forEach(item => {
        const turnoSelect = item.querySelector('.select-turno');
        const diasSeleccionados = Array.from(
            item.querySelectorAll('.dias-checkboxes input[type="checkbox"]:checked')
        ).map(chk => chk.value);

        if (turnoSelect && turnoSelect.value && diasSeleccionados.length > 0) {
            turnos.push({
                turnoID: parseInt(turnoSelect.value),
                dias: diasSeleccionados.join(',')
            });
        }
    });

    return turnos;
}

// ===================================== FORMULARIO ACEPTAR/RECHAZAR ====================================

// 🔹 Manejar cambio de decisión (aceptar/rechazar)
function manejarCambioDecision() {
    const decision = document.getElementById('decisionAceptacion');
    const camposAceptacion = document.getElementById('camposAceptacion');
    const contenedorTurnos = document.getElementById('contenedorTurnos');
    
    if (!decision || !camposAceptacion) return;

    if (decision.value === 'aceptar') {
        camposAceptacion.style.display = 'block';
        
        // Establecer campos como requeridos
        ['fechaEntrada', 'fechaSalida'].forEach(id => {
            const campo = document.getElementById(id);
            if (campo) campo.required = true;
        });
        
        // Agregar un turno por defecto si no hay ninguno
        if (contenedorTurnos && contenedorTurnos.children.length === 0) {
            agregarTurno();
        }
    } else {
        camposAceptacion.style.display = 'none';
        
        // Quitar campos requeridos
        ['fechaEntrada', 'fechaSalida'].forEach(id => {
            const campo = document.getElementById(id);
            if (campo) campo.required = false;
        });
        
        // Limpiar turnos
        if (contenedorTurnos) {
            contenedorTurnos.innerHTML = '';
        }
    }
}

// 🔹 Procesar aceptación de practicante
async function procesarAceptacion(practicanteID, solicitudID, mensajeRespuesta) {
    const fechaEntrada = document.getElementById('fechaEntrada')?.value;
    const fechaSalida = document.getElementById('fechaSalida')?.value;

    if (!fechaEntrada || !fechaSalida) {
        alert('Debes ingresar las fechas de entrada y salida.');
        return false;
    }

    const turnos = obtenerTurnosSeleccionados();

    if (turnos.length === 0) {
        alert('Debes asignar al menos un turno.');
        return false;
    }

    console.log("📤 Datos de aceptación:", {
        practicanteID,
        solicitudID,
        turnos,
        fechaEntrada,
        fechaSalida
    });

    const btn = document.getElementById("btnEnviarRespuesta");

    try {
        const result = await ejecutarUnaVez(btn, async () => {
            const response = await api.aceptarPracticante({
                practicanteID: parseInt(practicanteID),
                solicitudID: parseInt(solicitudID),
                areaID: parseInt(areaID()),
                turnos: turnos,
                fechaEntrada,
                fechaSalida,
                mensajeRespuesta
            });
            if (!response.success) throw new Error(response.message || "Error al aceptar solicitud");
            return response;
        });

        if (result.success) {
            alert('Practicante aceptado correctamente');
            cerrarModalAceptar();
            location.reload();
            return true;
        } else {
            alert('Error: ' + response.message);
            return false;
        }
    } catch (error) {
        console.error('Error al aceptar practicante:', error);
        alert('Error al aceptar practicante');
        return false;
    }
}

// 🔹 Procesar rechazo de practicante
async function procesarRechazo(practicanteID, solicitudID, mensajeRespuesta) {
    const btn = document.getElementById("btnEnviarRespuesta");
    try {
        const result = await ejecutarUnaVez(btn, async () => {
            const response = await api.rechazarPracticante({
                practicanteID: parseInt(practicanteID),
                solicitudID: parseInt(solicitudID),
                mensajeRespuesta
            });
            if (!response.success) throw new Error(response.message || "Error al recharzar solicitud");
            return response;
        });

        if (result.success) {
            alert('Practicante rechazado');
            cerrarModalAceptar();
            location.reload();
            return true;
        } else {
            alert('❌ Error: ' + response.message);
            return false;
        }
    } catch (error) {
        console.error('❌ Error al rechazar practicante:', error);
        alert('Error al rechazar practicante');
        return false;
    }
}

// ===================================== EVENT LISTENERS ====================================

function configurarEventListeners() {
    // Botón agregar turno
    const btnAgregarTurno = document.getElementById('btnAgregarTurno');
    if (btnAgregarTurno) {
        btnAgregarTurno.addEventListener('click', agregarTurno);
    }
    
    // Select de decisión
    const decisionSelect = document.getElementById('decisionAceptacion');
    if (decisionSelect) {
        decisionSelect.addEventListener('change', manejarCambioDecision);
    }
    
    // Formulario de aceptar/rechazar
    const formAceptar = document.getElementById('formAceptarPracticante');
    if (formAceptar) {
        formAceptar.addEventListener('submit', manejarSubmitAceptar);
    }
    
    // Botón enviar solicitud a área
    const btnEnviarSolicitud = document.getElementById('btnEnviarSolicitudArea');
    if (btnEnviarSolicitud) {
        btnEnviarSolicitud.addEventListener('click', () => {
            openModal('modalEnviarSolicitud');
        });
    }
}

// 🔹 Manejar submit del formulario de aceptar/rechazar
async function manejarSubmitAceptar(e) {
    e.preventDefault();

    const decision = document.getElementById('decisionAceptacion')?.value;
    const practicanteID = document.getElementById('aceptarPracticanteID')?.value;
    const solicitudID = document.getElementById('aceptarSolicitudID')?.value;
    const mensajeRespuesta = document.getElementById('mensajeRespuesta')?.value;

    if (!decision || !practicanteID || !solicitudID) {
        alert('❌ Faltan datos en el formulario');
        return;
    }

    if (decision === 'aceptar') {
        await procesarAceptacion(practicanteID, solicitudID, mensajeRespuesta);
    } else if (decision === 'rechazar') {
        await procesarRechazo(practicanteID, solicitudID, mensajeRespuesta);
    }
}

// ===================================== UTILIDADES ====================================

// 🔹 Obtener clase CSS según estado
function obtenerClaseEstado(estado) {
    const estados = {
        'Vigente': 'badge-success',
        'Aprobado': 'badge-success',
        'Rechazado': 'badge-danger',
        'Pendiente': 'badge-warning',
        'En revisión': 'badge-warning'
    };
    return estados[estado] || 'badge-secondary';
}

// 🔹 Abrir modal genérico
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex';
    }
}

// 🔹 Cerrar modal genérico
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
    }
}

// 🔹 Cerrar modal de enviar solicitud
function cerrarModalEnviarSolicitud() {
    closeModal('modalEnviarSolicitud');
    document.getElementById('formEnviarSolicitud')?.reset();
}

// ===================================== FUNCIONES GLOBALES ====================================
// Estas funciones deben estar disponibles globalmente para los onclick en HTML

window.eliminarMensaje = eliminarMensaje;
window.abrirModalAceptar = abrirModalAceptar;
window.cerrarModalAceptar = cerrarModalAceptar;
window.cerrarModalMensajes = cerrarModalMensajes;
window.cerrarModalEnviarSolicitud = cerrarModalEnviarSolicitud;
window.actualizarTablaPracticantes = actualizarTablaPracticantes;