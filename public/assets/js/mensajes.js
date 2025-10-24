// Cargar áreas para filtro
async function cargarAreasFiltro() {
    try {
        const response = await fetch('/api/areas');
        const data = await response.json();
        
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
    const nombre = document.getElementById('filtroNombre').value;
    const areaID = document.getElementById('filtroArea').value;
    
    try {
        const params = new URLSearchParams();
        if(nombre) params.append('nombre', nombre);
        if(areaID) params.append('areaID', areaID);
        
        const response = await fetch(`/api/practicantes/filtrar?${params}`);
        const data = await response.json();
        
        if(data.success) {
            actualizarTablaPracticantes(data.data);
        }
    } catch (error) {
        console.error('Error al filtrar:', error);
    }
});

// Abrir modal de mensajes
document.getElementById('btnMensajes')?.addEventListener('click', async () => {
    const areaID = sessionStorage.getItem('areaID'); // Asumiendo que guardas el área del usuario
    
    try {
        const response = await fetch(`/api/mensajes/${areaID}`);
        const data = await response.json();
        
        if(data.success) {
            mostrarMensajes(data.data);
            document.getElementById('modalMensajes').style.display = 'block';
        }
    } catch (error) {
        console.error('Error al cargar mensajes:', error);
    }
});

function mostrarMensajes(mensajes) {
    const container = document.getElementById('listaMensajes');
    container.innerHTML = '';
    
    mensajes.forEach(msg => {
        const div = document.createElement('div');
        div.className = 'mensaje-item';
        div.style.cssText = 'border: 1px solid #ddd; padding: 15px; margin-bottom: 10px; border-radius: 5px;';
        
        const estadoClass = msg.RespuestaEstado === 'aprobado' ? 'badge-success' : 
                           msg.RespuestaEstado === 'rechazado' ? 'badge-danger' : 'badge-warning';
        
        div.innerHTML = `
            <div style="display: flex; justify-content: space-between;">
                <strong>${msg.NombrePracticante}</strong>
                <span class="badge ${estadoClass}">${msg.RespuestaEstado || 'Pendiente'}</span>
            </div>
            <p><strong>De:</strong> ${msg.AreaRemitente} <strong>Para:</strong> ${msg.AreaDestino}</p>
            <p>${msg.Contenido}</p>
            <small>${new Date(msg.FechaEnvio).toLocaleString()}</small>
            ${msg.TipoMensaje === 'solicitud' && !msg.Leido ? 
                `<button onclick="responderSolicitud(${msg.MensajeID}, ${msg.SolicitudID})" class="btn-primary">Responder</button>` : ''}
        `;
        
        container.appendChild(div);
    });
}

// Enviar solicitud a área
document.getElementById('formEnviarSolicitud')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const solicitudID = document.getElementById('solicitudEnvioID').value;
    const destinatarioAreaID = document.getElementById('areaDestino').value;
    const contenido = document.getElementById('mensajeSolicitud').value;
    const remitenteAreaID = sessionStorage.getItem('areaID'); // Área de RRHH
    
    try {
        const response = await fetch('/api/mensajes/enviar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                solicitudID,
                remitenteAreaID,
                destinatarioAreaID,
                contenido
            })
        });
        
        const data = await response.json();
        
        if(data.success) {
            alert('Solicitud enviada correctamente');
            cerrarModalEnviarSolicitud();
            // Deshabilitar botón después de enviar
            document.getElementById('btnEnviarSolicitudArea').disabled = true;
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        console.error('Error al enviar solicitud:', error);
        alert('Error al enviar la solicitud');
    }
});

function cerrarModalEnviarSolicitud() {
    document.getElementById('modalEnviarSolicitud').style.display = 'none';
    document.getElementById('formEnviarSolicitud').reset();
}

function cerrarModalMensajes() {
    document.getElementById('modalMensajes').style.display = 'none';
}

