<!-- === PÁGINA REPORTES === -->
<div class="action-buttons">
    <button class="btn-primary" onclick="generarReporteCompleto()">
        <i class="fas fa-file-pdf"></i> Reporte Completo
    </button>
    <button class="btn-success" onclick="exportarAsistencias()">
        <i class="fas fa-calendar-alt"></i> Reporte Asistencias
    </button>
    <button class="btn-warning" onclick="reportePorArea()">
        <i class="fas fa-building"></i> Reporte por Área
    </button>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon" style="color: #667eea;">
            <i class="fas fa-graduation-cap"></i>
        </div>
        <div class="stat-number" id="practicantesCompletados">0</div>
        <div class="stat-label">Prácticas Completadas</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="color: #17a2b8;">
            <i class="fas fa-hourglass-half"></i>
        </div>
        <div class="stat-number" id="promedioHoras">0</div>
        <div class="stat-label">Promedio Horas/Día</div>
    </div>
</div>

<div class="content-card">
    <h3 class="card-title">Filtros de Reporte</h3>
    <form id="formFiltrosReporte">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
            <div class="form-group">
                <label for="fechaInicio">Fecha Inicio:</label>
                <input type="date" id="fechaInicio" name="fechaInicio">
            </div>
            <div class="form-group">
                <label for="fechaFin">Fecha Fin:</label>
                <input type="date" id="fechaFin" name="fechaFin">
            </div>
            <div class="form-group">
                <label for="areaFiltro">Área:</label>
                <select id="areaFiltro" name="areaFiltro">
                    <option value="">Todas las áreas</option>
                    <option value="sistemas">Sistemas</option>
                    <option value="contabilidad">Contabilidad</option>
                    <option value="legal">Legal</option>
                    <option value="obras">Obras</option>
                    <option value="catastro">Catastro</option>
                </select>
            </div>
            <div class="form-group">
                <label for="estadoFiltro">Estado:</label>
                <select id="estadoFiltro" name="estadoFiltro">
                    <option value="">Todos los estados</option>
                    <option value="pendiente">Pendiente</option>
                    <option value="proceso">En Proceso</option>
                    <option value="aprobado">Aprobado</option>
                    <option value="completado">Completado</option>
                </select>
            </div>
        </div>
        <button type="button" class="btn-primary" onclick="aplicarFiltros()">
            <i class="fas fa-filter"></i> Aplicar Filtros
        </button>
    </form>
</div>
