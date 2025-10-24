class API {
    constructor(baseURL = '/gestorPracticantes/public/api') {
        this.baseURL = baseURL;
    }
    
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
            
            // 🔹 Obtenemos el texto de la respuesta (puede estar vacío)
            const text = await response.text();

            // 🔹 Si hay contenido, parseamos JSON
            const data = text ? JSON.parse(text) : {};

            if (!response.ok) {
                throw new Error(data.message || 'Error en la petición');
            }

            return data;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }

    
    async get(endpoint) {
        return this.request(endpoint, { method: 'GET' });
    }
    
    async post(endpoint, data) {
        return this.request(endpoint, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    }
    
    async put(endpoint, data) {
        return this.request(endpoint, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    }
    
    async delete(endpoint) {
        return this.request(endpoint, { method: 'DELETE' });
    }
    
    // Métodos específicos
    async login(nombreUsuario, password) {
        return this.post('/login', { nombreUsuario, password });
    }
    
    async validarCUI(cui) {
        return this.post('/validar-cui', { cui });
    }
    
    async logout() {
        return this.post('/logout');
    }

    // --- PRACTICANTES ---
    async getPracticantes() {
        return this.get('/practicantes');
    }

    async crearPracticante(data) {
        return this.post('/practicantes', data);
    }

    async getPracticante(id) {
        return this.get(`/practicantes/${id}`);
    }

    async actualizarPracticante(id, data) {
        return this.put(`/practicantes/${id}`, data);
    }

    async eliminarPracticante(id) {
        return await this.delete(`/practicantes/${id}`);
    }

    // 🆕 Filtrar practicantes
    async filtrarPracticantes(nombre = null, areaID = null) {
        const params = new URLSearchParams();
        if (nombre) params.append('nombre', nombre);
        if (areaID) params.append('areaID', areaID);
        
        const query = params.toString() ? `?${params.toString()}` : '';
        return this.get(`/practicantes/filtrar${query}`);
    }

    // 🆕 Aceptar practicante
    async aceptarPracticante(data) {
        return this.post('/practicantes/aceptar', data);
    }

    // 🆕 Rechazar practicante
    async rechazarPracticante(data) {
        return this.post('/practicantes/rechazar', data);
    }

    // --- SOLICITUDES / DOCUMENTOS ---
    async listarNombrePracticantes() {
        return this.get('/solicitudes/listarPracticantes');
    }

    async obtenerDocumentosPorPracticante(practicanteID) {
        return this.get(`/solicitudes/documentos?practicanteID=${practicanteID}`);
    }

    async obtenerDocumentoPorTipoYPracticante(practicanteID, tipoDocumento) {
        return this.get(`/solicitudes/obtenerPorTipoYPracticante?practicanteID=${practicanteID}&tipoDocumento=${tipoDocumento}`);
    }

    // 🆕 Obtener solicitud por practicante
    async obtenerSolicitudPorPracticante(practicanteID) {
        return this.get(`/solicitudes/por-practicante?practicanteID=${practicanteID}`);
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

    // 🆕 --- MENSAJES ---
    async enviarSolicitudArea(data) {
        return this.post('/mensajes/enviar', data);
    }

    async responderSolicitud(data) {
        return this.post('/mensajes/responder', data);
    }

    async listarMensajes(areaID) {
        return this.get(`/mensajes/${areaID}`);
    }

    // 🆕 --- ÁREAS ---
    async listarAreas() {
        return this.get('/areas');
    }

    // 🆕 --- TURNOS ---
    async listarTurnos() {
        return this.get('/turnos');
    }

    async obtenerTurnosPracticante(practicanteID) {
        return this.get(`/turnos/practicante/${practicanteID}`);
    }

    // --- ASISTENCIAS ---
    async listarAsistencias() {
        return this.get('/asistencias');
    }

    async registrarEntrada(data) {
        return this.post('/asistencias/entrada', data);
    }

    async registrarSalida(data) {
        return this.post('/asistencias/salida', data);
    }

    // --- INICIO / DASHBOARD ---
    async obtenerDatosInicio() {
        return this.get('/inicio');
    }
}

const api = new API();