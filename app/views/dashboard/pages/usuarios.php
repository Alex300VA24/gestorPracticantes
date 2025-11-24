<div class="usuarios-container">

    <div class="page-header">
        
        <h1><i class="fas fa-users-cog"></i> Gestión de Usuarios</h1>
        <p class="page-subtitle">Administrar usuarios y permisos del sistema</p>
    </div>
    
    <!-- ============================================
         BOTONES DE ACCIÓN PRINCIPAL
         ============================================ -->
    <div class="action-buttons">
        <button class="btn btn-primary" id="btnNuevoUsuario">
            <i class="fas fa-user-plus"></i>
            Nuevo Usuario
        </button>
    </div>

    <!-- ============================================
         ESTADÍSTICAS DE USUARIOS
         ============================================ -->
    <div class="stats-grid">
        <!-- Total de Usuarios -->
        <div class="stat-card" style="color: #667eea;">
            <div class="stat-icon" style="color: #667eea;">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-number" id="totalUsuarios">0</div>
            <div class="stat-label">Total Usuarios</div>
        </div>

        <!-- Usuarios Activos -->
        <div class="stat-card" style="color: #28a745;">
            <div class="stat-icon" style="color: #28a745;">
                <i class="fas fa-user-check"></i>
            </div>
            <div class="stat-number" id="usuariosActivos">0</div>
            <div class="stat-label">Usuarios Activos</div>
        </div>

        <div class="stat-card" style="color: #8b5cf6;">
            <div class="stat-icon" style="color: red;">
                <i class="fas fa-user-times"></i>
            </div>
            <div class="stat-number" id="usuariosInactivos">0</div>
            <div class="stat-label">Usuarios Inactivos</div>
        </div>

        <!-- Gerentes de Área -->
        <div class="stat-card" style="color: #f59e0b;">
            <div class="stat-icon" style="color: #f59e0b;">
                <i class="fas fa-user-tie"></i>
            </div>
            <div class="stat-number" id="gerentesArea">0</div>
            <div class="stat-label">Gerentes de Área</div>
        </div>
    </div>

    <!-- ============================================
         FILTROS DE BÚSQUEDA
         ============================================ -->
    <div class="content-card">
        <h3 class="card-title">
            <i class="fas fa-filter"></i>
            Filtros
        </h3>
        <div class="card-body">
            <div class="practicantes-form" style="grid-template-columns: 1fr 1fr 1fr auto; align-items: end;">
                <!-- Filtro por Nombre/Usuario -->
                <div class="form-group mb-0" style="padding-bottom: 8px;">
                    <label for="filtroUsuario" class="form-label">
                        <i class="fas fa-search"></i>
                        Buscar:
                    </label>
                    <input type="text" 
                           id="filtroUsuario" 
                           class="form-control" 
                           placeholder="Nombre o usuario..."
                           autocomplete="off">
                </div>

                <!-- Filtro por Rol -->
                <div class="form-group mb-0" style="padding-bottom: 8px;">
                    <label for="filtroRol" class="form-label">
                        <i class="fas fa-user-tag"></i>
                        Cargo:
                    </label>
                    <select id="filtroRol" class="form-control">
                        <option value="">Todos los roles</option>
                        <option value="gerente_rrhh">Gerente RRHH</option>
                        <option value="gerente_area">Gerente de Área</option>
                        <option value="usuario_area">Usuario de Área</option>
                    </select>
                </div>

                <!-- Filtro por Área -->
                <div class="form-group mb-0" style="padding-bottom: 30px;">
                    <label for="filtroAreaUsuario" class="form-label">
                        <i class="fas fa-building"></i>
                        Área:
                    </label>
                    <select id="filtroAreaUsuario" class="form-control">
                        <option value="">Todas las áreas</option>
                        <!-- Se llenará dinámicamente -->
                    </select>
                </div>
                <!-- Botón Aplicar -->
                <button class="btn-primary" onclick="aplicarFiltrosUsuarios()" type="button">
                    <i class="fas fa-filter"></i>
                    Aplicar Filtros
                </button>
            </div>
        </div>
    </div>

    <!-- ============================================
         TABLA DE USUARIOS
         ============================================ -->
    <div class="content-card">
        <div class="card-title">
            <span>
                <i class="fas fa-list"></i>
                Lista de Usuarios
            </span>
            <span class="badge badge-info" id="totalRegistrosUsuarios">
                0 usuarios
            </span>
        </div>
        <div class="card-body">
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th><i class="fas fa-hashtag"></i> ID</th>
                            <th><i class="fas fa-user"></i> Usuario</th>
                            <th><i class="fas fa-id-card"></i> Nombre Completo</th>
                            <th><i class="fas fa-id-badge"></i> CUI</th>
                            <th><i class="fas fa-user-tag"></i> Cargo</th>
                            <th><i class="fas fa-building"></i> Área</th>
                            <th><i class="fas fa-calendar-plus"></i> Fecha Registro</th>
                            <th><i class="fas fa-toggle-on"></i> Estado</th>
                            <th><i class="fas fa-cog"></i> Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaUsuariosBody">
                        <tr>
                            <td colspan="9" class="text-center">
                                <div class="empty-state">
                                    <i class="fas fa-spinner fa-spin empty-state-icon"></i>
                                    <p class="empty-state-text">Cargando usuarios...</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ============================================
     MODAL: NUEVO/EDITAR USUARIO
     ============================================ -->
