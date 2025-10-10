<?php
$pageTitle = 'Asistencias';
$currentPage = 'asistencias';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-content">
    <div class="page-header">
        <h1 class="page-title">Control de Asistencias</h1>
        <p class="page-subtitle">Gestionar registro de entrada y salida de practicantes</p>
        <div class="breadcrumb">
            <a href="/dashboard"><i class="fas fa-home"></i> Inicio</a>
            <span>/</span>
            <span>Asistencias</span>
        </div>
    </div>

    <div class="action-buttons">
        <button class="btn btn-success" onclick="abrirModalEntrada()">
            <i class="fas fa-sign-in-alt"></i> Registrar Entrada
        </button>
        <button class="btn btn-warning" onclick="abrirModalSalida()">
            <i class="fas fa-sign-out-alt"></i> Registrar Salida
        </button>
        <button class="btn btn-info" onclick="exportarAsistencias()">
            <i class="fas fa-file-export"></i> Exportar Reporte
        </button>
    </div>

    <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
        <div class="stat-card">
            <div class="stat-icon success">
                <i class="fas fa-user-check"></i>
            </div>
            <div class="stat-number" id="presentesHoy">0</div>
            <div class="stat-label">Presentes Hoy</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon danger">
                <i class="fas fa-user-times"></i>
            </div>
            <div class="stat-number" id="ausentesHoy">0</div>
            <div class="stat-label">Ausentes</div>
        </div>
    </div>

    <div class="content-card">
        <h3 class="card-title">Registro de Asistencia</h3>
        
        <div class="form-group" style="max-width: 250px;">
            <label for="fechaFiltro">Filtrar por fecha:</label>
            <input type="date" id="fechaFiltro" class="form-control" value="<?= date('Y-m-d') ?>">
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Practicante</th>
                        <th>DNI</th>
                        <th>Hora Entrada</th>
                        <th>Hora Salida</th>
                        <th>Horas Trabajadas</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tableAsistenciasBody">
                    <tr>
                        <td colspan="7" class="text-center">
                            <div class="loader"></div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Registrar Entrada -->
<div id="modalEntrada" class="modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Registrar Entrada</h2>
                <button class="modal-close" onclick="cerrarModal('modalEntrada')">&times;</button>
            </div>
            <div class="modal-body">
                <form id="formEntrada">
                    <div class="form-group">
                        <label for="practicanteEntrada">Seleccionar Practicante: *</label>
                        <select id="practicanteEntrada" name="practicanteID" class="form-control" required>
                            <option value="">Seleccione...</option>
                        </select>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        La hora de entrada se registrará automáticamente
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" onclick="cerrarModal('modalEntrada')">
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Registrar Entrada
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Registrar Salida -->
<div id="modalSalida" class="modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Registrar Salida</h2>
                <button class="modal-close" onclick="cerrarModal('modalSalida')">&times;</button>
            </div>
            <div class="modal-body">
                <form id="formSalida">
                    <div class="form-group">
                        <label for="practicanteSalida">Seleccionar Practicante: *</label>
                        <select id="practicanteSalida" name="practicanteID" class="form-control" required>
                            <option value="">Seleccione...</option>
                        </select>
                    </div>

                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        Solo se pueden registrar salidas de practicantes con entrada registrada hoy
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" onclick="cerrarModal('modalSalida')">
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save"></i> Registrar Salida
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
let asistencias = [];
let practicantes = [];

document.addEventListener('DOMContentLoaded', async function() {
    await cargarPracticantes();
    await cargarAsistencias();
    configurarFiltroFecha();
});

async function cargarPracticantes() {
    try {
        const response = await api.getPracticantes();
        if (response.success) {
            practicantes = response.data.filter(p => p.estadoID === 3); // Solo aprobados
            
            const selectEntrada = document.getElementById('practicanteEntrada');
            const selectSalida = document.getElementById('practicanteSalida');
            
            practicantes.forEach(p => {
                const option1 = document.createElement('option');
                option1.value = p.practicanteID;
                option1.textContent = `${p.nombres} ${p.apellidoPaterno} - ${p.dni}`;
                selectEntrada.appendChild(option1);
                
                const option2 = document.createElement('option');
                option2.value = p.practicanteID;
                option2.textContent = `${p.nombres} ${p.apellidoPaterno} - ${p.dni}`;
                selectSalida.appendChild(option2);
            });
        }
    } catch (error) {
        console.error('Error al cargar practicantes:', error);
    }
}

async function cargarAsistencias() {
    try {
        showLoader();
        const fecha = document.getElementById('fechaFiltro').value;
        const response = await api.get(`/asistencias/fecha?fecha=${fecha}`);
        
        if (response.success) {
            asistencias = response.data;
            renderizarAsistencias();
            actualizarEstadisticas();
        }
    } catch (error) {
        console.error('Error al cargar asistencias:', error);
        showAlert('Error al cargar asistencias: ' + error.message, 'danger');
    } finally {
        hideLoader();
    }
}

