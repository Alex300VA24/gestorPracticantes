<?php
session_start();
use App\Helpers\Permisos;
require_once __DIR__ . '/../../app/helpers/Permisos.php';

// Validar sesión
if (!isset($_SESSION['usuarioID'])) {
    header("Location: /login");
    exit;
}

// 🔹 Obtener permisos según cargo
$cargoID = $_SESSION['cargoID'];
$modoEmergencia = false; // podrías traerlo desde la BD si implementas la tabla ConfiguracionSistema
$permisos = Permisos::obtenerPermisos($cargoID, $modoEmergencia);
?>

<?php $titulo = "Dashboard Principal"; ?>
<?php include __DIR__ . "/../layouts/header.php"; ?>

<div class="dashboard-container" id="dashboardContainer">
    <?php include __DIR__ . "/../layouts/sidebar.php"; ?>

    <div class="main-content">

        <?php if (in_array('inicio', $permisos)): ?>
        <div id="pageInicio" class="page-content active">
            <?php include __DIR__ . "/pages/inicio.php"; ?>
        </div>
        <?php endif; ?>

        <?php if (in_array('practicantes', $permisos)): ?>
        <div id="pagePracticantes" class="page-content">
            <?php include __DIR__ . "/pages/practicantes.php"; ?>
        </div>
        <?php endif; ?>

        <?php if (in_array('asistencias', $permisos)): ?>
        <div id="pageAsistencias" class="page-content">
            <?php include __DIR__ . "/pages/asistencias.php"; ?>
        </div>
        <?php endif; ?>

        <?php if (in_array('documentos', $permisos)): ?>
        <div id="pageDocumentos" class="page-content">
            <?php include __DIR__ . "/pages/documentos.php"; ?>
        </div>
        <?php endif; ?>

        <?php if (in_array('reportes', $permisos)): ?>
        <div id="pageReportes" class="page-content">
            <?php include __DIR__ . "/pages/reportes.php"; ?>
        </div>
        <?php endif; ?>

        <?php if (in_array('certificados', $permisos)): ?>
        <div id="pageCertificados" class="page-content">
            <?php include __DIR__ . "/pages/certificados.php"; ?>
        </div>
        <?php endif; ?>

    </div>
</div>


<?php include __DIR__ . "/../layouts/footer.php"; ?>
