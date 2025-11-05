// ===================================== Documentos ====================================================
document.addEventListener("DOMContentLoaded", async () => {
    const selectPracticanteDoc = document.getElementById("selectPracticanteDoc");
    const selectPracticanteModal = document.getElementById("practicanteDocumento");
    const listaDocumentos = document.getElementById("listaDocumentos");
    const contenedorDocumentos = document.getElementById("contenedorDocumentos");
    const btnGuardar = document.getElementById("btnGuardarDocumentos");

    let solicitudIDActual = null;
    const tiposDocumento = ['cv', 'dni', 'carnet_vacunacion', 'carta_presentacion'];

    // 🆕 Cargar áreas para el modal de envío de solicitud
    //await cargarAreasParaSolicitud();

    // 🔹 Cargar practicantes en ambos select
    try {
        const practicantes = await api.listarNombrePracticantes();

        if (!practicantes || !Array.isArray(practicantes)) {
            console.warn("La respuesta de la API no es un array válido de practicantes.");
            return; 
        }

        practicantes.forEach(p => {
            const option1 = new Option(p.NombreCompleto, p.PracticanteID);
            const option2 = new Option(p.NombreCompleto, p.PracticanteID);
            selectPracticanteDoc.add(option1);
            selectPracticanteModal.add(option2);
        });

    } catch (err) {
        console.error("Error cargando practicantes:", err);
    }

    // 🔹 Botón abrir modal
    document.getElementById("btnSubirDocumento").addEventListener("click", () => {
        openModal("modalSubirDocumento");
    });

    // 🔹 Cuando se selecciona practicante en el modal
    selectPracticanteModal.addEventListener("change", async (e) => {
        const practicanteID = e.target.value;
        
        if (!practicanteID) {
            contenedorDocumentos.style.display = "none";
            btnGuardar.style.display = "none";
            return;
        }

        // Obtener o crear solicitud
        try {
            const result = await api.getPracticante(practicanteID);
            
            if (result.success && result.data && result.data.SolicitudID) {
                solicitudIDActual = result.data.SolicitudID;
            } else {
                // Crear nueva solicitud
                const crearResponse = await api.crearSolicitud(practicanteID);
                if (!crearResponse.ok) throw new Error(`Error HTTP: ${crearResponse.status}`);

                const crearResult = await crearResponse.json();
                if (!crearResult.success) throw new Error("Error al crear solicitud");

                solicitudIDActual = crearResult.solicitudID;
            }

            document.getElementById("solicitudID").value = solicitudIDActual;
            window.solicitudActualID = solicitudIDActual;
            
            // Cargar documentos existentes
            await cargarDocumentosExistentes(practicanteID);
            
            contenedorDocumentos.style.display = "block";
            btnGuardar.style.display = "inline-block";

        } catch (error) {
            console.error("❌ Error al obtener/crear solicitud:", error);
            alert("Error al procesar la solicitud del practicante");
        }
    });

    // 🔹 Cargar documentos existentes en los previews
    async function cargarDocumentosExistentes(practicanteID) {
        for (const tipo of tiposDocumento) {
            try {
                const result = await api.obtenerDocumentoPorTipoYPracticante(practicanteID, tipo);
                const previewDiv = document.getElementById(`preview_${tipo}`);
                
                if (result.success && result.data) {
                    previewDiv.innerHTML = `
                        <div class="archivo-actual">
                            <span>
                                <i class="fas fa-check-circle" style="color: green;"></i>
                                Documento subido (${result.data.FechaSubida})
                            </span>
                            <div class="btn-group">
                                <button type="button" class="btn-view" onclick="verDocumento('${result.data.Archivo}')">
                                    <i class="fas fa-eye"></i> Ver
                                </button>
                                <button type="button" class="btn-delete" onclick="eliminarDocumentoModal(${result.data.DocumentoID}, '${tipo}', ${practicanteID})">
                                    <i class="fas fa-trash"></i> Eliminar
                                </button>
                            </div>
                        </div>
                    `;
                } else {
                    previewDiv.innerHTML = "";
                }
            } catch (err) {
                console.error(`Error al cargar documento ${tipo}:`, err);
            }
        }
    }

    // 🔹 Detectar cambios en archivos
    tiposDocumento.forEach(tipo => {
        const input = document.getElementById(`archivo_${tipo}`);
        const preview = document.getElementById(`preview_${tipo}`);
        
        if (input) {
            input.addEventListener("change", () => {
                if (input.files.length > 0) {
                    const fileName = input.files[0].name;
                    const existingPreview = preview.querySelector('.archivo-actual');
                    
                    if (existingPreview) {
                        preview.innerHTML = `
                            ${existingPreview.outerHTML}
                            <div style="color: orange; padding: 5px; margin-top: 5px;">
                                <i class="fas fa-file"></i> Se reemplazará con: ${fileName}
                            </div>
                        `;
                    } else {
                        preview.innerHTML = `
                            <div style="color: #4CAF50; padding: 5px;">
                                <i class="fas fa-file"></i> Nuevo archivo: ${fileName}
                            </div>
                        `;
                    }
                }
            });
        }
    });

    // 🔹 Enviar formulario de documentos
    document.getElementById("formSubirDocumentos").addEventListener("submit", async (e) => {
        e.preventDefault();

        const practicanteID = selectPracticanteModal.value;
        if (!practicanteID) {
            alert("Por favor selecciona un practicante");
            return;
        }

        const btn = document.getElementById("btnGuardarDocumentos");
        
        try {
            await ejecutarUnaVez(btn, async () => {
                const observaciones = document.getElementById("observacionesGenerales").value;
                let documentosSubidos = 0;
                
                // Subir cada documento que tenga archivo seleccionado
                for (const tipo of tiposDocumento) {
                    const input = document.getElementById(`archivo_${tipo}`);
                    
                    if (input && input.files.length > 0) {
                        const formData = new FormData();
                        formData.append('solicitudID', solicitudIDActual);
                        formData.append('tipoDocumento', tipo);
                        formData.append('archivoDocumento', input.files[0]);
                        formData.append('observacionesDoc', observaciones);
                        formData.append('practicanteID', practicanteID);

                        // Verificar si existe documento previo
                        const existente = await api.obtenerDocumentoPorTipoYPracticante(practicanteID, tipo);
                        
                        let response;
                        if (existente.success && existente.data) {
                            response = await api.actualizarDocumento(formData);
                        } else {
                            response = await api.subirDocumento(formData);
                        }

                        if (!response.ok) {
                            throw new Error(`Error al subir ${tipo}`);
                        }
                        
                        documentosSubidos++;
                    }
                }
                
                if (documentosSubidos === 0) {
                    throw new Error("No se seleccionó ningún documento para subir");
                }
            });

            alert("Documentos guardados correctamente");
            
            // Recargar previews y limpiar inputs
            await cargarDocumentosExistentes(practicanteID);
            
            tiposDocumento.forEach(tipo => {
                const input = document.getElementById(`archivo_${tipo}`);
                if (input) input.value = "";
            });
            document.getElementById("observacionesGenerales").value = "";

            // Actualizar lista si está seleccionado el mismo practicante
            if (selectPracticanteDoc.value === practicanteID) {
                const documentos = await getDocumentosPorPracticante(practicanteID);
                await renderDocumentos(documentos, solicitudIDActual);
            }
            closeModal("modalSubirDocumento");

        } catch (err) {
            console.error("❌ Error al guardar documentos:", err);
            alert("Error al guardar los documentos: " + err.message);
        }
    });

    // 🔹 Cuando se selecciona practicante en la vista de lista
    selectPracticanteDoc.addEventListener("change", async () => {
        const id = selectPracticanteDoc.value;
        if (!id) {
            listaDocumentos.innerHTML = "<p>Seleccione un practicante...</p>";
            solicitudIDActual = null;
            return;
        }

        try {
            const result = await api.getPracticante(id);
            
            if (result.success && result.data && result.data.SolicitudID) {
                solicitudIDActual = result.data.SolicitudID;
            } else {
                solicitudIDActual = null;
            }
        } catch (error) {
            console.error("Error al obtener solicitud:", error);
            solicitudIDActual = null;
        }

        const documentos = await getDocumentosPorPracticante(id);
        await renderDocumentos(documentos, solicitudIDActual);
    });
});

