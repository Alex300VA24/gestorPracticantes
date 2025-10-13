   // ===================================== Documentos ====================================================
document.addEventListener("DOMContentLoaded", async () => {
    const selectPracticante = document.getElementById("selectPracticanteDoc");
    const selectPracticanteModal = document.getElementById("practicanteDocumento");
    const listaDocumentos = document.getElementById("listaDocumentos");

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

    // Botón revisar documentos
    document.getElementById("btnRevisarDocumentos").addEventListener("click", async () => {
        const id = selectPracticante.value;
        if (!id) {
            alert("Seleccione primero un practicante para revisar sus documentos.");
            return;
        }

        openModal("modalSubirDocumento");

        // Cargar documentos del practicante en la lista dentro del modal
        const documentos = await obtenerDocumentosPorPracticante(id);
        renderArchivosSubidos(documentos);
    });

    // 🔹 Subir documento
    document.getElementById("formSubirDocumento").addEventListener("submit", async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);

        try {
            const response = await fetch(`${api.baseURL}/solicitudes/subirDocumento`, {
                method: "POST",
                body: formData
            });

            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status}`);
            }

            const result = await response.text();
            console.log("📤 Respuesta de subida:", result);

            // ✅ Cerrar modal
            closeModal('modalSubirDocumento');

            // ✅ Limpiar formulario
            e.target.reset();

            // ✅ Feedback al usuario
            alert("✅ Documento subido correctamente.");

            // ✅ Verificar si hay un practicante seleccionado en el combo principal
            const selectPracticante = document.getElementById("selectPracticanteDoc");
            const idSeleccionado = selectPracticante.value;

            if (idSeleccionado) {
                console.log("🔄 Recargando documentos para practicante:", idSeleccionado);
                const documentos = await getDocumentosPorPracticante(idSeleccionado);
                renderDocumentos(documentos);
            }

        } catch (err) {
            console.error("❌ Error al subir documento:", err);
            alert("Error al subir el documento. Revisa la consola.");
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
        const response = await fetch(`${api.baseURL}/solicitudes/documentos?practicanteID=${id}`);
        const text = await response.text(); // 🔍 ver respuesta sin procesar

        // Intenta convertir la respuesta en JSON
        const data = JSON.parse(text);
        console.log("JSON parseado:", data);

        // Validar formato
        if (!data || !Array.isArray(data)) {
            console.warn("⚠️ La API no devolvió un array de documentos válido:", data);
            return [];
        }

        return data; // ✅ devolvemos los documentos
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

    // Definir los documentos obligatorios
    const obligatorios = ["CV", "DNI", "Carnet de Vacunacion"];

    // Detectar si todos los obligatorios están presentes
    const tiposSubidos = documentos.map(doc => doc.tipo);
    const faltantes = obligatorios.filter(req => !tiposSubidos.includes(req));
    const todosCompletos = faltantes.length === 0;

    // Generar tabla de documentos
    const tabla = `
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Tipo</th>
                    <th>Archivo</th>
                    <th>Importancia</th>
                </tr>
            </thead>
            <tbody>
                ${documentos.map(doc => {
                    const tipoMime = doc.archivo.startsWith("JVBER") ? "application/pdf" : "image/png";
                    const obligatorio = obligatorios.includes(doc.tipo);
                    return `
                        <tr>
                            <td>${doc.tipo}</td>
                            <td>
                                <button class="btn-descargar" onclick="descargarArchivo('${doc.archivo}', '${doc.tipo}')">
                                    <i class="fas fa-download"></i> Descargar
                                </button>
                            </td>
                            <td style="color: ${obligatorio ? '#e74c3c' : '#27ae60'}; font-weight: bold;">
                                ${obligatorio ? 'Obligatorio' : 'Opcional'}
                            </td>
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

    // Asignar evento si todo está completo
    if (todosCompletos) {
        document.getElementById("btnEnviarSolicitud").addEventListener("click", async () => {
            const id = document.getElementById("selectPracticanteDoc").value;
            if (!id) return alert("Seleccione un practicante primero.");

            const resp = await fetch(`${api.baseURL}/solicitudes/enviarSolicitud`, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ practicanteID: id })
            });

            const data = await resp.text();
            console.log("Respuesta de enviarSolicitud:", data);
            alert("✅ Solicitud enviada con éxito.");
        });
    }
}


function renderArchivosSubidos(documentos) {
    const cont = document.getElementById("listaArchivosSubidos");
    if (!documentos.length) {
        cont.innerHTML = "<p>No hay documentos subidos.</p>";
        return;
    }
    cont.innerHTML = documentos.map(d => `
        <div class="archivo-item">
            <strong>${d.tipo}</strong> — 
            <a href="${d.url}" target="_blank">Ver archivo</a>
        </div>
    `).join("");
}