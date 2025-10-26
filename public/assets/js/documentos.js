// ===================================== Documentos ====================================================

document.addEventListener("DOMContentLoaded", async () => {
    const selectPracticante = document.getElementById("selectPracticanteDoc");
    const listaDocumentos = document.getElementById("listaDocumentos");
    const selectPracticanteModal = document.getElementById("practicanteDocumento");
    const selectTipoDocumento = document.getElementById("tipoDocumento");
    const inputArchivo = document.getElementById("archivoDocumento");
    const textareaObs = document.getElementById("observacionesDoc");
    const contenedorArchivoActual = document.createElement("div");
    inputArchivo.parentElement.appendChild(contenedorArchivoActual);

    let archivoExistente = null;
    let existeDocumento = false;

    // 🆕 Cargar áreas para el modal de envío de solicitud
    await cargarAreasParaSolicitud();

    // 🔹 Verificar si existe documento previo
    async function verificarDocumentoExistente() {
        const practicanteID = document.getElementById("practicanteDocumento").value;
        const tipoDocumento = document.getElementById("tipoDocumento").value;
        const contenedorArchivoActual = document.getElementById("contenedorArchivoActual");
        const textareaObs = document.getElementById("observacionesDoc");

        if (!practicanteID || !tipoDocumento) return;

        try {
            const result = await api.obtenerDocumentoPorTipoYPracticante(practicanteID, tipoDocumento);
            console.log("📁 Documento existente:", result);

            if (result.success && result.data) {
                existeDocumento = true;
                document.getElementById("solicitudID").value = result.data.SolicitudID;
                textareaObs.value = result.data.Observaciones || "";

                contenedorArchivoActual.innerHTML = `
                    <div class="archivo-actual">
                        <p><strong>Documento actual:</strong> ${tipoDocumento.toUpperCase()} (${result.data.FechaSubida})</p>
                        <button type="button" class="btn-view" id="btnVerDocumento">
                            <i class="fas fa-eye"></i> Ver Documento
                        </button>
                    </div>
                `;

                document.getElementById("btnVerDocumento").addEventListener("click", () => {
                    const base64 = result.data.Archivo;
                    const tipoMime = base64.startsWith("JVBER") ? "application/pdf"
                                    : base64.startsWith("/9j/") ? "image/jpeg"
                                    : base64.startsWith("iVBOR") ? "image/png"
                                    : "application/octet-stream";
                    const blob = b64toBlob(base64, tipoMime);
                    const url = URL.createObjectURL(blob);
                    window.open(url, "_blank");
                });

            } else {
                existeDocumento = false;
                textareaObs.value = "";
                contenedorArchivoActual.innerHTML = "";
                console.log("ℹ️ No hay documento previo");
            }

        } catch (err) {
            console.error("❌ Error al verificar documento existente:", err);
        }
    }

    // 🔹 Detectar cambios
    selectPracticanteModal.addEventListener("change", async (e) => {
        const practicanteID = e.target.value;
        if (!practicanteID) return;

        // Obtener datos del practicante y su solicitud
        try {
            const result = await api.getPracticante(practicanteID);
            console.log("🧾 Datos completos recibidos:", result);

            const campoSolicitud = document.getElementById("solicitudID");

            if ((!campoSolicitud.value || campoSolicitud.value === "undefined") &&
                result.success && result.data && result.data.SolicitudID) {
                campoSolicitud.value = result.data.SolicitudID;
                window.solicitudActualID = result.data.SolicitudID;
                solicitudIDActual = result.data.SolicitudID;
                console.log("✅ Solicitud asociada:", result.data.SolicitudID);
            }

        } catch (error) {
            console.error("❌ Error al obtener datos del practicante:", error);
        }

        verificarDocumentoExistente();
    });

    selectTipoDocumento.addEventListener("change", verificarDocumentoExistente);

    // 🔹 Si se selecciona un nuevo archivo
    inputArchivo.addEventListener("change", () => {
        if (inputArchivo.files.length > 0) {
            contenedorArchivoActual.innerHTML = `
                <p style="color:orange;">Se reemplazará el documento existente al subir uno nuevo.</p>
            `;
        } else if (archivoExistente) {
            verificarDocumentoExistente();
        }
    });

    // 🔹 Utilidad: convertir Base64 a Blob
    function b64toBlob(base64, type) {
        const byteCharacters = atob(base64);
        const byteNumbers = Array.from(byteCharacters, c => c.charCodeAt(0));
        const byteArray = new Uint8Array(byteNumbers);
        return new Blob([byteArray], { type });
    }

    // 🔹 Cargar practicantes en ambos select
    try {
        const practicantes = await api.listarNombrePracticantes();

        if (!practicantes || !Array.isArray(practicantes)) {
            console.warn("La respuesta de la API no es un array válido de practicantes.");
            return; 
        }

        practicantes.forEach(p => {
            const option = new Option(p.NombreCompleto, p.PracticanteID);
            selectPracticante.add(option.cloneNode(true));
            selectPracticanteModal.add(option);
        });

    } catch (err) {
        console.error("Error cargando practicantes:", err);
    }

    // 🔹 Cuando se selecciona un practicante en la vista principal
    selectPracticante.addEventListener("change", async () => {
        const id = selectPracticante.value;
        if (!id) {
            listaDocumentos.innerHTML = "<p>Seleccione un practicante...</p>";
            return;
        }

        // 🆕 Obtener solicitudID del practicante seleccionado
        try {
            const solicitudData = await api.obtenerSolicitudPorPracticante(id);
            if (solicitudData.success && solicitudData.data) {
                solicitudIDActual = solicitudData.data.SolicitudID;
                console.log("📋 SolicitudID obtenida:", solicitudIDActual);
            }
        } catch (error) {
            console.error("Error al obtener solicitud:", error);
        }

        const documentos = await getDocumentosPorPracticante(id);
        renderDocumentos(documentos);
    });

    // 🔹 Botón subir documento
    document.getElementById("btnSubirDocumento").addEventListener("click", () => {
        openModal("modalSubirDocumento");
    });

    // 🔹 Subir documento 
    document.getElementById("formSubirDocumento").addEventListener("submit", async (e) => {
        e.preventDefault();

        // 📍 1️⃣ Obtener el practicante seleccionado
        const practicanteID = document.getElementById("practicanteDocumento")?.value;
        if (!practicanteID) {
            alert("Por favor selecciona un practicante antes de subir documentos.");
            return;
        }

        // 📍 2️⃣ Verificar si ya existe una solicitud actual
        let solicitudID = window.solicitudActualID || null;

        if (!solicitudID) {
            console.log("🟠 No hay solicitud registrada. Creando nueva solicitud para el practicante:", practicanteID);

            try {
                const crearResponse = await api.crearSolicitud(practicanteID);
                if (!crearResponse.ok) throw new Error(`Error HTTP: ${crearResponse.status}`);

                const crearResult = await crearResponse.json();
                console.log("📩 Respuesta crearSolicitud:", crearResult);

                if (!crearResult.success) {
                    alert("Error al crear solicitud: " + crearResult.message);
                    return;
                }

                solicitudID = crearResult.solicitudID;
                window.solicitudActualID = solicitudID; // guardamos para siguientes documentos
                console.log("🆕 Solicitud creada con ID:", solicitudID);
            } catch (err) {
                console.error("❌ Error al crear solicitud:", err);
                alert("No se pudo crear la solicitud. Revisa la consola.");
                return;
            }
        }

        // 📍 3️⃣ Asignar solicitudID al formulario antes de enviarlo
        const inputSolicitud = document.getElementById("solicitudID");
        if (inputSolicitud) {
            inputSolicitud.value = solicitudID;
        } else {
            console.warn("⚠️ No existe input hidden con id='solicitudID'");
        }

        const formData = new FormData(e.target);

        console.log("📦 FormData enviado:", Object.fromEntries(formData.entries()));
        console.log("🧩 existeDocumento =", existeDocumento, "| solicitudID =", formData.get("solicitudID"));

        try {
            let response;
            if (existeDocumento) {
                console.log("🟢 Actualizando documento existente...");
                response = await api.actualizarDocumento(formData);
            } else {
                console.log("🟡 Subiendo nuevo documento...");
                response = await api.subirDocumento(formData);
            }

            if (!response.ok) throw new Error(`Error HTTP: ${response.status}`);
            const result = await response.json();

            if (!result.success) throw new Error(result.message || "Error desconocido");

            alert(result.message);
            closeModal("modalSubirDocumento");
            e.target.reset();

            // 🔄 4️⃣ Recargar lista de documentos
            const documentos = await getDocumentosPorPracticante(practicanteID);
            renderDocumentos(documentos);

            existeDocumento = false;
        } catch (err) {
            console.error("❌ Error al guardar documento:", err);
            alert("Error al guardar el documento. Revisa la consola.");
        }
    });


    // 🆕 Botón para generar carta (cuando documentos completos)
    document.getElementById("btnGenerarCarta")?.addEventListener("click", async () => {
        const practicanteID = selectPracticante.value;
        if (!practicanteID) {
            alert("Seleccione un practicante primero");
            return;
        }

        try {
            // Aquí implementarás la generación de carta PDF
            alert("Funcionalidad de generar carta en desarrollo");
            // TODO: Implementar generación de carta de aceptación
        } catch (error) {
            console.error("Error al generar carta:", error);
            alert("Error al generar la carta");
        }
    });

});

