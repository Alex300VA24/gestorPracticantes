<?php
$pageTitle = 'Dashboard';
$currentPage = 'inicio';
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<div class="main-content">
    <div class="page-header">
        <h1 class="page-title">Dashboard Principal</h1>
        <p class="page-subtitle">Panel de control del sistema de gestión de practicantes</p>
        <div class="breadcrumb">
            <a href="/dashboard"><i class="fas fa-home"></i> Inicio</a>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon primary">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-number" id="totalPracticantes">0</div>
            <div class="stat-label">Total Practicantes</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon warning">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-number" id="pendientesAprobacion">0</div>
            <div class="stat-label">Pendientes de Aprobación</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon success">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-number" id="practicantesActivos">0</div>
            <div class="stat-label">Practicantes Activos</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon info">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="stat-number" id="asistenciaHoy">0</div>
            <div class="stat-label">Asistencias Hoy</div>
        </div>
    </div>

    <div class="content-card">
        <h3 class="card-title">Actividad Reciente</h3>
        <div id="actividadReciente">
            <div class="loader"></div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', async function() {
    await cargarEstadisticas();
    await cargarActividadReciente();
});

async function cargarEstadisticas() {
    try {
        const response = await api.getPracticantes();
        
        if (response.success) {
            const practicantes = response.data;
            document.getElementById('totalPracticantes').textContent = practicantes.length;
            
            // Aquí debes ajustar según los estadoID reales de tu BD
            const pendientes = practicantes.filter(p => p.estadoID === 1).length;
            const activos = practicantes.filter(p => p.estadoID === 2).length;
            
            document.getElementById('pendientesAprobacion').textContent = pendientes;
            document.getElementById('practicantesActivos').textContent = activos;
        }
        
        // Cargar asistencias de hoy
        const asistenciasResponse = await api.get('/asistencias/fecha?fecha=' + new Date().toISOString().split('T')[0]);
        if (asistenciasResponse.success) {
            document.getElementById('asistenciaHoy').textContent = asistenciasResponse.data.length;
        }
        
    } catch (error) {
        console.error('Error al cargar estadísticas:', error);
    }
}

async function cargarActividadReciente() {
    const container = document.getElementById('actividadReciente');
    
    try {
        const response = await api.getPracticantes();
        
        if (response.success && response.data.length > 0) {
            const ultimos = response.data.slice(0, 5);
            
            let html = '<div style="display: grid; gap: 15px;">';
            ultimos.forEach(p => {
                const colorBorder = getColorByEstado(p.estadoID);
                html += `
                    <div style="padding: 15px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid ${colorBorder};">
                        <strong>📋 ${p.nombres} ${p.apellidoPaterno}</strong>
                        <div style="font-size: 0.9rem; color: #666; margin-top: 5px;">
                            ${p.universidad} - ${p.fechaEntrada || 'Fecha pendiente'}
                        </div>
                    </div>
                `;
            });
            html += '</div>';
            
            container.innerHTML = html;
        } else {
            container.innerHTML = '<p class="text-muted">No hay actividad reciente</p>';
        }
    } catch (error) {
        container.innerHTML = '<p class="text-muted">Error al cargar actividad reciente</p>';
    }
}

function getColorByEstado(estadoID) {
    const colores = {
        1: '#ffc107',
        2: '#28a745',
        3: '#dc3545',
        4: '#6f42c1'
    };
    return colores[estadoID] || '#6c757d';
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>