// 🔹 Ver documento
window.verDocumento = function(base64) {
    const tipoMime = base64.startsWith("JVBER") ? "application/pdf"
                    : base64.startsWith("/9j/") ? "image/jpeg"
                    : base64.startsWith("iVBOR") ? "image/png"
                    : "application/octet-stream";
    
    const blob = b64toBlob(base64, tipoMime);
    const url = URL.createObjectURL(blob);
    window.open(url, "_blank");
};

// 🔹 Eliminar documento desde el modal
window.eliminarDocumentoModal = async function(documentoID, tipo, practicanteID) {
    if (!confirm("¿Está seguro de eliminar este documento?")) return;
    
    try {
        const response = await api.eliminarDocumento(documentoID);
        
        if (response.success) {
            alert("Documento eliminado correctamente");
            
            // Recargar preview en el modal
            const previewDiv = document.getElementById(`preview_${tipo}`);
            if (previewDiv) previewDiv.innerHTML = "";
            
            // Si está en la lista, recargar
            if (document.getElementById("selectPracticanteDoc").value == practicanteID) {
                const documentos = await getDocumentosPorPracticante(practicanteID);
                await renderDocumentos(documentos, window.solicitudActualID);
            }
        } else {
            alert("Error al eliminar: " + response.message);
        }
    } catch (error) {
        console.error("Error al eliminar documento:", error);
        alert("Error al eliminar el documento");
    }
};

