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
      <input type="text" id="Area" name="Area" placeholder="Área">
      <input type="date" id="FechaEntrada" name="FechaEntrada">
      <input type="date" id="FechaSalida" name="FechaSalida">
      <button type="submit">Guardar</button>
      <button type="button" onclick="cerrarModal()">Cancelar</button>
    </form>

  </div>
</div>