// Abrir modal para aceptar/rechazar practicante
async function abrirModalAceptar(practicanteID, solicitudID) {
    document.getElementById('aceptarPracticanteID').value = practicanteID;
    document.getElementById('aceptarSolicitudID').value = solicitudID;
    
    // Cargar información del practicante
    try {
        const response = await fetch(`/api/practicantes/${practicanteID}`);
        const data = await response.json();
        
        if(data.success) {
            const p = data.data;
            document.getElementById('infoPracticante').innerHTML = `
                <h4>${p.Nombres} ${p.ApellidoPaterno} ${p.ApellidoMaterno}</h4>
                <p><strong>DNI:</strong> ${p.DNI}</p>
                <p><strong>Carrera:</strong> ${p.Carrera}</p>
                <p><strong>Universidad:</strong> ${p.Universidad}</p>
                <p><strong>Email:</strong> ${p.Email}</p>
                <p><strong>Teléfono:</strong> ${p.Telefono}</p>
            `;
        }
    } catch (error) {
        console.error('Error al cargar practicante:', error);
    }
    
    document.getElementById('modalAceptarPracticante').style.display = 'block';
}

// Cambiar campos según decisión
document.getElementById('decisionAceptacion')?.addEventListener('change', function() {
    const camposAceptacion = document.getElementById('camposAceptacion');
    if(this.value === 'aceptar') {
        camposAceptacion.style.display = 'block';
        document.getElementById('horaEntrada').required = true;
        document.getElementById('horaSalida').required = true;
        document.getElementById('diasLaborales').required = true;
    } else {
        camposAceptacion.style.display = 'none';
        document.getElementById('horaEntrada').required = false;
        document.getElementById('horaSalida').required = false;
        document.getElementById('diasLaborales').required = false;
    }
});

// Enviar decisión sobre practicante
document.getElementById('formAceptarPracticante')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const decision = document.getElementById('decisionAceptacion').value;
    const practicanteID = document.getElementById('aceptarPracticanteID').value;
    const solicitudID = document.getElementById('aceptarSolicitudID').value;
    const mensajeRespuesta = document.getElementById('mensajeRespuesta').value;
    
    if(decision === 'aceptar') {
        const horaEntrada = document.getElementById('horaEntrada').value;
        const horaSalida = document.getElementById('horaSalida').value;
        const diasLaborales = document.getElementById('diasLaborales').value;
        const areaID = sessionStorage.getItem('areaID');
        
        try {
            const response = await fetch('/api/practicantes/aceptar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    practicanteID,
                    solicitudID,
                    areaID,
                    horaEntrada,
                    horaSalida,
                    diasLaborales,
                    mensajeRespuesta
                })
            });
            
            const data = await response.json();
            
            if(data.success) {
                alert('Practicante aceptado correctamente');
                cerrarModalAceptar();
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error al aceptar practicante');
        }
    } else if(decision === 'rechazar') {
        try {
            const response = await fetch('/api/practicantes/rechazar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    practicanteID,
                    solicitudID,
                    mensajeRespuesta
                })
            });
            
            const data = await response.json();
            
            if(data.success) {
                alert('Practicante rechazado');
                cerrarModalAceptar();
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error al rechazar practicante');
        }
    }
});

function cerrarModalAceptar() {
    document.getElementById('modalAceptarPracticante').style.display = 'none';
    document.getElementById('formAceptarPracticante').reset();
    document.getElementById('camposAceptacion').style.display = 'none';
}