// 🔹 Eliminar documento desde la tabla
window.eliminarDocumento = async function(documentoID, tipo) {
    if (!confirm("¿Está seguro de eliminar este documento?")) return;
    
    try {
        const response = await api.eliminarDocumento(documentoID);
        
        if (response.success) {
            alert("Documento eliminado correctamente");
            
            // Recargar la lista
            const practicanteID = document.getElementById("selectPracticanteDoc").value;
            if (practicanteID) {
                const documentos = await getDocumentosPorPracticante(practicanteID);
                await renderDocumentos(documentos, window.solicitudActualID);
            }
        } else {
            alert("Error al eliminar: " + response.message);
        }
    } catch (error) {
        console.error("Error al eliminar documento:", error);
        alert("Error al eliminar el documento");
    }
};

// Utilidades
function b64toBlob(base64, type) {
    const byteCharacters = atob(base64);
    const byteNumbers = Array.from(byteCharacters, c => c.charCodeAt(0));
    const byteArray = new Uint8Array(byteNumbers);
    return new Blob([byteArray], { type });
}

async function cargarAreasParaSolicitud() {
    try {
        const response = await api.listarAreas();
        
        if (response.success) {
            const selectArea = document.getElementById("areaDestino");
            if (selectArea) {
                selectArea.innerHTML = '<option value="">Seleccionar área...</option>';
                response.data.forEach(area => {
                    const option = document.createElement('option');
                    option.value = area.AreaID;
                    option.textContent = area.NombreArea;
                    selectArea.appendChild(option);
                });
            }
        }
    } catch (error) {
        console.error("Error al cargar áreas:", error);
    }
}

function abrirModalEnviarSolicitud(solicitudID) {
    document.getElementById("solicitudEnvioID").value = solicitudID;
    openModal("modalEnviarSolicitud");
}

function cerrarModalEnviarSolicitud() {
    closeModal("modalEnviarSolicitud");
    document.getElementById("formEnviarSolicitud").reset();
}

document.getElementById("formEnviarSolicitud")?.addEventListener("submit", async (e) => {
    e.preventDefault();

    const btn = document.getElementById("btnEnviarSolicitud");
    const solicitudID = document.getElementById("solicitudEnvioID").value;
    const destinatarioAreaID = document.getElementById("areaDestino").value;
    const contenido = document.getElementById("mensajeSolicitud").value;
    const remitenteAreaID = sessionStorage.getItem('areaID') || 1;

    try {
        const result = await ejecutarUnaVez(btn, async () => {
            const response = await api.enviarSolicitudArea({
                solicitudID: parseInt(solicitudID),
                remitenteAreaID: parseInt(remitenteAreaID),
                destinatarioAreaID: parseInt(destinatarioAreaID),
                contenido
            });

            if (!response.success) throw new Error(response.message);
            return response;
        });

        alert('Solicitud enviada correctamente al área');
        cerrarModalEnviarSolicitud();
        location.reload();

    } catch (error) {
        console.error('❌ Error al enviar solicitud:', error);
        alert('❌ ' + (error.message || 'Error al enviar la solicitud'));
    }
});

function openModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.style.display = "flex";
        
        // Resetear el modal de documentos cuando se abre
        if (id === "modalSubirDocumento") {
            document.getElementById("practicanteDocumento").value = "";
            document.getElementById("contenedorDocumentos").style.display = "none";
            document.getElementById("btnGuardarDocumentos").style.display = "none";
            
            // Limpiar previews
            const tiposDocumento = ['cv', 'dni', 'carnet_vacunacion', 'carta_presentacion'];
            tiposDocumento.forEach(tipo => {
                const input = document.getElementById(`archivo_${tipo}`);
                const preview = document.getElementById(`preview_${tipo}`);
                if (input) input.value = "";
                if (preview) preview.innerHTML = "";
            });
            
            document.getElementById("observacionesGenerales").value = "";
        }
    }
}

function closeModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.style.display = "none";
    }
}

async function getDocumentosPorPracticante(id) {
    try {
        const data = await api.obtenerDocumentosPorPracticante(id);
        
        if (!data || !Array.isArray(data)) {
            return [];
        }

        return data;
    } catch (e) {
        console.error("Error obteniendo documentos:", e);
        return [];
    }
}