<div id="modalUsuario" class="modal">
    <div class="modal-content-custom" style="max-width: 700px;">
        <div class="modal-header-custom">
            <h3 class="modal-title" id="tituloUsuarioModal">
                <i class="fas fa-user-plus"></i>
                Nuevo Usuario
            </h3>
            <button class="close" onclick="cerrarModalUsuario()" type="button">
                &times;
            </button>
        </div>

        <form id="formUsuario">
            <div class="modal-body-custom">
                <!-- ID Oculto -->
                <input type="hidden" id="usuarioID" name="usuarioID">

                <!-- ============================================
                     DATOS DE ACCESO
                     ============================================ -->
                <div class="section-header">
                    <i class="fas fa-key"></i>
                    Datos de Acceso
                </div>
                
                <!-- Usuario -->
                <div class="form-group">
                    <label for="nombreUsuario" class="form-label">
                        <i class="fas fa-user"></i>
                        Nombre de Usuario: *
                    </label>
                    <input type="text" 
                           id="nombreUsuario" 
                           name="usuario"
                           class="form-control" 
                           placeholder="Ej: jperez"
                           autocomplete="off"
                           required>
                    <small class="text-muted">
                        <i class="fas fa-info-circle"></i>
                        Solo letras minúsculas y números, sin espacios
                    </small>
                </div>

                <!-- Contraseña -->
                <div class="form-group">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock"></i>
                        Contraseña: <span id="passwordLabel">*</span>
                    </label>
                    <div style="position: relative;">
                        <input type="password" 
                               id="password" 
                               name="password"
                               class="form-control" 
                               placeholder="••••••••"
                               style="padding-right: 45px;">
                        <i class="fas fa-eye-slash" 
                           id="togglePassword1"
                           onclick="togglePasswordVisibility('password', 'togglePassword1')"
                           style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #6b7280;"></i>
                    </div>
                    <small class="text-muted">
                        <i class="fas fa-shield-alt"></i>
                        Mínimo 8 caracteres, incluir mayúsculas, números y símbolos
                    </small>
                    <div id="passwordStrength" style="margin-top: 0.5rem; display: none;"></div>
                </div>

                <!-- Confirmar Contraseña -->
                <div class="form-group">
                    <label for="confirmarPassword" class="form-label">
                        <i class="fas fa-lock"></i>
                        Confirmar Contraseña: <span id="confirmarPasswordLabel">*</span>
                    </label>
                    <div style="position: relative;">
                        <input type="password" 
                               id="confirmarPassword" 
                               name="confirmarPassword"
                               class="form-control" 
                               placeholder="••••••••"
                               style="padding-right: 45px;">
                        <i class="fas fa-eye-slash" 
                           id="togglePassword2"
                           onclick="togglePasswordVisibility('confirmarPassword', 'togglePassword2')"
                           style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #6b7280;"></i>
                    </div>
                    <div id="passwordMatch" style="margin-top: 0.5rem; display: none;"></div>
                </div>

                <!-- ============================================
                     DATOS PERSONALES
                     ============================================ -->
                <div class="section-header">
                    <i class="fas fa-id-card"></i>
                    Datos Personales
                </div>

                <!-- Nombre Completo -->
                <div class="form-group">
                    <label for="nombreCompleto" class="form-label">
                        <i class="fas fa-user"></i>
                        Nombre Completo: *
                    </label>
                    <input type="text" 
                           id="nombreCompleto" 
                           name="nombreCompleto"
                           class="form-control" 
                           placeholder="Ej: Juan Carlos Pérez García"
                           required>
                </div>

                <!-- DNI + CUI -->
                <div class="form-group">
                    <label for="cuiUsuario" class="form-label">
                        <i class="fas fa-id-badge"></i>
                        Documento de Identidad (DNI): *
                    </label>
                    <input type="text" 
                           id="cuiUsuario" 
                           name="cui"
                           class="form-control" 
                           placeholder="9 dígitos del DNI"
                           maxlength="9"
                           required>
                    <small class="text-muted">
                        <i class="fas fa-info-circle"></i>
                        Ingrese los 9 dígitos del DNI (incluye el CUI)
                    </small>
                </div>

                <!-- ============================================
                     PERMISOS Y ASIGNACIÓN
                     ============================================ -->
                <div class="section-header">
                    <i class="fas fa-user-shield"></i>
                    Permisos y Asignación
                </div>

                <div class="practicantes-form">
                    <!-- Rol -->
                    <div class="form-group">
                        <label for="rolUsuario" class="form-label">
                            <i class="fas fa-user-tag"></i>
                            Rol del Usuario: *
                        </label>
                        <select id="rolUsuario" 
                                name="rol"
                                class="form-control"
                                required>
                            <option value="">-- Seleccionar rol --</option>
                            <option value="gerente_rrhh">Gerente RRHH</option>
                            <option value="gerente_area">Gerente de Área</option>
                            <option value="usuario_area">Usuario de Área</option>
                            <option value="gerente_sistemas">Gerente de Sistemas</option>
                        </select>
                    </div>

                    <!-- Área -->
                    <div class="form-group">
                        <label for="areaUsuario" class="form-label">
                            <i class="fas fa-building"></i>
                            Área: *
                        </label>
                        <select id="areaUsuario" 
                                name="areaID"
                                class="form-control"
                                required>
                            <option value="">-- Seleccionar área --</option>
                            <!-- Se llenará dinámicamente -->
                        </select>
                    </div>
                </div>

                <!-- Descripción de Roles -->
                <div class="alert alert-info" style="margin-top: 1rem; padding: 1rem; background: #e0f2fe; border-left: 4px solid #0891b2;">
                    <strong><i class="fas fa-info-circle"></i> Descripción de Roles:</strong>
                    <ul style="margin: 0.5rem 0 0 1.5rem; font-size: 0.9rem;">
                        <li><strong>Gerente RRHH:</strong> Acceso total al sistema</li>
                        <li><strong>Gerente de Área:</strong> Gestiona practicantes de su área</li>
                        <li><strong>Usuario de Área:</strong> Registra asistencias y consulta información</li>
                    </ul>
                </div>

                <!-- Estado (solo en edición) -->
                <div class="form-group" id="estadoUsuarioGroup" style="display: none;">
                    <label for="estadoUsuario" class="form-label">
                        <i class="fas fa-toggle-on"></i>
                        Estado:
                    </label>
                    <select id="estadoUsuario" name="estado" class="form-control">
                        <option value="1">Activo</option>
                        <option value="0">Inactivo</option>
                    </select>
                </div>

                <div class="modal-footer-custom">
                    <button type="button" 
                            onclick="cerrarModalUsuario()" 
                            class="btn btn-secondary">
                        <i class="fas fa-times"></i>
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i>
                        Guardar Usuario
                    </button>
                </div>
            </div>         
        </form>
    </div>
