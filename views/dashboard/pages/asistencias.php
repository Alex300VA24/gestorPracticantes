<div class="asistencias-container">

    <div class="page-header">
        <h1>Control de Asistencias</h1>
        <p class="page-subtitle">Gestionar registro de entrada y salida de practicantes</p>
    </div>

    <div class="action-buttons">
        <button class="btn-info" onclick="generarReporte()">
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
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Practicante</th>
                        <th>Turno</th>
                        <th>Hora Entrada</th>
                        <th>Hora Salida</th>
                        <th>Tiempo</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tableAsistenciasBody">
                    <!-- Datos dinámicos -->
                </tbody>
            </table>
        </div>
    </div>
</div>
