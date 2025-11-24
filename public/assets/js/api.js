class API {

    // Base para las URLs
    constructor(baseURL = '/gestorPracticantes/public/api') {
        this.baseURL = baseURL;
    }
    
    /* Metodo request para peticiones HTTP
        *  Parametros: '/ejemplo', { method: 'GET' }
    */
    // En tu archivo api.js, reemplaza el método request:

    async request(endpoint, options = {}) {
        const url = `${this.baseURL}${endpoint}`;
        
        const config = {
            ...options,
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            }
        };
        
        try {
            const response = await fetch(url, config);

            // Obtenemos el texto de la respuesta
            const text = await response.text();

            // Si hay contenido, parseamos JSON
            let data = {};
            try {
                data = text ? JSON.parse(text) : {};
            } catch (e) {
                console.error('Error al parsear JSON:', e);
                console.error('Texto recibido:', text);
                throw new Error('Respuesta del servidor no es JSON válido: ' + text.substring(0, 200));
            }

            if (!response.ok) {
                console.error('❌ Error response:', data);
                throw new Error(data.message || `Error HTTP ${response.status}`);
            }

            return data;
        } catch (error) {
            console.error('💥 API Error:', error);
            throw error;
        }
    }

    // =========================== METODOS HTTP =============================== 
    // Metodo GET para hacer consultas
    async get(endpoint) {
        return this.request(endpoint, { method: 'GET' });
    }
    
    // Metodo POST para enviar datos
    async post(endpoint, data) {
        return this.request(endpoint, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    }
    
    // Metodo PUT para actualizar data
    async put(endpoint, data) {
        return this.request(endpoint, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    }
    
    // Metodo DELETE para eliminar data
    async delete(endpoint) {
        return this.request(endpoint, { method: 'DELETE' });
    }

    /* =================== METODOS DE AUTENTICACION ================== */
    
    /* Iniciar Sesion del Usuario
     * Parametros: ('username', 'password') 
     */
    async login(nombreUsuario, password) {
        return this.post('/login', { nombreUsuario, password });
    }
    
    /* Validar la doble autenticación
     * Parametros: (/^[0-9]$/) 
    */
    async validarCUI(cui) {
        return this.post('/validar-cui', { cui });
    }
    
    // Cerrar Sesion del usuario
    async logout() {
        return this.post('/logout');
    }

    // En api.js - el método ya existe, solo verifica que esté así:

    async obtenerDatosInicio(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return this.get(`/inicio${queryString ? '?' + queryString : ''}`);
    }

    // ================ METODOS PARA SECCION PRACTICANTES ====================

    /*
     * Obtener todos los practicantes de la base de datos
    */
    async getPracticantes() {
        return this.get('/practicantes');
    }

    /* Obtener un practicante de la base de datos por su ID
     * Parametro: (/^[0-9]+$/)
    */
    async getPracticante(practicanteID) {
        return this.get(`/practicantes/${practicanteID}`);
    }

    /* Crear un practicante en la base de datos
     * Parametros: 
        data =  {
                    ApellidoMaterno: "apellidoMaterno",
                    ApellidoPaterno: "apellidoPaterno",
                    Carrera: "carrera",
                    DNI: "12345678",
                    Direccion: "direccion",
                    Email: "email",
                    Nombres: "nombres",
                    Universidad: "universidad"
                }
    */
    async crearPracticante(data) {
        return this.post('/practicantes', data);
    }

    /* Actualizar un practicante en la base de datos
     * Parametros:
        id = (/^[0-9]+$/)
        data =  {
                    ApellidoMaterno: "apellidoMaterno",
                    ApellidoPaterno: "apellidoPaterno",
                    Carrera: "carrera",
                    DNI: "12345678",
                    Direccion: "direccion",
                    Email: "email",
                    Nombres: "nombres",
                    Universidad: "universidad"
                }
    */
    async actualizarPracticante(practicanteID, data) {
        return this.put(`/practicantes/${practicanteID}`, data);
    }

    /* Eliminar un practicante en la base de datos
     * Parametro: (/^[0-9]+$/)
    */
    async eliminarPracticante(practicanteID) {
        return this.delete(`/practicantes/${practicanteID}`);
    }

    /* Filtrar practicantes por Area o Nombre
     * filtros = {
                    nombre = nombre,
                    areaID = [0-9]+
                 }
    */  
    async filtrarPracticantes(filtros = {}) {
        return this.post('/practicantes/filtrar', filtros);
    }

    /* Aceptar Practicante 
     * data = { 
        practicanteID,
        solicitudID,
        areaID,
        fechaEntrada,
        fechaSalida,
        mensajeRespuesta
      }
    */
    async aceptarPracticante(data) {
        return this.post('/practicantes/aceptar', data);
    }

    /* Rechazar practicante 
     * data = {
            practicanteID,
            solicitudID,
            mensajeRespuesta
        }
    */
    async rechazarPracticante(data) {
        return this.post('/practicantes/rechazar', data);
    }
    /* Lista nombres de todos los Practicantes para select */
    async listarNombrePracticantes() {
        return this.get('/practicantes/listar-nombres');
    }

    // ============================= DOCUMENTOS ===============================

    /* Obtener documentos del Practicante por su id
     *  Parametro: practicanteID
    */
    async obtenerDocumentosPorPracticante(practicanteID) {
        return this.get(`/solicitudes/documentos?practicanteID=${practicanteID}`);
    }
    /* Obtener documentos por su tipo ('cv', 'dni', 'carnet_vacunacion', 'carta_presentacion')
        y por practicante id
     *  Parametro: practicanteID, tipoDocumento
    */
    async obtenerDocumentoPorTipoYPracticante(practicanteID, tipoDocumento) {
        return this.get(`/solicitudes/obtenerPorTipoYPracticante?practicanteID=${practicanteID}&tipoDocumento=${tipoDocumento}`);
    }

    async subirDocumento(formData) {
        const response = await fetch(`${this.baseURL}/solicitudes/subirDocumento`, {
            method: "POST",
            body: formData
        });
        return response;
    }

    async actualizarDocumento(formData) {
        return fetch(`${this.baseURL}/solicitudes/actualizarDocumento`, {
            method: "POST",
            body: formData
        });
    }

    async generarCartaAceptacion(solicitudID, numeroExpediente, formato) {
        return this.post('/solicitudes/generarCartaAceptacion', {
            solicitudID,
            numeroExpediente,
            formato
        });
    }

    async verificarSolicitudParaCarta(solicitudID) {
        return this.get(`/solicitudes/verificarSolicitudParaCarta?solicitudID=${solicitudID}`);
    }

    async listarSolicitudesAprobadas() {
        return this.get('/solicitudes/listarSolicitudesAprobadas');
    }

    // ============================= SOLICITUDES ===============================
    /* Obtener solicitud por practicante 
     * Parametro: practicanteID
    */
    async obtenerSolicitudPorPracticante(practicanteID) {
        return this.get(`/solicitudes/por-practicante?practicanteID=${practicanteID}`);
    }

    /* Verificar estado de la solicitud enviada al area correspondiente 
     * Parametro: solicitudID
    */
    async verificarEstadoSolicitud(solicitudID) {
        return this.get(`/solicitudes/estado?solicitudID=${solicitudID}`);
    }

    /* Crear solicitud usando la id del practicante
     * Parametro: practicanteID
    */
    async crearSolicitud(practicanteID) {
        return this.get(`/solicitudes/crearSolicitud?practicanteID=${practicanteID}`);
    }


    //  --- MENSAJES ---
    async enviarSolicitudArea(data) {
        return this.post('/mensajes/enviar', data);
    }

    // En tu archivo api.js, agregar:
    async eliminarDocumento(documentoID) {
        return this.post('/solicitudes/eliminarDocumento', { documentoID });
    }

    async responderSolicitud(data) {
        return this.post('/mensajes/responder', data);
    }

    async listarMensajes(areaID) {
        return this.get(`/mensajes/${areaID}`);
    }

    async eliminarMensaje(id) {
        return await this.delete(`/mensajes/${id}`);
    }

    // ================ MÉTODOS PARA REPORTES ====================

    // Reportes de Practicantes
    async reportePracticantesActivos() {
        return this.get('/reportes/practicantes-activos');
    }

    async reportePracticantesCompletados() {
        return this.get('/reportes/practicantes-completados');
    }

    async reportePorArea(areaID = null) {
        const endpoint = areaID ? `/reportes/por-area?areaID=${areaID}` : '/reportes/por-area';
        return this.get(endpoint);
    }

    async reportePorUniversidad() {
        return this.get('/reportes/por-universidad');
    }

    // Reportes de Asistencia
    async reporteAsistenciaPracticante(practicanteID, fechaInicio = null, fechaFin = null) {
        let endpoint = `/reportes/asistencia-practicante?practicanteID=${practicanteID}`;
        if (fechaInicio) endpoint += `&fechaInicio=${fechaInicio}`;
        if (fechaFin) endpoint += `&fechaFin=${fechaFin}`;
        return this.get(endpoint);
    }

    async reporteAsistenciaDelDia(fecha = null) {
        const endpoint = fecha ? `/reportes/asistencia-dia?fecha=${fecha}` : '/reportes/asistencia-dia';
        return this.get(endpoint);
    }

    async reporteAsistenciaMensual(mes, anio) {
        return this.get(`/reportes/asistencia-mensual?mes=${mes}&anio=${anio}`);
    }

    async reporteAsistenciaAnual(anio) {
        return this.get(`/reportes/asistencia-anual?anio=${anio}`);
    }

    async reporteHorasAcumuladas(practicanteID = null) {
        const endpoint = practicanteID ? `/reportes/horas-acumuladas?practicanteID=${practicanteID}` : '/reportes/horas-acumuladas';
        return this.get(endpoint);
    }

    // Reportes Estadísticos
    async reporteEstadisticasGenerales() {
        return this.get('/reportes/estadisticas-generales');
    }

    async reportePromedioHoras() {
        return this.get('/reportes/promedio-horas');
    }

    async reporteComparativoAreas() {
        return this.get('/reportes/comparativo-areas');
    }

    async reporteCompleto() {
        return this.get('/reportes/completo');
    }

    // Exportaciones
    async exportarPDF(tipoReporte, datos) {
        return this.post('/reportes/exportar-pdf', { tipoReporte, datos });
    }

    async exportarExcel(tipoReporte, datos) {
        return this.post('/reportes/exportar-excel', { tipoReporte, datos });
    }

    async exportarWord(tipoReporte, datos) {
        return this.post('/reportes/exportar-word', { tipoReporte, datos });
    }




    //  --- ÁREAS ---
    async listarAreas() {
        return this.get('/areas');
    }

    async obtenerTurnosPracticante(practicanteID) {
        return this.get(`/turnos/practicante/${practicanteID}`);
    }

    // --- ASISTENCIAS ---
    async listarAsistencias(data) {
        return this.post('/asistencias', data);
    }

    async registrarEntrada(data) {
        return this.post('/asistencias/entrada', data);
    }

    async registrarSalida(data) {
        return this.post('/asistencias/salida', data);
    }

    async iniciarPausa(data) {
        return this.post('/asistencias/pausa/iniciar', data);
    }

    async finalizarPausa(data) {
        return this.post('/asistencias/pausa/finalizar', data);
    }

    async obtenerAsistenciaCompleta(practicanteID) {
        return this.get(`/asistencias/obtener?practicanteID=${practicanteID}`);
    }

    // ================ MÉTODOS PARA CERTIFICADOS ====================

    async obtenerEstadisticasCertificados() {
        return this.get('/certificados/estadisticas');
    }

    async listarPracticantesParaCertificado() {
        return this.get('/certificados/listar-practicantes');
    }

    async obtenerInformacionCertificado(practicanteID) {
        return this.get(`/certificados/informacion/${practicanteID}`);
    }

    async generarCertificadoHoras(data) {
        return this.post('/certificados/generar', data);
    }

    // ================ MÉTODOS PARA USUARIOS ====================

    /* Listar todos los usuarios */
    async listarUsuarios() {
        return this.get('/usuarios');
    }

    /* Obtener un usuario por ID */
    async obtenerUsuario(usuarioID) {
        return this.get(`/usuarios/${usuarioID}`);
    }

    /* Crear nuevo usuario */
    async crearUsuario(data) {
        return this.post('/usuarios', data);
    }

    /* Actualizar usuario */
    async actualizarUsuario(usuarioID, data) {
        return this.put(`/usuarios/${usuarioID}`, data);
    }

    /* Eliminar usuario */
    async eliminarUsuario(usuarioID) {
        return this.delete(`/usuarios/${usuarioID}`);
    }

    /* Cambiar contraseña de usuario */
    async cambiarPasswordUsuario(usuarioID, data) {
        return this.put(`/usuarios/${usuarioID}/password`, data);
    }

    /* Filtrar usuarios */
    async filtrarUsuarios(filtros = {}) {
        return this.post('/usuarios/filtrar', filtros);
    }

    // --- INICIO / DASHBOARD ---
    async obtenerDatosInicio(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return this.get(`/inicio${queryString ? '?' + queryString : ''}`);
    }

}

const api = new API();