</div>
<!-- ============================================
     MODAL: CAMBIAR CONTRASEÑA
     ============================================ -->
<div id="modalCambiarPassword" class="modal">
    <div class="modal-content-custom" style="max-width: 500px;">
        <div class="modal-header-custom">
            <h3 class="modal-title">
                <i class="fas fa-key"></i>
                Cambiar Contraseña
            </h3>
            <button class="close" onclick="cerrarModalPassword()" type="button">
                &times;
            </button>
        </div>

        <form id="formCambiarPassword">
            <div class="modal-body-custom">
                <input type="hidden" id="passwordUsuarioID">

                <div class="alert alert-warning" style="padding: 1rem; background: #fef3c7; border-left: 4px solid #f59e0b; margin-bottom: 1.5rem;">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Atención:</strong> Esta acción cambiará la contraseña del usuario seleccionado.
                </div>

                <!-- Usuario (solo lectura) -->
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-user"></i>
                        Usuario:
                    </label>
                    <input type="text" 
                           id="passwordNombreUsuario"
                           class="form-control" 
                           readonly
                           style="background: #f3f4f6;">
                </div>

                <!-- Nueva Contraseña -->
                <div class="form-group">
                    <label for="nuevaPassword" class="form-label">
                        <i class="fas fa-lock"></i>
                        Nueva Contraseña: *
                    </label>
                    <div style="position: relative;">
                        <input type="password" 
                               id="nuevaPassword" 
                               class="form-control" 
                               placeholder="••••••••"
                               style="padding-right: 45px;"
                               required>
                        <i class="fas fa-eye-slash" 
                           id="togglePassword3"
                           onclick="togglePasswordVisibility('nuevaPassword', 'togglePassword3')"
                           style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #6b7280;"></i>
                    </div>
                </div>

                <!-- Confirmar Nueva Contraseña -->
                <div class="form-group">
                    <label for="confirmarNuevaPassword" class="form-label">
                        <i class="fas fa-lock"></i>
                        Confirmar Nueva Contraseña: *
                    </label>
                    <div style="position: relative;">
                        <input type="password" 
                               id="confirmarNuevaPassword" 
                               class="form-control" 
                               placeholder="••••••••"
                               style="padding-right: 45px;"
                               required>
                        <i class="fas fa-eye-slash" 
                           id="togglePassword4"
                           onclick="togglePasswordVisibility('confirmarNuevaPassword', 'togglePassword4')"
                           style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #6b7280;"></i>
                    </div>
                    <div id="passwordMatch2" style="margin-top: 0.5rem; display: none;"></div>
                </div>
                <div class="modal-footer-custom">
                <button type="button" 
                        onclick="cerrarModalPassword()" 
                        class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                    Cancelar
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-key"></i>
                    Cambiar Contraseña
                </button>
            </div>
            </div>
        </form>
    </div>
