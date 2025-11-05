<div class="asistencias-container">

    <div class="page-header">
        <h1>Control de Asistencias</h1>
        <p class="page-subtitle">Gestionar registro de entrada y salida de practicantes</p>
    </div>

    <div class="action-buttons">
        <button class="btn-info" onclick="generarReporte()">
            <i class="fas fa-file-export"></i> Generar Reporte
        </button>
        <button class="btn-primary" onclick="cargarAsistencias()">
            <i class="fas fa-sync-alt"></i> Actualizar
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
                        <th>Turno(s)</th>
                        <th>Hora Entrada</th>
                        <th>Hora Salida</th>
                        <th>Tiempo Efectivo</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tableAsistenciasBody">
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 20px;">
                            <i class="fas fa-spinner fa-spin"></i> Cargando datos...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- El modal se genera dinámicamente desde JavaScript -->