// 🆕 Cargar áreas para el modal de envío de solicitud
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

// 🆕 Abrir modal para enviar solicitud a área
function abrirModalEnviarSolicitud(solicitudID) {
    document.getElementById("solicitudEnvioID").value = solicitudID;
    openModal("modalEnviarSolicitud");
}

// 🆕 Cerrar modal de enviar solicitud
function cerrarModalEnviarSolicitud() {
    closeModal("modalEnviarSolicitud");
    document.getElementById("formEnviarSolicitud").reset();
}

// 🆕 Enviar solicitud a área
document.getElementById("formEnviarSolicitud")?.addEventListener("submit", async (e) => {
    e.preventDefault();
    
    const solicitudID = document.getElementById("solicitudEnvioID").value;
    const destinatarioAreaID = document.getElementById("areaDestino").value;
    const contenido = document.getElementById("mensajeSolicitud").value;

    console.log(solicitudID);
    console.log(destinatarioAreaID);
    console.log(contenido);
    
    // Obtener área de RRHH desde sessionStorage (deberías guardarlo en el login)
    const remitenteAreaID = sessionStorage.getItem('areaID') || 1; // 1 = RRHH por defecto
    console.log("El remitente de area ID", remitenteAreaID);
    
    try {
        const response = await api.enviarSolicitudArea({
            solicitudID: parseInt(solicitudID),
            remitenteAreaID: parseInt(remitenteAreaID),
            destinatarioAreaID: parseInt(destinatarioAreaID),
            contenido
        });
        
        if (response.success) {
            alert('✅ Solicitud enviada correctamente al área');
            cerrarModalEnviarSolicitud();
            
            // Deshabilitar botón después de enviar
            const btnEnviar = document.getElementById("btnEnviarSolicitudArea");
            if (btnEnviar) {
                btnEnviar.disabled = true;
                btnEnviar.innerHTML = '<i class="fas fa-check"></i> Solicitud Enviada';
            }
        } else {
            alert('Error: ' + response.message);
        }
    } catch (error) {
        console.error('Error al enviar solicitud:', error);
        alert('❌ Error al enviar la solicitud');
    }
});

