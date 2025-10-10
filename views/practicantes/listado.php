<div class="practicantes-container">
    <div class="action-buttons">
        <button class="btn-primary" id="btnNuevoPracticante"><i class="fas fa-plus"></i> Nuevo Practicante</button>
        <button class="btn-success" id="btnExportarPracticantes"><i class="fas fa-download"></i> Exportar Lista</button>
        <button class="btn-warning" id="btnMostrarPendientes"><i class="fas fa-exclamation-triangle"></i> Ver Pendientes</button>

    </div>

    <div class="content-card">
        <h3 class="card-title">Lista de Practicantes</h3>
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

<!-- Modal para crear practicante -->
<div id="nuevoPracticanteModal" class="modal" style="display:none;">
  <div class="modal-content">
    <h3>Nuevo Practicante</h3>
    <form id="formNuevoPracticante">
      <input type="text" name="DNI" placeholder="DNI" required>
      <input type="text" name="Nombres" placeholder="Nombres" required>
      <input type="text" name="ApellidoPaterno" placeholder="Apellido Paterno" required>
      <input type="text" name="ApellidoMaterno" placeholder="Apellido Materno" required>
      <input type="text" name="Carrera" placeholder="Carrera" required>
      <input type="email" name="Email" placeholder="Correo">
      <input type="text" name="Telefono" placeholder="Teléfono">
      <input type="text" name="Direccion" placeholder="Dirección">
      <input type="text" name="Universidad" placeholder="Universidad">
      <input type="date" name="FechaEntrada" required>
      <input type="date" name="FechaSalida" required>
      <button type="submit">Guardar</button>
      <button type="button" onclick="cerrarModal()">Cancelar</button>
    </form>
  </div>
</div>


<!-- Modal para editar practicante -->
<div id="editarPracticanteModal" class="modal" style="display:none;">
  <div class="modal-content">
    <h3>Editar Practicante</h3>
    <form id="formEditarPracticante">
      <input type="hidden" name="ID">

      <input type="text" name="DNI" placeholder="DNI" required>
      <input type="text" name="Nombres" placeholder="Nombres" required>
      <input type="text" name="ApellidoPaterno" placeholder="Apellido Paterno" required>
      <input type="text" name="ApellidoMaterno" placeholder="Apellido Materno" required>
      <input type="text" name="Carrera" placeholder="Carrera" required>
      <input type="email" name="Email" placeholder="Correo">
      <input type="text" name="Telefono" placeholder="Teléfono">
      <input type="text" name="Direccion" placeholder="Dirección">
      <input type="text" name="Universidad" placeholder="Universidad">
      <input type="date" name="FechaEntrada" required>
      <input type="date" name="FechaSalida" required>

      <button type="submit">Actualizar</button>
      <button type="button" onclick="cerrarModalEditar()">Cancelar</button>
    </form>
  </div>
</div>