</div>

<style>
    .usuarios-container{
        width: 100%;
        padding: var(--spacing-xl);
        max-width: 1440px;
        margin: 0 auto;
        font-family: var(--font-sans);
        color: var(--gray-900);
        line-height: 1.6;
    }
    /* Estilos para el modal body con padding lateral */
.modal-body-custom {
    padding: 2rem 2.5rem;
    max-height: 70vh;
    overflow-y: auto;
}

/* Estilos para los encabezados de sección del modal */
.section-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 0.75rem 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 1.1rem;
    font-weight: 600;
    box-shadow: 0 2px 4px rgba(102, 126, 234, 0.2);
}

.section-header i {
    font-size: 1.2rem;
}

/* Espaciado entre form groups */
.modal-body-custom .form-group {
    margin-bottom: 1.5rem;
}

/* Footer del modal con padding lateral */
.modal-footer {
    padding: 1.5rem 2.5rem;
    border-top: 1px solid #e5e7eb;
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    background: #f9fafb;
}

/* Ajuste para el header del modal */
.modal-header-custom {
    padding: 1.5rem 2.5rem;
    border-bottom: 2px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: white;
}

/* Espaciado para el alert de información */
.alert {
    margin: 1rem 0;
    padding: 1rem;
    border-radius: 8px;
}