// 🧩 Funciones auxiliares
function openModal(id) {
    document.getElementById(id).style.display = "flex";
}

function closeModal(id) {
    document.getElementById(id).style.display = "none";
}

async function getDocumentosPorPracticante(id) {
    try {
        const data = await api.obtenerDocumentosPorPracticante(id);
        console.log("✅ Documentos recibidos:", data);

        if (!data || !Array.isArray(data)) {
            console.warn("⚠️ La API no devolvió un array de documentos válido:", data);
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
    } else if (base64.startsWith("0M8R4KGx")) {
        tipoMime = "application/msword";
        extension = "doc";
    }

    const link = document.createElement("a");
    link.href = `data:${tipoMime};base64,${base64}`;
    link.download = `${nombre}.${extension}`;
    link.click();
}

function renderDocumentos(documentos) {
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

    const tabla = `
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Tipo</th>
                    <th>Archivo</th>
                    <th>Importancia</th>
                    <th>Observaciones</th>
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
                                <button class="btn-descargar" 
                                        onclick="descargarArchivo('${doc.archivo}', '${doc.tipo}')">
                                    <i class="fas fa-download"></i> Descargar
                                </button>
                            </td>
                            <td style="color: ${obligatorio ? '#FF664A' : '#7575FA'}; font-weight: bold;">
                                ${obligatorio ? 'Obligatorio' : 'Opcional'}
                            </td>
                            <td>${doc.observaciones ? doc.observaciones : '-'}</td>
                        </tr>
                    `;
                }).join("")}
            </tbody>
        </table>

        <div class="enviar-solicitud-container" style="margin-top: 20px; text-align: center;">
            <button id="btnEnviarSolicitudArea" 
                    class="btn-enviar" 
                    ${todosCompletos ? '' : 'disabled'}
                    onclick="abrirModalEnviarSolicitud(${solicitudIDActual})">
                <i class="fas fa-paper-plane"></i> Enviar Solicitud a Área
            </button>
            <button id="btnGenerarCarta" 
                    class="btn-success" 
                    ${todosCompletos ? '' : 'disabled'}
                    style="margin-left: 10px;">
                <i class="fas fa-file-contract"></i> Generar Carta de Aceptación
            </button>
            ${
                todosCompletos
                ? "<p class='msg-ok'>✅ Todos los documentos obligatorios están completos.</p>"
                : `<p class='msg-warn'>⚠️ Faltan documentos obligatorios: ${faltantes.join(", ")}</p>`
            }
        </div>
    `;

    contenedor.innerHTML = tabla;
}

// 🆕 Hacer solicitudIDActual global para uso en onclick
let solicitudIDActual = null;