// Actualizar tabla de practicantes con filtros
function actualizarTablaPracticantes(practicantes) {
    const tbody = document.querySelector('#tablaPracticantes tbody');
    tbody.innerHTML = '';
    
    const esRRHH = sessionStorage.getItem('rolArea') === 'RRHH';
    const areaUsuario = sessionStorage.getItem('areaID');
    
    practicantes.forEach(p => {
        const tr = document.createElement('tr');
        
        // Determinar si mostrar botón de aceptar
        const mostrarBotonAceptar = !esRRHH && p.AreaID == areaUsuario && p.EstadoDescripcion === 'Pendiente';
        
        tr.innerHTML = `
            <td>${p.PracticanteID}</td>
            <td>${p.DNI}</td>
            <td>${p.Nombres} ${p.ApellidoPaterno} ${p.ApellidoMaterno}</td>
            <td>${p.Carrera}</td>
            <td>${p.Universidad}</td>
            <td>${p.FechaRegistro ? new Date(p.FechaRegistro).toLocaleDateString() : '-'}</td>
            <td>${p.NombreArea || '-'}</td>
            <td><span class="badge ${getBadgeClass(p.EstadoDescripcion)}">${p.EstadoDescripcion || 'Pendiente'}</span></td>
            <td>
                <button onclick="verPracticante(${p.PracticanteID})" class="btn-info btn-sm" title="Ver">
                    <i class="fas fa-eye"></i>
                </button>
                <button onclick="editarPracticante(${p.PracticanteID})" class="btn-warning btn-sm" title="Editar">
                    <i class="fas fa-edit"></i>
                </button>
                <button onclick="eliminarPracticante(${p.PracticanteID})" class="btn-danger btn-sm" title="Eliminar">
                    <i class="fas fa-trash"></i>
                </button>
                ${mostrarBotonAceptar ? `
                    <button onclick="abrirModalAceptar(${p.PracticanteID}, ${p.SolicitudID || 0})" 
                            class="btn-success btn-sm" title="Aceptar">
                        <i class="fas fa-check"></i>
                    </button>
                ` : ''}
            </td>
        `;
        
        tbody.appendChild(tr);
    });
}

function getBadgeClass(estado) {
    switch(estado) {
        case 'Vigente': return 'badge-success';
        case 'Aprobado': return 'badge-success';
        case 'Rechazado': return 'badge-danger';
        case 'Pendiente': return 'badge-warning';
        default: return 'badge-secondary';
    }
}

// Habilitar botón de enviar solicitud cuando documentos estén completos
function verificarDocumentosCompletos(practicanteID) {
    fetch(`/api/solicitudes/documentos?practicanteID=${practicanteID}`)
        .then(res => res.json())
        .then(documentos => {
            const tiposRequeridos = ['cv', 'carnet_vacunacion', 'carta_presentacion', 'dni'];
            const tiposSubidos = documentos.map(d => d.tipo);
            
            const todosCompletos = tiposRequeridos.every(tipo => tiposSubidos.includes(tipo));
            
            if(todosCompletos) {
                document.getElementById('btnEnviarSolicitudArea').disabled = false;
                document.getElementById('btnGenerarCarta').disabled = false;
            }
        });
}

// Evento para verificar documentos cuando se selecciona practicante en documentos
document.getElementById('selectPracticanteDoc')?.addEventListener('change', function() {
    const practicanteID = this.value;
    if(practicanteID) {
        verificarDocumentosCompletos(practicanteID);
        
        // Buscar solicitudID del practicante
        fetch(`/api/solicitudes/por-practicante?practicanteID=${practicanteID}`)
            .then(res => res.json())
            .then(data => {
                if(data.success && data.data) {
                    document.getElementById('solicitudEnvioID').value = data.data.SolicitudID;
                }
            });
    } else {
        document.getElementById('btnEnviarSolicitudArea').disabled = true;
        document.getElementById('btnGenerarCarta').disabled = true;
    }
});

// Botón para abrir modal de envío de solicitud
document.getElementById('btnEnviarSolicitudArea')?.addEventListener('click', function() {
    document.getElementById('modalEnviarSolicitud').style.display = 'block';
});

// Inicializar al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    cargarAreasFiltro();
});

// Para turnos

let turnosDisponibles = [];

