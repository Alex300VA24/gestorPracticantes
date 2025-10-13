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

        <button class="logout-btn" id="btnLogout">
            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
        </button>
    </div>
</div>