.alert-info {
    background: #e0f2fe;
    border-left: 4px solid #0891b2;
    color: #0c4a6e;
}

/* Grid para los campos en dos columnas */
.practicantes-form {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin-bottom: 1rem;
}

/* Responsive */
@media (max-width: 768px) {
    .modal-body-custom {
        padding: 1.5rem 1rem;
    }
    
    .modal-header-custom {
        padding: 1rem 1rem;
    }
    
    .modal-footer {
        padding: 1rem 1rem;
    }
    
    .practicantes-form {
        grid-template-columns: 1fr;
    }
}

/* Estilos para los inputs del modal */
.modal-body-custom .form-control {
    padding: 0.75rem 1rem;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 0.95rem;
    transition: all 0.2s;
}

.modal-body-custom .form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    outline: none;
}

/* Labels del formulario */
.form-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 500;
    color: #374151;
    margin-bottom: 0.5rem;
    font-size: 0.95rem;
}

.form-label i {
    color: #667eea;
}

/* Texto muted */
.text-muted {
    color: #6b7280;
    font-size: 0.875rem;
    display: flex;
    align-items: center;
    gap: 0.25rem;
    margin-top: 0.5rem;
}

.text-muted i {
    color: #9ca3af;
}

/* Scroll personalizado para el modal body */
.modal-body-custom::-webkit-scrollbar {
    width: 8px;
}

.modal-body-custom::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.modal-body-custom::-webkit-scrollbar-thumb {
    background: #d1d5db;
    border-radius: 4px;
}

.modal-body-custom::-webkit-scrollbar-thumb:hover {
    background: #9ca3af;
}

/* Ajuste para el contenedor del modal */
.modal-content-custom {
    background: white;
    border-radius: 12px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    width: 100%;
    max-width: 700px;
    margin: 2rem auto;
    animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
    from {
        transform: translateY(-20px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

/* Modal backdrop */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
    padding: 1rem;
}

.modal.active {
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Botones del modal */
.modal-footer .btn {
    padding: 0.75rem 1.5rem;
    border-radius: 6px;
    font-weight: 500;
    font-size: 0.95rem;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    border: none;
    transition: all 0.2s;
}

.modal-footer .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.modal-footer .btn-secondary {
    background: #6b7280;
    color: white;
}

.modal-footer .btn-secondary:hover {
    background: #4b5563;
}

.modal-footer .btn-success {
    background: #10b981;
    color: white;
}

.modal-footer .btn-success:hover {
    background: #059669;
}

/* Botón de cerrar del header */
.modal-header-custom .close {
    background: transparent;
    border: none;
    font-size: 2rem;
    color: #6b7280;
    cursor: pointer;
    line-height: 1;
    padding: 0;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    transition: all 0.2s;
}

.modal-header-custom .close:hover {
    background: #f3f4f6;
    color: #374151;
}

/* Título del modal */
.modal-title {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 1.5rem;
    font-weight: 600;
    color: #1f2937;
    margin: 0;
}

.modal-title i {
    color: #667eea;
}

.swal2-container {
    z-index: 99999 !important;
}

</style>