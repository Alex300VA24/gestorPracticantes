<div class="sidebar">
    <div class="sidebar-header">
        <img class="sidebar-logo" src="/assets/images/logo.png" alt="Logo MDE">
        <div class="sidebar-title">MDE</div>
        <div class="sidebar-subtitle">Gestión de Practicantes</div>
    </div>

    <div class="sidebar-menu">
        <a href="/dashboard" class="menu-item <?= ($currentPage ?? '') === 'inicio' ? 'active' : '' ?>">
            <i class="fas fa-home"></i>
            <span>Inicio</span>
        </a>

        <a href="/dashboard/practicantes" class="menu-item <?= ($currentPage ?? '') === 'practicantes' ? 'active' : '' ?>">
            <i class="fas fa-users"></i>
            <span>Practicantes</span>
        </a>

        <a href="/dashboard/asistencias" class="menu-item <?= ($currentPage ?? '') === 'asistencias' ? 'active' : '' ?>">
            <i class="fas fa-calendar-check"></i>
            <span>Asistencias</span>
        </a>

        <a href="/dashboard/documentos" class="menu-item <?= ($currentPage ?? '') === 'documentos' ? 'active' : '' ?>">
            <i class="fas fa-file-alt"></i>
            <span>Documentos</span>
        </a>

        <a href="/dashboard/certificados" class="menu-item <?= ($currentPage ?? '') === 'certificados' ? 'active' : '' ?>">
            <i class="fas fa-certificate"></i>
            <span>Certificados</span>
        </a>

        <a href="/dashboard/reportes" class="menu-item <?= ($currentPage ?? '') === 'reportes' ? 'active' : '' ?>">
            <i class="fas fa-chart-bar"></i>
            <span>Reportes</span>
        </a>
    </div>

    <div class="user-info-sidebar">
        <div class="user-name"><?= htmlspecialchars($nombreCompleto) ?></div>
        <div class="user-role">
            <?php
            $roles = [1 => 'RRHH', 2 => 'Gerente', 3 => 'Mesa de Partes', 4 => 'Portero'];
            echo $roles[$cargoID] ?? 'Usuario';
            ?>
        </div>
        <button class="logout-btn" onclick="logout()">
            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
        </button>
    </div>
</div>