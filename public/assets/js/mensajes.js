// Cargar áreas para filtro
async function cargarAreasFiltro() {
    try {
        const response = await api.listarAreas();
        const data = response;
        
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

// Abrir modal de mensajes
document.getElementById('btnMensajes')?.addEventListener('click', async () => {
    const areaID = sessionStorage.getItem('areaID'); // Asumiendo que guardas el área del usuario
    console.log(areaID);
    
    try {
        const response = await api.listarMensajes(areaID);
        const data = response;
        console.log(data);
        
        if(data.success) {
            mostrarMensajes(data.data);
            document.getElementById('modalMensajes').style.display = 'block';
        }
    } catch (error) {
        console.error('Error al cargar mensajes:', error);
    }
});

async function eliminarMensaje(mensajeID) {
    if (!confirm('¿Seguro que deseas eliminar este mensaje?')) return;

    try {
        console.log("🗑️ Eliminando mensaje:", mensajeID);
        const respuesta = await api.eliminarMensaje(mensajeID);
        console.log("🔎 Respuesta del servidor:", respuesta);

        if (respuesta.success) {
            alert(respuesta.message);
            
            // 🔄 Refrescar mensajes
            const areaID = sessionStorage.getItem('areaID');
            const response = await api.listarMensajes(areaID);
            if (response.success) mostrarMensajes(response.data);

        } else {
            alert(respuesta.message || "No se pudo eliminar el mensaje.");
        }
    } catch (error) {
        console.error("Error al eliminar mensaje:", error);
        alert("Error al eliminar mensaje.");
    }
}




function mostrarMensajes(mensajes) {
    const container = document.getElementById('listaMensajes');
    container.innerHTML = '';

    mensajes.forEach(msg => {
        const div = document.createElement('div');
        div.className = 'mensaje-item';
        div.style.cssText = 'border: 1px solid #ddd; padding: 15px; margin-bottom: 10px; border-radius: 5px; position: relative;';

        const estadoSolicitud = msg.EstadoSolicitud || 'En revisión';
        const estadoClass =
            estadoSolicitud.toLowerCase() === 'aprobado' ? 'badge-success' :
            estadoSolicitud.toLowerCase() === 'rechazado' ? 'badge-danger' :
            'badge-warning';

        div.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <strong>${msg.NombrePracticante}</strong>
                <div>
                    <span class="badge ${estadoClass}">${estadoSolicitud}</span>
                    <button class="btn-eliminar" title="Eliminar mensaje" onclick="eliminarMensaje(${msg.MensajeID})">
                        🗑️
                    </button>
                </div>
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
/*document.getElementById('formEnviarSolicitud')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const solicitudID = document.getElementById('solicitudEnvioID').value;
    const destinatarioAreaID = document.getElementById('areaDestino').value;
    const contenido = document.getElementById('mensajeSolicitud').value;
    const remitenteAreaID = sessionStorage.getItem('areaID'); // Área de RRHH
    
    try {
        const response = await api.enviarSolicitudArea({
            solicitudID: parseInt(solicitudID),
            remitenteAreaID: parseInt(remitenteAreaID),
            destinatarioAreaID: parseInt(destinatarioAreaID),
            contenido
        });
        
        const data = response;
        
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
});*/

function cerrarModalEnviarSolicitud() {
    document.getElementById('modalEnviarSolicitud').style.display = 'none';
    document.getElementById('formEnviarSolicitud').reset();
}

function cerrarModalMensajes() {
    document.getElementById('modalMensajes').style.display = 'none';
}

// Abrir modal para aceptar/rechazar practicante
async function abrirModalAceptar(practicanteID) {

    const response = await api.obtenerSolicitudPorPracticante(practicanteID);
    const data = response;
    console.log(data.data.SolicitudID);

    document.getElementById('aceptarPracticanteID').value = practicanteID;
    document.getElementById('aceptarSolicitudID').value = data.data.SolicitudID;
    
    // Cargar información del practicante
    try {
        const response = await api.getPracticante(practicanteID);
        const data = response;
        
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
document.addEventListener('DOMContentLoaded', function() {
    const decision = document.getElementById('decisionAceptacion');
    if (!decision) return; // Si no existe, no hacemos nada

    decision.addEventListener('change', function() {
        const camposAceptacion = document.getElementById('camposAceptacion');
        const horaEntrada = document.getElementById('horaEntrada');
        const horaSalida = document.getElementById('horaSalida');
        const diasLaborales = document.getElementById('diasLaborales');

        if (!camposAceptacion || !horaEntrada || !horaSalida || !diasLaborales) {
            console.warn('⚠️ Algunos campos del formulario no existen aún en el DOM.');
            return;
        }

        if (this.value === 'aceptar') {
            camposAceptacion.style.display = 'block';
            horaEntrada.required = true;
            horaSalida.required = true;
            diasLaborales.required = true;
        } else {
            camposAceptacion.style.display = 'none';
            horaEntrada.required = false;
            horaSalida.required = false;
            diasLaborales.required = false;
        }
    });
});


// Enviar decisión sobre practicante
document.getElementById('formAceptarPracticante')?.addEventListener('submit', async (e) => {
    e.preventDefault();

    const decisionEl = document.getElementById('decisionAceptacion');
    const practicanteEl = document.getElementById('aceptarPracticanteID');
    const solicitudEl = document.getElementById('aceptarSolicitudID');
    const mensajeEl = document.getElementById('mensajeRespuesta');

    if (!decisionEl || !practicanteEl || !solicitudEl || !mensajeEl) {
        console.error('❌ Faltan elementos del formulario en el DOM.');
        alert('Error interno: faltan campos en el formulario.');
        return;
    }

    const decision = decisionEl.value;
    const practicanteID = practicanteEl.value;
    const solicitudID = solicitudEl.value;
    const mensajeRespuesta = mensajeEl.value;

    if (decision === 'aceptar') {
        const fechaEntradaEl = document.getElementById('fechaEntrada');
        const fechaSalidaEl = document.getElementById('fechaSalida');

        if (!fechaEntradaEl || !fechaSalidaEl) {
            alert('⚠️ Debes ingresar las fechas de entrada y salida.');
            return;
        }

        const fechaEntrada = fechaEntradaEl.value;
        const fechaSalida = fechaSalidaEl.value;

        if (!fechaEntrada || !fechaSalida) {
            alert('⚠️ Las fechas no pueden estar vacías.');
            return;
        }

        const areaID = sessionStorage.getItem('areaID');

        // Obtener los turnos dinámicos del contenedor
        const contenedorTurnos = document.getElementById('contenedorTurnos');
        console.log(contenedorTurnos);
        const turnos = [];

        contenedorTurnos.querySelectorAll('.turno-item').forEach(item => {
            // Seleccionar el <select> del turno
            const turnoSelect = item.querySelector('.select-turno');

            // Obtener todos los checkboxes de días seleccionados
            const diasSeleccionados = Array.from(
                item.querySelectorAll('.dias-checkboxes input[type="checkbox"]:checked')
            ).map(chk => chk.value);

            if (turnoSelect && turnoSelect.value && diasSeleccionados.length > 0) {
                turnos.push({
                    turnoID: parseInt(turnoSelect.value),
                    dias: diasSeleccionados.join(',') // Ejemplo: "Lunes,Martes,Viernes"
                });
            }
        });

        console.log(turnos);

        if (turnos.length === 0) {
            alert('⚠️ Debes asignar al menos un turno.');
            return;
        }

        try {
            const response = await api.aceptarPracticante({
                practicanteID: parseInt(practicanteID),
                solicitudID: parseInt(solicitudID),
                areaID: parseInt(areaID),
                turnos: turnos,
                fechaEntrada,
                fechaSalida,
                mensajeRespuesta
            });

            const data = response;
            if (data.success) {
                alert('✅ Practicante aceptado correctamente');
                cerrarModalAceptar();
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error al aceptar practicante');
        }

    } else if (decision === 'rechazar') {
        try {
            const response = await api.rechazarPracticante({
                practicanteID: parseInt(practicanteID),
                solicitudID: parseInt(solicitudID),
                mensajeRespuesta
            });

            const data = response;
            if (data.success) {
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
/*unction verificarDocumentosCompletos(practicanteID) {
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
}*/

// Evento para verificar documentos cuando se selecciona practicante en documentos
/*document.getElementById('selectPracticanteDoc')?.addEventListener('change', function() {
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
});*/

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
        const response = await api.listarTurnos();
        const data = response;
        
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