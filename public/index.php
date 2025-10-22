<?php
require_once __DIR__ . '/../autoload.php';


// Para no mostrar los errores
error_reporting();
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
    // Rutas de Usuario/Auth
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
    
    // Rutas de Practicantes
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
            $controller->eliminar($practicanteID); // ✅ Agrega este bloque
        }
        break;
    

    // Solicitudes / Documentos
    case $path === '/api/solicitudes/listarPracticantes':
        $controller = new \App\Controllers\SolicitudController();
        $controller->listarPracticantes();
        break;

    case preg_match('#^/api/solicitudes/documentos$#', $path):
        header('Content-Type: application/json; charset=utf-8');
        $controller = new \App\Controllers\SolicitudController();
        $controller->obtenerDocumentosPorPracticante();
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


    // Rutas de Asistencias
    case $path === '/api/asistencias':
        $controller = new \App\Controllers\AsistenciaController();
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
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
    
    // Vista de Login
    case $path === '/' || $path === '/login':
        require __DIR__ . '/../views/login.php';
        break;
        
    // Vista de Dashboard
    case $path === '/dashboard':
        if (!isset($_SESSION['authenticated']) || !$_SESSION['authenticated']) {
            header('Location: /login');
            exit;
        }
        require __DIR__ . '/../views/dashboard/index.php';
        break;
    
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Ruta no encontrada']);
        break;
}
