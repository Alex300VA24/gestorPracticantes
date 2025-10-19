<div class="action-buttons">
    <button class="btn-success" onclick="">
        <i class="fas fa-sign-in-alt"></i> Registrar Entrada
    </button>
    <button class="btn-warning" onclick="">
        <i class="fas fa-sign-out-alt"></i> Registrar Salida
    </button>
    <button class="btn-info" onclick="">
        <i class="fas fa-file-export"></i> Generar Reporte
    </button>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon" style="color: #28a745;">
            <i class="fas fa-user-check"></i>
        </div>
        <div class="stat-number" id="presentesHoy">0</div>
        <div class="stat-label">Presentes Hoy</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="color: #dc3545;">
            <i class="fas fa-user-times"></i>
        </div>
        <div class="stat-number" id="ausentesHoy">0</div>
        <div class="stat-label">Ausentes Hoy</div>
    </div>
</div>

<div class="content-card">
    <h3 class="card-title">Registro de Asistencia - Hoy</h3>
    <table class="table" id="tableAsistencias">
        <thead>
            <tr>
                <th>Practicante</th>
                <th>Área</th>
                <th>Hora Entrada</th>
                <th>Hora Salida</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody id="tableAsistenciasBody">
            <!-- Datos dinámicos -->
        </tbody>
    </table>
</div>
