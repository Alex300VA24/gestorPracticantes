<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['authenticated']) || !$_SESSION['authenticated']) {
    header('Location: ' . BASE_URL . 'login');
    exit;
}

$nombreUsuario = $_SESSION['nombreUsuario'] ?? 'Usuario';
$nombreCargo   = $_SESSION['nombreCargo'] ?? 'Sin cargo';
$nombreArea    = $_SESSION['nombreArea'] ?? 'Sin área';

// Obtener usuario actual desde la sesión
$usuario = $_SESSION['usuario'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Principal - Gestión de Practicantes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/dashboard.css">
        
</head>
<body>
    <div class="dashboard-container" id="dashboardContainer">
        <!-- === SIDEBAR === -->
        <div class="sideBar">
            <div>
                <div class="sidebar-header">
                    <img class="sidebar-logo" src="<?= BASE_URL ?>assets/images/logo.png" alt="Logo">
                    <div class="sidebar-title">MDE</div>
                    <div class="sidebar-subtitle">Gestión de Practicantes</div>
                </div>

                <div class="option active" onclick="showPage('inicio', this)">
                    <div class="containerIconOption"><i class="fas fa-home"></i></div>
                    <p>Inicio</p>
                </div>
                <div class="option" onclick="showPage('practicantes', this)">
                    <div class="containerIconOption"><i class="fas fa-users"></i></div>
                    <p>Practicantes</p>
                </div>
                <div class="option" onclick="showPage('asistencias', this)">
                    <div class="containerIconOption"><i class="fas fa-calendar-check"></i></div>
                    <p>Asistencias</p>
                </div>
                <div class="option" onclick="showPage('documentos', this)">
                    <div class="containerIconOption"><i class="fas fa-file-alt"></i></div>
                    <p>Documentos</p>
                </div>
                <div class="option" onclick="showPage('reportes', this)">
                    <div class="containerIconOption"><i class="fas fa-chart-bar"></i></div>
                    <p>Reportes</p>
                </div>
                <div class="option" onclick="showPage('certificados', this)">
                    <div class="containerIconOption"><i class="fas fa-certificate"></i></div>
                    <p>Certificados</p>
                </div>
            </div>

            <div class="user-info">
                <div><strong id="currentUserName"><?= htmlspecialchars($nombreUsuario) ?></strong></div>
                <div id="currentUserRole">
                    <?= htmlspecialchars($nombreCargo) ?> - <?= htmlspecialchars($nombreArea) ?>
                </div>

                <button class="logout-btn" id="btnLogout" onclick="cerrarSesion()">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </button>
            </div>


        </div>

        <!-- === CONTENIDO PRINCIPAL === -->
        <div class="main-content">
            <div id="pageInicio" class="page-content active">
                <div class="page-header">
                    <h1>Dashboard Principal</h1>
                    <p class="page-subtitle">Panel de control del sistema de gestión de practicantes</p>
                </div>
            </div>

            <div id="pagePracticantes" class="page-content">
                <div class="page-header">
                    <h1>Gestión de Practicantes</h1>
                    <p class="page-subtitle">Administrar solicitudes y estados de practicantes</p>
                </div>

                <?php include __DIR__ . '/practicantes/listado.php'; ?>
            </div>


            <div id="pageAsistencias" class="page-content">
                <div class="page-header">
                    <h1>Control de Asistencias</h1>
                </div>
            </div>

            <div id="pageDocumentos" class="page-content">
                <div class="page-header">
                    <h1>Gestión de Documentos</h1>
                    <p class="page-subtitle">Administrar documentos requeridos y cartas de aceptación</p>
                </div>
                <?php include __DIR__ . '/documentos/documentos.php'; ?>
            </div>

            <div id="pageReportes" class="page-content">
                <div class="page-header">
                    <h1>Reportes del Sistema</h1>
                </div>
            </div>

            <div id="pageCertificados" class="page-content">
                <div class="page-header">
                    <h1>Certificados de Prácticas</h1>
                </div>
            </div>
        </div>
    </div>

    <!-- === SCRIPT JS === -->
    <script src="<?= BASE_URL ?>assets/js/api.js"></script>

    <script>
        function showPage(pageId, element) {
            document.querySelectorAll('.page-content').forEach(p => p.classList.remove('active'));
            document.getElementById('page' + capitalize(pageId)).classList.add('active');

            document.querySelectorAll('.option').forEach(o => o.classList.remove('active'));
            element.classList.add('active');
        }

        function capitalize(str) {
            return str.charAt(0).toUpperCase() + str.slice(1);
        }

        document.getElementById('btnLogout').addEventListener('click', async function () {
            try {
                await api.logout();
                window.location.href = "<?= BASE_URL ?>login";
            } catch (error) {
                alert('Error al cerrar sesión');
            }
        });

        async function verPracticante(id) {
            try {
                const res = await api.getPracticante(id);
                const p = res.data;

                alert(`👀 Detalles del practicante:
                Nombre: ${p.Nombres} ${p.ApellidoPaterno}
                DNI: ${p.DNI}
                Carrera: ${p.Carrera}
                Universidad: ${p.Universidad}
                Area: ${p.Area}
                Direccion: ${p.Direccion}
                Telefono: ${p.Telefono}
                Email: ${p.Email}
                Estado: ${p.Estado}`);
            } catch (err) {
                alert("Error al obtener practicante: " + err.message);
            }
        }

        async function editarPracticante(id) {
            try {
                // 1️⃣ Obtener los datos del practicante desde la API
                const practicante = await api.getPracticante(id);

                // 2️⃣ Mostrar el modal
                const modal = document.getElementById("editarPracticanteModal");
                modal.style.display = "block";

                // 3️⃣ Llenar los campos del formulario con los datos existentes
                const form = document.getElementById("formEditarPracticante");
                form.ID.value = practicante.ID || id;
                form.DNI.value = practicante.DNI || "";
                form.Nombres.value = practicante.Nombres || "";
                form.ApellidoPaterno.value = practicante.ApellidoPaterno || "";
                form.ApellidoMaterno.value = practicante.ApellidoMaterno || "";
                form.Carrera.value = practicante.Carrera || "";
                form.Email.value = practicante.Email || "";
                form.Telefono.value = practicante.Telefono || "";
                form.Direccion.value = practicante.Direccion || "";
                form.Universidad.value = practicante.Universidad || "";
                form.FechaEntrada.value = practicante.FechaEntrada ? practicante.FechaEntrada.split("T")[0] : "";
                form.FechaSalida.value = practicante.FechaSalida ? practicante.FechaSalida.split("T")[0] : "";

                // 4️⃣ Configurar el envío del formulario
                form.onsubmit = async (e) => {
                    e.preventDefault();

                    const datosActualizados = {
                        DNI: form.DNI.value,
                        Nombres: form.Nombres.value,
                        ApellidoPaterno: form.ApellidoPaterno.value,
                        ApellidoMaterno: form.ApellidoMaterno.value,
                        Carrera: form.Carrera.value,
                        Email: form.Email.value,
                        Telefono: form.Telefono.value,
                        Direccion: form.Direccion.value,
                        Universidad: form.Universidad.value,
                        FechaEntrada: form.FechaEntrada.value,
                        FechaSalida: form.FechaSalida.value,
                    };

                    try {
                        const res = await api.actualizarPracticante(id, datosActualizados);
                        alert(res.message || "Practicante actualizado correctamente");
                        cerrarModalEditar();
                        location.reload();
                    } catch (err) {
                        alert("Error al actualizar practicante: " + err.message);
                    }
                };
            } catch (error) {
                console.error("Error al obtener practicante:", error);
                alert("No se pudieron cargar los datos del practicante");
            }
        }

    function cerrarModalEditar() {
        document.getElementById("editarPracticanteModal").style.display = "none";
    }



        async function eliminarPracticante(id) {
            if (!confirm("¿Seguro que deseas eliminar este practicante?")) return;

            try {
                const res = await api.delete(`/practicantes/${id}`);
                alert(res.message);
                location.reload();
            } catch (err) {
                alert("Error al eliminar practicante: " + err.message);
            }
        }


        // listar practicantes
        document.addEventListener('DOMContentLoaded', async () => {
            try {
                const response = await api.getPracticantes();
                const practicantes = response.data || [];

                // Usa el tbody dentro de #tablaPracticantes
                const tbody = document.querySelector('#tablaPracticantes tbody');
                if (!tbody) {
                    console.error('❌ No se encontró el tbody de la tabla de practicantes.');
                    return;
                }

                tbody.innerHTML = '';

                if (practicantes.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="8" class="text-center">No hay practicantes registrados</td></tr>`;
                    return;
                }

                practicantes.forEach(p => {
                    const estadoClass = p.Estado ? p.Estado.toLowerCase() : 'pendiente';
                    const estadoBadge = `<span class="status-badge status-${estadoClass}">${estadoClass.toUpperCase()}</span>`;

                    const fila = document.createElement('tr');
                    fila.innerHTML = `
                        <td>${p.PracticanteID}</td>
                        <td>${p.DNI}</td>
                        <td>${p.NombreCompleto}</td>
                        <td>${p.Carrera || '-'}</td>
                        <td>${p.Universidad}</td>
                        <td>${p.FechaEntrada ? new Date(p.FechaEntrada).toLocaleDateString() : '-'}</td>
                        <td>${p.Area || '-'}</td>
                        <td>${estadoBadge}</td>
                        <td>
                            <button class="btn-primary" style="padding: 8px 12px; font-size: 0.8rem;">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-success" style="padding: 8px 12px; font-size: 0.8rem;" >
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn-warning" style="padding: 8px 12px; font-size: 0.8rem;">
                                <i class="fas fa-check"></i>
                            </button>
                            <button class="btn-danger" style="padding: 8px 12px; font-size: 0.8rem;">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    `;
                    tbody.appendChild(fila);
                    fila.querySelector('.btn-primary').addEventListener('click', () => editarPracticante(p.PracticanteID));
                    fila.querySelector('.btn-success').addEventListener('click', () => verPracticante(p.PracticanteID));
                    fila.querySelector('.btn-danger').addEventListener('click', () => eliminarPracticante(p.PracticanteID));

                });

            } catch (error) {
                console.error('❌ Error al listar practicantes:', error);
                const tbody = document.querySelector('#tablaPracticantes tbody');
                if (tbody) {
                    tbody.innerHTML = `<tr><td colspan="8" class="text-center text-danger">Error al cargar los practicantes</td></tr>`;
                }
            }
        });



    // NUEVO PRACTICANTE

    document.getElementById('btnNuevoPracticante').addEventListener('click', () => {
        document.getElementById('nuevoPracticanteModal').style.display = 'flex';
    });

    function cerrarModal() {
        document.getElementById('nuevoPracticanteModal').style.display = 'none';
    }



    document.getElementById('formNuevoPracticante').addEventListener('submit', async function (e) {
        e.preventDefault();
        const formData = Object.fromEntries(new FormData(this).entries());

        try {
            const res = await api.crearPracticante(formData);
            alert(res.message || 'Practicante registrado correctamente');
            cerrarModal();
            location.reload(); // recarga la tabla
        } catch (error) {
            alert('Error al crear practicante');
            console.error(error);
        }
    });

    </script>


</body>
</html>