// Cargar turnos disponibles
async function cargarTurnos() {
    try {
        const response = await fetch('/api/turnos');
        const data = await response.json();
        
        if(data.success) {
            turnosDisponibles = data.data;
        }
    } catch (error) {
        console.error('Error al cargar turnos:', error);
    }
}

// Agregar turno al formulario
document.getElementById('btnAgregarTurno')?.addEventListener('click', function() {
    const contenedor = document.getElementById('contenedorTurnos');
    const template = document.getElementById('templateTurno');
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
    clone.querySelector('.btn-eliminar-turno').addEventListener('click', function() {
        this.closest('.turno-item').remove();
    });
    
    contenedor.appendChild(clone);
});

// Modificar la función de cambio de decisión
document.getElementById('decisionAceptacion')?.addEventListener('change', function() {
    const camposAceptacion = document.getElementById('camposAceptacion');
    const contenedorTurnos = document.getElementById('contenedorTurnos');
    
    if(this.value === 'aceptar') {
        camposAceptacion.style.display = 'block';
        // Agregar un turno por defecto si no hay ninguno
        if(contenedorTurnos.children.length === 0) {
            document.getElementById('btnAgregarTurno').click();
        }
    } else {
        camposAceptacion.style.display = 'none';
        contenedorTurnos.innerHTML = ''; // Limpiar turnos
    }
});

// Modificar el submit del formulario de aceptar practicante
document.getElementById('formAceptarPracticante')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const decision = document.getElementById('decisionAceptacion').value;
    const practicanteID = document.getElementById('aceptarPracticanteID').value;
    const solicitudID = document.getElementById('aceptarSolicitudID').value;
    const mensajeRespuesta = document.getElementById('mensajeRespuesta').value;
    
    if(decision === 'aceptar') {
        // Recolectar información de turnos
        const turnosItems = document.querySelectorAll('.turno-item');
        const turnos = [];
        
        let valido = true;
        turnosItems.forEach(item => {
            const turnoID = item.querySelector('.select-turno').value;
            const diasCheckboxes = item.querySelectorAll('.dias-checkboxes input[type="checkbox"]:checked');
            const dias = Array.from(diasCheckboxes).map(cb => cb.value).join(',');
            
            if(!turnoID || !dias) {
                valido = false;
                return;
            }
            
            turnos.push({
                turnoID: parseInt(turnoID),
                dias: dias
            });
        });
        
        if(!valido || turnos.length === 0) {
            alert('Por favor complete todos los turnos y seleccione al menos un día para cada uno');
            return;
        }
        
        const areaID = sessionStorage.getItem('areaID');
        
        try {
            const response = await fetch('/api/practicantes/aceptar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    practicanteID,
                    solicitudID,
                    areaID,
                    turnos, // Array de objetos {turnoID, dias}
                    mensajeRespuesta
                })
            });
            
            const data = await response.json();
            
            if(data.success) {
                alert('Practicante aceptado correctamente');
                cerrarModalAceptar();
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error al aceptar practicante');
        }
    } else if(decision === 'rechazar') {
        // Código de rechazo (sin cambios)
        try {
            const response = await fetch('/api/practicantes/rechazar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    practicanteID,
                    solicitudID,
                    mensajeRespuesta
                })
            });
            
            const data = await response.json();
            
            if(data.success) {
                alert('Practicante rechazado');
                cerrarModalAceptar();
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error al rechazar practicante');
        }
    }
});

function cerrarModalAceptar() {
    document.getElementById('modalAceptarPracticante').style.display = 'none';
    document.getElementById('formAceptarPracticante').reset();
    document.getElementById('camposAceptacion').style.display = 'none';
    document.getElementById('contenedorTurnos').innerHTML = '';
}

// Modificar la inicialización
document.addEventListener('DOMContentLoaded', function() {
    cargarAreasFiltro();
    cargarTurnos(); // Cargar turnos al inicio
});