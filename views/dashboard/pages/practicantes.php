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
      <button type="button" onclick="cerrarModal()">Cancelar</button>
    </form>
  </div>
</div>

<!-- Modal de Mensajes -->
<div id="modalMensajes" class="modal" style="display:none;">
    <div class="modal-content">
        <h3>Solicitudes</h3>
        <div id="listaMensajes" style="max-height: 400px; overflow-y: auto;">
            <!-- Se llenará dinámicamente -->
        </div>
        <button type="button" onclick="cerrarModalMensajes()">Cerrar</button>
    </div>
</div>

<!-- Modal para Aceptar/Rechazar Practicante -->
<div id="modalAceptarPracticante" class="modal" style="display:none;">
    <div class="modal-content" style="max-width: 700px;">
        <h3>Gestionar Practicante</h3>
        <form id="formAceptarPracticante">
            <input type="hidden" id="aceptarPracticanteID">
            <input type="hidden" id="aceptarSolicitudID">
            
            <div id="infoPracticante">
                <!-- Información del practicante -->
            </div>
            
            <div class="form-group">
                <label>Decisión:</label>
                <select id="decisionAceptacion" required>
                    <option value="">Seleccionar...</option>
                    <option value="aceptar">Aceptar</option>
                    <option value="rechazar">Rechazar</option>
                </select>
            </div>
            
            <div id="camposAceptacion" style="display:none;">
                <div class="form-group">
                    <label>Turnos Asignados:</label>
                    <div id="contenedorTurnos">
                        <!-- Se llenará dinámicamente -->
                    </div>
                    <button type="button" class="btn-info btn-sm" id="btnAgregarTurno">
                        <i class="fas fa-plus"></i> Agregar Turno
                    </button>
                    <label>Fecha de Entrada:</label>
                    <input type="date" id="fechaEntrada">
                    <label>Fecha de Salida:</label>
                    <input type="date" id="fechaSalida">
                </div>
            </div>


            
            <div class="form-group">
                <label>Mensaje:</label>
                <textarea id="mensajeRespuesta" rows="4" required></textarea>
            </div>
            
            <button type="submit" class="btn-success" id="btnEnviarRespuesta">Enviar Respuesta</button>
            <button type="button" onclick="cerrarModalAceptar()">Cancelar</button>
        </form>
    </div>
</div>

<!-- Template para turno (oculto) -->
<template id="templateTurno">
    <div class="turno-item" style="border: 1px solid #ddd; padding: 10px; margin-bottom: 10px; border-radius: 5px;">
        <div style="display: flex; gap: 10px; align-items: flex-start;">
            <div style="flex: 1;">
                <label>Turno:</label>
                <select class="select-turno" required>
                    <option value="">Seleccionar turno...</option>
                </select>
            </div>
            <div style="flex: 2;">
                <label>Días:</label>
                <div class="dias-checkboxes" style="display: flex; gap: 5px; flex-wrap: wrap;">
                    <label><input type="checkbox" value="Lunes"> Lun</label>
                    <label><input type="checkbox" value="Martes"> Mar</label>
                    <label><input type="checkbox" value="Miércoles"> Mié</label>
                    <label><input type="checkbox" value="Jueves"> Jue</label>
                    <label><input type="checkbox" value="Viernes"> Vie</label>
                </div>
            </div>
            <button type="button" class="btn-danger btn-sm btn-eliminar-turno" style="margin-top: 20px;">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    </div>
</template>
