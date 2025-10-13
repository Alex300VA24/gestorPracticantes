<?php $titulo = "Dashboard Principal"; ?>
<?php include __DIR__ . "/../layouts/header.php"; ?>

<div class="dashboard-container" id="dashboardContainer">
    <?php include __DIR__ . "/../layouts/sidebar.php"; ?>

    <div class="main-content">
        <div id="pageInicio" class="page-content active">
            <?php include __DIR__ . "/pages/inicio.php"; ?>
        </div>

        <div id="pagePracticantes" class="page-content">
            <?php include __DIR__ . "/pages/practicantes.php"; ?>
        </div>

        <div id="pageAsistencias" class="page-content">
            <?php include __DIR__ . "/pages/asistencias.php"; ?>
        </div>

        <div id="pageDocumentos" class="page-content">
            <?php include __DIR__ . "/pages/documentos.php"; ?>
        </div>

        <div id="pageReportes" class="page-content">
            <?php include __DIR__ . "/pages/reportes.php"; ?>
        </div>

        <div id="pageCertificados" class="page-content">
            <?php include __DIR__ . "/pages/certificados.php"; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . "/../layouts/footer.php"; ?>