function descargarArchivo(base64, nombre) {
    let tipoMime = "application/octet-stream";
    let extension = "bin";

    if (base64.startsWith("JVBER")) {
        tipoMime = "application/pdf";
        extension = "pdf";
    } else if (base64.startsWith("UEsDB")) {
        tipoMime = "application/vnd.openxmlformats-officedocument.wordprocessingml.document";
        extension = "docx";
    } else if (base64.startsWith("/9j/")) {
        tipoMime = "image/jpeg";
        extension = "jpg";
    } else if (base64.startsWith("iVBOR")) {
        tipoMime = "image/png";
        extension = "png";
    }

    const link = document.createElement("a");
    link.href = `data:${tipoMime};base64,${base64}`;
    link.download = `${nombre}.${extension}`;
    link.click();
}

async function renderDocumentos(documentos, solicitudID, forzarEnviada = false) {
    const contenedor = document.getElementById("listaDocumentos");

    if (!documentos || !Array.isArray(documentos) || documentos.length === 0) {
        contenedor.innerHTML = "<p>No hay documentos registrados.</p>";
        return;
    }

    const obligatorios = ["CV", "DNI", "Carnet_Vacunacion"];
    const normalizar = str =>
        str.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");

    const tiposSubidos = documentos.map(doc => normalizar(doc.tipo));
    const faltantes = obligatorios.filter(req =>
        !tiposSubidos.includes(normalizar(req))
    );
    const todosCompletos = faltantes.length === 0;

    let solicitudEnviada = forzarEnviada;
    let solicitudAprobada = false;
    
    if (solicitudID) {
        try {
            const estadoResponse = await api.verificarEstadoSolicitud(solicitudID);
            if (estadoResponse.success && estadoResponse.data) {
                solicitudEnviada = estadoResponse.data.enviada === true || forzarEnviada;
                solicitudAprobada = estadoResponse.data.aprobada;
            }
        } catch (error) {
            console.warn("No se pudo verificar estado de solicitud:", error);
        }
    }

    const tabla = `
        <table class="table">
            <thead>
                <tr>
                    <th>Tipo</th>
                    <th>Archivo</th>
                    <th>Importancia</th>
                    <th>Observaciones</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                ${documentos.map(doc => {
                    const obligatorio = obligatorios.some(req =>
                        normalizar(req) === normalizar(doc.tipo)
                    );

                    return `
                        <tr>
                            <td>${doc.tipo}</td>
                            <td>
                                <button class="btn-primary" 
                                        onclick="descargarArchivo('${doc.archivo}', '${doc.tipo}')">
                                    <i class="fas fa-download"></i> Descargar
                                </button>
                            </td>
                            <td style="color: ${obligatorio ? '#FF664A' : '#7575FA'}; font-weight: bold;">
                                ${obligatorio ? 'Obligatorio' : 'Opcional'}
                            </td>
                            <td>${doc.observaciones ? doc.observaciones : '-'}</td>
                            <td>
                                <button class="btn-delete" onclick="eliminarDocumento(${doc.documentoID}, '${normalizar(doc.tipo)}')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                }).join("")}
            </tbody>
        </table>

        <div class="enviar-solicitud-container" style="margin-top: 20px; text-align: center;">
            <button id="btnEnviarSolicitudArea" 
                    class="btn-info" 
                    ${todosCompletos && !solicitudEnviada ? '' : 'disabled'}
                    onclick="abrirModalEnviarSolicitud(${solicitudID})">
                <i class="fas fa-${solicitudEnviada ? 'check' : 'paper-plane'}"></i> 
                ${solicitudEnviada ? 'Solicitud Enviada' : 'Enviar Solicitud a Área'}
            </button>
            <button id="btnGenerarCarta" 
                    class="btn-success" 
                    ${solicitudAprobada ? '' : 'disabled'}
                    style="margin-left: 10px;"
                    onclick="generarCartaAceptacion(${solicitudID})">
                <i class="fas fa-file-contract"></i> Generar Carta de Aceptación
            </button>
            ${
                !todosCompletos
                ? `<p class='msg-warn'>Faltan documentos obligatorios: ${faltantes.join(", ")}</p>`
                : !solicitudEnviada
                ? "<p class='msg-ok'>Documentos completos. Ahora puede enviar la solicitud.</p>"
                : solicitudAprobada
                ? "<p class='msg-ok'>Solicitud <strong>APROBADA</strong>. Puede generar la carta de aceptación.</p>"
                : "<p class='msg-info'>Solicitud enviada. Esperando aprobación del área.</p>"
            }
        </div>
    `;

    contenedor.innerHTML = tabla;
}

function generarCartaAceptacion(solicitudID) {
    console.log("📄 Generando carta para solicitud:", solicitudID);
    alert("Funcionalidad de generar carta en desarrollo");
}