function renderizarAsistencias() {
    const tbody = document.getElementById('tableAsistenciasBody');
    
    if (asistencias.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center">No hay registros de asistencia para esta fecha</td></tr>';
        return;
    }
    
    tbody.innerHTML = '';
    
    asistencias.forEach(a => {
        const tr = document.createElement('tr');
        
        const horasTrabajadas = calcularHoras(a.HoraEntrada, a.HoraSalida);
        const estado = a.HoraSalida ? 
            '<span class="status-badge status-completado">Completo</span>' :
            '<span class="status-badge status-proceso">En curso</span>';
        
        tr.innerHTML = `
            <td>${a.Nombres} ${a.ApellidoPaterno} ${a.ApellidoMaterno}</td>
            <td>${a.DNI || '-'}</td>
            <td>${formatearHora(a.HoraEntrada)}</td>
            <td>${a.HoraSalida ? formatearHora(a.HoraSalida) : '<span class="text-muted">Pendiente</span>'}</td>
            <td>${horasTrabajadas}</td>
            <td>${estado}</td>
            <td>
                ${!a.HoraSalida ? `
                    <button class="btn btn-warning btn-sm" onclick="registrarSalidaDirecta(${a.PracticanteID})" title="Registrar salida">
                        <i class="fas fa-sign-out-alt"></i>
                    </button>
                ` : '-'}
            </td>
        `;
        
        tbody.appendChild(tr);
    });
}

function calcularHoras(entrada, salida) {
    if (!salida) return '-';
    
    const horaEntrada = new Date('1970-01-01 ' + entrada);
    const horaSalida = new Date('1970-01-01 ' + salida);
    const diff = (horaSalida - horaEntrada) / 1000 / 60 / 60;
    
    return diff.toFixed(2) + ' hrs';
}

function formatearHora(hora) {
    if (!hora) return '-';
    return hora.substring(0, 5); // HH:MM
}

function actualizarEstadisticas() {
    const presentes = asistencias.length;
    const totalAprobados = practicantes.length;
    const ausentes = totalAprobados - presentes;
    
    document.getElementById('presentesHoy').textContent = presentes;
    document.getElementById('ausentesHoy').textContent = ausentes;
}

function configurarFiltroFecha() {
    document.getElementById('fechaFiltro').addEventListener('change', cargarAsistencias);
}

function abrirModalEntrada() {
    document.getElementById('formEntrada').reset();
    document.getElementById('modalEntrada').style.display = 'block';
}

function abrirModalSalida() {
    document.getElementById('formSalida').reset();
    document.getElementById('modalSalida').style.display = 'block';
}

function cerrarModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

document.getElementById('formEntrada').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const practicanteID = document.getElementById('practicanteEntrada').value;
    
    try {
        showLoader();
        const response = await api.post('/asistencias/entrada', {
            practicanteID: parseInt(practicanteID)
        });
        
        if (response.success) {
            showAlert('Entrada registrada exitosamente', 'success');
            cerrarModal('modalEntrada');
            await cargarAsistencias();
        }
    } catch (error) {
        showAlert('Error al registrar entrada: ' + error.message, 'danger');
    } finally {
        hideLoader();
    }
});

document.getElementById('formSalida').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const practicanteID = document.getElementById('practicanteSalida').value;
    
    try {
        showLoader();
        const response = await api.post('/asistencias/salida', {
            practicanteID: parseInt(practicanteID)
        });
        
        if (response.success) {
            showAlert('Salida registrada exitosamente', 'success');
            cerrarModal('modalSalida');
            await cargarAsistencias();
        }
    } catch (error) {
        showAlert('Error al registrar salida: ' + error.message, 'danger');
    } finally {
        hideLoader();
    }
});

async function registrarSalidaDirecta(practicanteID) {
    if (!confirm('¿Registrar salida para este practicante?')) return;
    
    try {
        showLoader();
        const response = await api.post('/asistencias/salida', {
            practicanteID: practicanteID
        });
        
        if (response.success) {
            showAlert('Salida registrada exitosamente', 'success');
            await cargarAsistencias();
        }
    } catch (error) {
        showAlert('Error al registrar salida: ' + error.message, 'danger');
    } finally {
        hideLoader();
    }
}

function exportarAsistencias() {
    let csv = 'Fecha,Practicante,DNI,Hora Entrada,Hora Salida,Horas Trabajadas\n';
    
    const fecha = document.getElementById('fechaFiltro').value;
    
    asistencias.forEach(a => {
        const nombreCompleto = `${a.Nombres} ${a.ApellidoPaterno} ${a.ApellidoMaterno}`;
        const horasTrabajadas = calcularHoras(a.HoraEntrada, a.HoraSalida);
        
        csv += `${fecha},"${nombreCompleto}","${a.DNI || ''}","${a.HoraEntrada}","${a.HoraSalida || 'Pendiente'}","${horasTrabajadas}"\n`;
    });
    
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `asistencias_${fecha}.csv`;
    a.click();
    window.URL.revokeObjectURL(url);
    
    showAlert('Reporte exportado exitosamente', 'success');
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>