<div class="documentos-container">
    <div class="action-buttons">
        <button class="btn-primary" id="btnSubirDocumento">
            <i class="fas fa-upload"></i> Subir Documento
        </button>
        <button class="btn-success" id="btnGenerarCarta" disabled>
            <i class="fas fa-file-contract"></i> Generar Carta
        </button>
        <button class="btn-info" id="btnEnviarSolicitudArea" disabled>
            <i class="fas fa-paper-plane"></i> Enviar Solicitud a Área
        </button>
    </div>
</div>

<!-- Modal para enviar solicitud -->
<div id="modalEnviarSolicitud" class="modal" style="display:none;">
    <div class="modal-content">
        <h3>Enviar Solicitud a Área</h3>
        <form id="formEnviarSolicitud">
            <input type="hidden" id="solicitudEnvioID">
            
            <div class="form-group">
                <label>Área Destino:</label>
                <select id="areaDestino" required>
                    <option value="">Seleccionar área...</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Mensaje:</label>
                <textarea id="mensajeSolicitud" rows="4" required 
                    placeholder="Descripción de la solicitud..."></textarea>
            </div>
            
            <button type="submit" class="btn-success">Enviar Solicitud</button>
            <button type="button" onclick="cerrarModalEnviarSolicitud()">Cancelar</button>
        </form>
    </div>
</div>

<div class="page-header">
    <h3 class="card-title">Documentos Requeridos por Practicante</h3>
    <div id="documentosRequeridos">
        <div class="form-group">
            <label for="selectPracticanteDoc">Seleccionar Practicante:</label>
            <select id="selectPracticanteDoc">
                <option value="">Seleccionar...</option>
            </select>
        </div>
        <div id="listaDocumentos">
            <!-- Contenido dinámico -->
        </div>
    </div>
</div>


<!-- Modal: Subir Documento -->
<div id="modalSubirDocumento" class="modal" style="display:none;">
    <div class="modal-content-custom">
        <div class="modal-header-custom">
            <h2>Subir Documento</h2>
            <button class="close" onclick="closeModal('modalSubirDocumento')">&times;</button>
        </div>
        <div class="modal-body-custom">
            <form id="formSubirDocumento" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="practicanteDocumento">Practicante:</label>
                    <select id="practicanteDocumento" name="practicanteID" required>
                        <option value="">Seleccionar practicante...</option>
                    </select>
                </div>

                <input type="hidden" id="solicitudID" name="solicitudID">

                <div class="form-group">
                    <label for="tipoDocumento">Tipo de Documento:</label>
                    <select id="tipoDocumento" name="tipoDocumento" required>
                        <option value="">Seleccionar tipo...</option>
                        <option value="cv">Curriculum Vitae</option>
                        <option value="carnet_vacunacion">Carnet de Vacunación COVID</option>
                        <option value="carta_presentacion">Carta de Presentación de la Universidad</option>
                        <option value="dni">DNI</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="archivoDocumento">Archivo:</label>
                    <input type="file" id="archivoDocumento" name="archivoDocumento" accept=".pdf,.doc,.docx,.jpg,.png">
                    
                    <!-- Contenedor donde aparecerá el documento actual -->
                    <div id="contenedorArchivoActual" style="margin-top: 10px;"></div>
                </div>


                <div class="form-group">
                    <label for="observacionesDoc">Observaciones:</label>
                    <textarea id="observacionesDoc" name="observacionesDoc" rows="2"></textarea>
                </div>

                <div class="action-buttons">
                    <button type="submit" class="btn-success">
                        <i class="fas fa-upload"></i> Subir Documento
                    </button>
                    <button type="button" class="btn-cancel" onclick="closeModal('modalSubirDocumento')">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

