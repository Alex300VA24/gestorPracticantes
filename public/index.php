<?php
require_once __DIR__ . '/../autoload.php';

// Para no mostrar los errores
error_reporting(0);
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);

// Iniciar la sesion
session_start();

// Constante BASE_URL
define('BASE_URL', '/gestorPracticantes/public/');

// Configurar headers para CORS si es necesario
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Router simple
$request_uri = $_SERVER['REQUEST_URI'];
$script_name = dirname($_SERVER['SCRIPT_NAME']);
$path = str_replace($script_name, '', $request_uri);
$path = parse_url($path, PHP_URL_PATH);

if ($path[0] !== '/') {
    $path = '/' . $path;
}

error_log("PATH RECIBIDO: " . $path);

// Rutas de la API
switch (true) {
    // ============================================
    // RUTAS DE USUARIO/AUTH
    // ============================================
    case preg_match('#^/api/login$#', $path):
        $controller = new \App\Controllers\UsuarioController();
        $controller->login();
        break;
        
    case preg_match('#^/api/validar-cui$#', $path):
        $controller = new \App\Controllers\UsuarioController();
        $controller->validarCUI();
        break;
        
    case preg_match('#^/api/logout$#', $path):
        $controller = new \App\Controllers\UsuarioController();
        $controller->logout();
        break;

    // ============================================
    // RUTA DE INICIO/DASHBOARD
    // ============================================
    case preg_match('#^/api/inicio$#', $path):
        $controller = new \App\Controllers\DashboardController();
        $controller->obtenerDatosInicio();
        break;

    // ============================================
    // RUTAS DE PRACTICANTES
    // ============================================
    case $path === '/api/practicantes/filtrar':
        $controller = new \App\Controllers\PracticanteController();
        $controller->filtrarPracticantes();
        break;
    
    case $path === '/api/practicantes/aceptar':
        $controller = new \App\Controllers\PracticanteController();
        $controller->aceptarPracticante();
        break;
    
    case $path === '/api/practicantes/rechazar':
        $controller = new \App\Controllers\PracticanteController();
        $controller->rechazarPracticante();
        break;

    case preg_match('#^/api/practicantes$#', $path):
        $controller = new \App\Controllers\PracticanteController();
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $controller->listarPracticantes();
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->registrarPracticante();
        }
        break;
    
    case preg_match('#^/api/practicantes/(\d+)$#', $path, $matches):
        $controller = new \App\Controllers\PracticanteController();
        $practicanteID = $matches[1];

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $controller->obtener($practicanteID);
        } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            $controller->actualizar($practicanteID);
        } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            $controller->eliminar($practicanteID);
        }
        break;

    // ============================================
    // RUTAS DE SOLICITUDES / DOCUMENTOS
    // ============================================
    case $path === '/api/solicitudes/listarPracticantes':
        $controller = new \App\Controllers\SolicitudController();
        $controller->listarPracticantes();
        break;

    case $path === '/api/solicitudes/por-practicante':
        $controller = new \App\Controllers\SolicitudController();
        $controller->obtenerSolicitudPorPracticante();
        break;

    case preg_match('#^/api/solicitudes/documentos$#', $path):
        header('Content-Type: application/json; charset=utf-8');
        $controller = new \App\Controllers\SolicitudController();
        $controller->obtenerDocumentosPorPracticante();
        break;

    case $path === '/api/solicitudes/crearSolicitud':
        $controller = new \App\Controllers\SolicitudController();
        $controller->crearSolicitud();
        break;

    case $path === '/api/solicitudes/subirDocumento':
        $controller = new \App\Controllers\SolicitudController();
        $controller->subirDocumento();
        break;
    
    case $path === '/api/solicitudes/actualizarDocumento':
        $controller = new \App\Controllers\SolicitudController();
        $controller->actualizarDocumento();
        break;
    
    case $path === '/api/solicitudes/obtenerPorTipoYPracticante':
        $controller = new \App\Controllers\SolicitudController();
        $controller->obtenerDocumentoPorTipoYPracticante();
        break;
    
    case $path === '/api/solicitudes/estado':
        $controller = new \App\Controllers\SolicitudController();
        $solicitudID = $_GET['solicitudID'] ?? null; // 👈 obtiene el parámetro de la URL
        $controller->verificarEstado($solicitudID);
        break;
    
    case $path === '/api/solicitudes/obtenerSolicitud':
        $controller = new \App\Controllers\SolicitudController();
        $controller->obtenerSolicitudPorID();
        break;
    
    case $path === '/api/solicitudes/eliminarDocumento':
        $controller = new \App\Controllers\SolicitudController();
        $controller->eliminarDocumento();
        break;

    // Agregar esta ruta en la sección de ASISTENCIAS
    case $path === '/api/asistencias/obtener':
        $controller = new \App\Controllers\AsistenciaController();
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $controller->obtenerAsistenciaCompleta();
        }
        break;


    // ============================================
    // RUTAS DE MENSAJES
    // ============================================
    case $path === '/api/mensajes/enviar':
        $controller = new \App\Controllers\MensajeController();
        $controller->enviarSolicitud();
        break;
    
    case $path === '/api/mensajes/responder':
        $controller = new \App\Controllers\MensajeController();
        $controller->responderSolicitud();
        break;
    
    case preg_match('#^/api/mensajes/(\d+)$#', $path, $matches):
        $controller = new \App\Controllers\MensajeController();

        if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            $controller->eliminarMensaje($matches[1]);
        } else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $controller->listarMensajes($matches[1]);
        } else {
            header('Content-Type: application/json', true, 405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
        }
        break;

    // ============================================
    // RUTAS DE ÁREAS
    // ============================================
    case $path === '/api/areas':
        $controller = new \App\Controllers\AreaController();
        $controller->listar();
        break;

    // ============================================
    // RUTAS DE TURNOS
    // ============================================
    case $path === '/api/turnos':
        $controller = new \App\Controllers\TurnoController();
        $controller->listar();
        break;
    
    case preg_match('#^/api/turnos/practicante/(\d+)$#', $path, $matches):
        $controller = new \App\Controllers\TurnoController();
        $controller->obtenerPorPracticante($matches[1]);
        break;

    // ============================================
    // RUTAS DE ASISTENCIAS (Reemplazar la sección existente)
    // ============================================
    case $path === '/api/asistencias':
        $controller = new \App\Controllers\AsistenciaController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->listarAsistencias();
        }
        break;

    case $path === '/api/asistencias/entrada':
        $controller = new \App\Controllers\AsistenciaController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->registrarEntrada();
        }
        break;

    case $path === '/api/asistencias/salida':
        $controller = new \App\Controllers\AsistenciaController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->registrarSalida();
        }
        break;

    case $path === '/api/asistencias/pausa/iniciar':
        $controller = new \App\Controllers\AsistenciaController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->iniciarPausa();
        }
        break;

    case $path === '/api/asistencias/pausa/finalizar':
        $controller = new \App\Controllers\AsistenciaController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->finalizarPausa();
        }
        break;

    // ============================================
    // RUTAS DE VISTAS
    // ============================================
    case $path === '/' || $path === '/login':
        require __DIR__ . '/../views/login.php';
        break;
        
    case $path === '/dashboard':
        if (!isset($_SESSION['authenticated']) || !$_SESSION['authenticated']) {
            require __DIR__ . '/../views/login.php';
            exit;
        }
        require __DIR__ . '/../views/dashboard/index.php';
        break;
    
    // ============================================
    // RUTA 404
    // ============================================
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Ruta no encontrada: ' . $path]);
        break;
}