<!-- VISTA DE SECCION PRACTICANTES -->

<div class="practicantes-container">

    <div class="page-header">
        <h1>Gestión de Practicantes</h1>
        <p class="page-subtitle">Administrar solicitudes y estados de practicantes</p>
    </div>
    
    <div class="action-buttons">
        <button class="btn-primary" id="btnNuevoPracticante"><i class="fas fa-plus"></i> Nuevo Practicante</button>
        <button class="btn-success" id="btnExportarPracticantes"><i class="fas fa-download"></i> Exportar Lista</button>
        <button class="btn-info" id="btnMensajes"><i class="fas fa-envelope"></i> Mensajes</button>
    </div>

    <!-- Filtros -->
    <div class="content-card" style="margin-bottom: 20px;">
        <h3 class="card-title">Filtros</h3>
        <div style="display: flex; gap: 15px; align-items: center;">
            <div style="flex: 1;">
                <label for="filtroNombre">Buscar por nombre:</label>
                <input type="text" id="filtroNombre" placeholder="Ingrese nombre..." style="width: 100%; padding: 8px;">
            </div>
            <div style="flex: 1;">
                <label for="filtroArea">Filtrar por área:</label>
                <select id="filtroArea" style="width: 100%; padding: 8px;">
                    <option value="">Todas las áreas</option>
                </select>
            </div>
            <button class="btn-primary" id="btnAplicarFiltros" style="margin-top: 20px;">
                <i class="fas fa-filter"></i> Aplicar Filtros
            </button>
        </div>
    </div>

    <div class="content-card">
        <h3 class="card-title">Lista de Practicantes</h3>
        <div class="table-container">
            <table id="tablaPracticantes" class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>DNI</th>
                        <th>Nombre Completo</th>
                        <th>Carrera</th>
                        <th>Universidad</th>
                        <th>Fecha Registro</th>
                        <th>Area</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Se llenará dinámicamente con JS -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal existente de Practicante -->
<div id="PracticanteModal" class="modal" style="display:none;">
  <div class="modal-content">
    <h3 id="tituloModalPracticante">Nuevo Practicante</h3>
    <form id="formPracticante">
      <input type="hidden" id="practicanteID" name="ID">
      <input type="text" id="DNI" name="DNI" placeholder="DNI" required>
      <input type="text" id="Nombres" name="Nombres" placeholder="Nombres" required>
      <input type="text" id="ApellidoPaterno" name="ApellidoPaterno" placeholder="Apellido Paterno" required>
      <input type="text" id="ApellidoMaterno" name="ApellidoMaterno" placeholder="Apellido Materno" required>
      <input type="text" id="Carrera" name="Carrera" placeholder="Carrera" required>
      <input type="email" id="Email" name="Email" placeholder="Correo">
      <input type="text" id="Telefono" name="Telefono" placeholder="Teléfono">
      <input type="text" id="Direccion" name="Direccion" placeholder="Dirección">
      <input type="text" id="Universidad" name="Universidad" placeholder="Universidad">
      <button type="submit">Guardar</button>
      <button type="button" onclick="cerrarModalPracticante()">Cancelar</button>
    </form>
  </div>
</div>

<!-- Modal de Mensajes -->
<div id="modalMensajes" class="modal" style="display:none;">
    <div class="modal-content">
        <h3><i class="fas fa-inbox"></i> Solicitudes Recibidas</h3>
        
        <div id="listaMensajes" style="max-height: 450px; overflow-y: auto;">
            <!-- Se llenará dinámicamente con este formato:
            
            <div class="mensaje-card nuevo" onclick="abrirSolicitud(1)">
                <div class="mensaje-header">
                    <span class="mensaje-titulo">
                        <i class="fas fa-user-graduate"></i> Juan Pérez García
                    </span>
                    <span class="mensaje-fecha">
                        <i class="far fa-clock"></i> Hace 2 horas
                    </span>
                </div>
                <div class="mensaje-contenido">
                    Solicita prácticas para el área de Sistemas. Universidad Nacional...
                </div>
                <div class="mensaje-footer">
                    <span class="badge badge-info">Pendiente</span>
                </div>
            </div>
            
            -->
        </div>
        
        <div class="modal-footer">
            <button type="button" onclick="cerrarModalMensajes()" class="btn-cancel">
                <i class="fas fa-times"></i> Cerrar
            </button>
        </div>
    </div>
</div>

<!-- Modal para Aceptar/Rechazar Practicante -->
<div id="modalAceptarPracticante" class="modal" style="display:none;">
    <div class="modal-content" style="max-width: 750px;">
        <h3><i class="fas fa-user-check"></i> Gestionar Solicitud de Practicante</h3>
        
        <form id="formAceptarPracticante">
            <input type="hidden" id="aceptarPracticanteID">
            <input type="hidden" id="aceptarSolicitudID">
            
            <!-- Información del Practicante -->
            <div id="infoPracticante">
                <!-- Se llenará dinámicamente -->
                
            </div>
            
            <!-- Decisión -->
            <div class="form-group">
                <label>
                    <i class="fas fa-tasks"></i> Decisión sobre la Solicitud
                </label>
                <select id="decisionAceptacion" required>
                    <option value="">-- Seleccionar decisión --</option>
                    <option value="aceptar">✓ Aceptar Practicante</option>
                    <option value="rechazar">✗ Rechazar Solicitud</option>
                </select>
            </div>
            
            <!-- Campos de Aceptación (aparecen cuando se selecciona "aceptar") -->
            <div id="camposAceptacion" style="display:none;">
                <h4 style="margin: 0 0 15px 0; color: #667eea;">
                    <i class="fas fa-calendar-check"></i> Periodo de Prácticas
                </h4>
                
                <div class="form-group">
                    <label>
                        <i class="fas fa-calendar-plus"></i> Fecha de Entrada
                    </label>
                    <input type="date" id="fechaEntrada" min="">
                </div>
                
                <div class="form-group">
                    <label>
                        <i class="fas fa-calendar-minus"></i> Fecha de Salida
                    </label>
                    <input type="date" id="fechaSalida" min="">
                </div>
            </div>
            
            <!-- Mensaje de Respuesta -->
            <div class="form-group">
                <label>
                    <i class="fas fa-comment-dots"></i> Mensaje de Respuesta
                </label>
                <textarea 
                    id="mensajeRespuesta" 
                    rows="4" 
                    placeholder="Escriba un mensaje para el practicante explicando su decisión..."
                    required
                ></textarea>
            </div>
            
            <!-- Botones de Acción -->
            <div class="modal-footer">
                <button type="button" onclick="cerrarModalAceptar()" class="btn-cancel">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="submit" class="btn-success" id="btnEnviarRespuesta">
                    <i class="fas fa-paper-plane"></i> Enviar Respuesta
                </button>
            </div>
        </form>
    </div>
</div>

