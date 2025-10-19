   // ===================================== Documentos ====================================================

document.addEventListener("DOMContentLoaded", async () => {
    const selectPracticante = document.getElementById("selectPracticanteDoc");
    const listaDocumentos = document.getElementById("listaDocumentos");


    const selectPracticanteModal = document.getElementById("practicanteDocumento");
    const selectTipoDocumento = document.getElementById("tipoDocumento");
    const inputArchivo = document.getElementById("archivoDocumento");
    const textareaObs = document.getElementById("observacionesDoc");
    const contenedorArchivoActual = document.createElement("div"); // contenedor dinámico
    inputArchivo.parentElement.appendChild(contenedorArchivoActual); // lo colocamos debajo del input

    let archivoExistente = null;
    let existeDocumento = false;


    // 🔹 Cuando se selecciona practicante o tipo de documento
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
                // Guardamos el ID para hacer actualización en lugar de crear
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

                // Ver documento en nueva pestaña
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
                existeDocumento = false; // ✅ Marca que no existe
                textareaObs.value = "";
                contenedorArchivoActual.innerHTML = "";
                console.log("ℹ️ No hay documento previo, pero se mantiene SolicitudID actual:", document.getElementById("solicitudID").value);
            }

            //window.solicitudActualID = result.data.SolicitudID;
            //document.getElementById("solicitudID").value = result.data.SolicitudID;

        } catch (err) {
            console.error("❌ Error al verificar documento existente:", err);
        }
    }


    // 🔹 Detectar cambios
    selectPracticanteModal.addEventListener("change", verificarDocumentoExistente);
    selectTipoDocumento.addEventListener("change", verificarDocumentoExistente);

    // 🔹 Si se selecciona un nuevo archivo, se reemplaza
    inputArchivo.addEventListener("change", () => {
        if (inputArchivo.files.length > 0) {
            contenedorArchivoActual.innerHTML = `
                <p style="color:orange;">Se reemplazará el documento existente al subir uno nuevo.</p>
            `;
        } else if (archivoExistente) {
            verificarDocumentoExistente(); // restaurar si cancela
        }
    });

    // 🔹 Utilidad: convertir Base64 a Blob
    function b64toBlob(base64, type) {
        const byteCharacters = atob(base64);
        const byteNumbers = Array.from(byteCharacters, c => c.charCodeAt(0));
        const byteArray = new Uint8Array(byteNumbers);
        return new Blob([byteArray], { type });
    }


    // 🔹 Cuando se selecciona un practicante en el modal, obtenemos su SolicitudID
    
    selectPracticanteModal.addEventListener("change", async (e) => {
        const practicanteID = e.target.value;
        if (!practicanteID) return;

        try {
            const result = await api.getPracticante(practicanteID);
            console.log("🧾 Datos completos recibidos:", result);

            const campoSolicitud = document.getElementById("solicitudID");

            // 🧩 Solo actualiza si el campo está vacío o no tiene valor válido
            if ((!campoSolicitud.value || campoSolicitud.value === "undefined") &&
                result.success && result.data && result.data.SolicitudID) {

                campoSolicitud.value = result.data.SolicitudID;
                window.solicitudActualID = result.data.SolicitudID;
                console.log("✅ Solicitud asociada:", result.data.SolicitudID);
            } else {
                console.log("⚙️ Se mantiene solicitudID actual:", campoSolicitud.value);
            }

        } catch (error) {
            console.error("❌ Error al obtener datos del practicante:", error);
        }
    });




    // 🔹 Cargar practicantes en ambos select
    try {
        const practicantes = await api.listarNombrePracticantes();

        // 1. **Verificar** si practicantes es un array válido antes de usar forEach.
        if (!practicantes || !Array.isArray(practicantes)) {
            console.warn("La respuesta de la API no es un array válido de practicantes.");
            // Opcional: podrías lanzar un error o simplemente salir de la función
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

    // Cuando se selecciona un practicante
    selectPracticante.addEventListener("change", async () => {
        const id = selectPracticante.value;
        if (!id) {
            listaDocumentos.innerHTML = "<p>Seleccione un practicante...</p>";
            return;
        }

        const documentos = await getDocumentosPorPracticante(id);
        renderDocumentos(documentos);
    });


    // Botón subir documento
    document.getElementById("btnSubirDocumento").addEventListener("click", () => {
        openModal("modalSubirDocumento");
    });

    // 🔹 Subir documento 
    document.getElementById("formSubirDocumento").addEventListener("submit", async (e) => {
        e.preventDefault();

        // 🔧 Asegura que el campo tenga valor
        if (window.solicitudActualID) {
            document.getElementById("solicitudID").value = window.solicitudActualID;
        }
        const formData = new FormData(e.target);


        console.log("📦 FormData enviado:", Object.fromEntries(formData.entries()));
        console.log("🧩 existeDocumento =", existeDocumento, "| solicitudID =", formData.get("solicitudID"));


        let response;

        try {
            if (existeDocumento) {
                console.log("🟢 Actualizando documento existente...");
                response = await api.actualizarDocumento(formData);
            } else {
                console.log("🟡 Subiendo nuevo documento...");
                response = await api.subirDocumento(formData);
            }

            if (!response.ok) throw new Error(`Error HTTP: ${response.status}`);

            const result = await response.json();
            console.log("📤 Respuesta:", result);

            if (!result.success) throw new Error(result.message || "Error desconocido");

            alert(result.message);
            closeModal("modalSubirDocumento");
            e.target.reset();

            // 🔄 Actualizar lista de documentos
            const selectPracticante = document.getElementById("selectPracticanteDoc");
            const idSeleccionado = selectPracticante.value;
            if (idSeleccionado) {
                const documentos = await getDocumentosPorPracticante(idSeleccionado);
                renderDocumentos(documentos);
            }

            // Reiniciar flag
            existeDocumento = false;

        } catch (err) {
            console.error("❌ Error al guardar documento:", err);
            alert("Error al guardar el documento. Revisa la consola.");
        }
    });


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
    // Detectar tipo MIME por las primeras letras del Base64
    let tipoMime = "application/octet-stream"; // valor por defecto
    let extension = "bin";

    if (base64.startsWith("JVBER")) { // PDF
        tipoMime = "application/pdf";
        extension = "pdf";
    } else if (base64.startsWith("UEsDB")) { // DOCX o XLSX o ZIP
        tipoMime = "application/vnd.openxmlformats-officedocument.wordprocessingml.document";
        extension = "docx";
    } else if (base64.startsWith("/9j/")) { // JPG
        tipoMime = "image/jpeg";
        extension = "jpg";
    } else if (base64.startsWith("iVBOR")) { // PNG
        tipoMime = "image/png";
        extension = "png";
    } else if (base64.startsWith("0M8R4KGx")) { // DOC antiguo (binario)
        tipoMime = "application/msword";
        extension = "doc";
    }

    // Crear enlace de descarga
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

    // Documentos obligatorios
    const obligatorios = ["CV", "DNI", "Carnet_Vacunacion"];

    // Verificar documentos subidos
    const normalizar = str =>
    str.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");

    const tiposSubidos = documentos.map(doc => normalizar(doc.tipo));
    const faltantes = obligatorios.filter(req =>
        !tiposSubidos.includes(normalizar(req))
    );
    const todosCompletos = faltantes.length === 0;


    // Construcción de tabla
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
                        req.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "") ===
                        doc.tipo.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "")
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

        <div class="enviar-solicitud-container">
            <button id="btnEnviarSolicitud" class="btn-enviar" ${todosCompletos ? '' : 'disabled'}>
                <i class="fas fa-paper-plane"></i> Enviar Solicitud
            </button>
            ${
                todosCompletos
                ? "<p class='msg-ok'>✅ Todos los documentos obligatorios están completos.</p>"
                : `<p class='msg-warn'>⚠️ Faltan documentos obligatorios: ${faltantes.join(", ")}</p>`
            }
        </div>
    `;

    contenedor.innerHTML = tabla;

    // 🔹 Evento para enviar solicitud (solo si está habilitado)
    const btnEnviar = document.getElementById("btnEnviarSolicitud");
    if (btnEnviar && todosCompletos) {
        btnEnviar.addEventListener("click", async () => {
            const id = document.getElementById("selectPracticanteDoc").value;
            if (!id) return alert("Seleccione un practicante primero.");

            try {
                const resp = await fetch(`${BASE_URL}solicitudes/enviarSolicitud`, {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ practicanteID: id })
                });

                if (!resp.ok) throw new Error("Error en la solicitud.");
                const data = await resp.text();
                console.log("Respuesta de enviarSolicitud:", data);
                alert("✅ Solicitud enviada con éxito.");
            } catch (err) {
                console.error("Error al enviar solicitud:", err);
                alert("❌ No se pudo enviar la solicitud.");
            }
        });
    }
}
