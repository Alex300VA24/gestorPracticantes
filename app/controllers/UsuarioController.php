<?php
namespace App\Controllers;

use App\Services\UsuarioService;

class UsuarioController {
    private $usuarioService;
    
    public function __construct() {
        $this->usuarioService = new UsuarioService();
    }
    
    // Metodo UsuarioController:login
    public function login() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new \Exception("Método no permitido");
            }
            
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            
            $nombreUsuario = $data['nombreUsuario'] ?? '';
            $password = $data['password'] ?? '';
            
            $usuario = $this->usuarioService->login($nombreUsuario, $password);
            
            // Iniciar sesión
            session_start();
            $_SESSION['usuarioID'] = $usuario->getUsuarioID();
            $_SESSION['nombreUsuario'] = $usuario->getNombreUsuario();
            $_SESSION['nombreCargo'] = $usuario->getCargo()->getNombreCargo();
            $_SESSION['nombreArea'] = $usuario->getArea()->getNombreArea();
            $_SESSION['requireCUI'] = true;
            
            $this->jsonResponse([
                'success' => true,
                'message' => 'Login exitoso',
                'data' => [
                    'usuarioID' => $usuario->getUsuarioID(),
                    'nombreUsuario' => $usuario->getNombreUsuario(),
                    'nombreCompleto' => $usuario->getNombreCompleto(),
                    'cargo' => $usuario->getCargo()->toArray(),
                    'area' => $usuario->getArea()->toArray(),
                    'requireCUI' => true
                ]
            ]);
            
        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    // Metodo UsuarioController:validarCUI
    public function validarCUI() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new \Exception("Método no permitido");
            }
            
            session_start();
            
            if (!isset($_SESSION['usuarioID'])) {
                throw new \Exception("Sesión no iniciada");
            }
            
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            
            $usuarioID = $_SESSION['usuarioID'];
            $cui = $data['cui'] ?? '';
            $usuario = $this->usuarioService->validarCUI($usuarioID, $cui);
        
            // Actualizar sesión
            $_SESSION['authenticated'] = true;
            $_SESSION['requireCUI'] = false;
            $_SESSION['usuario'] = $usuario->toArray();
            
            $this->jsonResponse([
                'success' => true,
                'message' => 'CUI validado correctamente',
                'data' => $usuario->toArray()
            ]);
            
        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    // Metodo UsuarioController:logout()
    public function logout() {
        session_start();
        session_destroy();
        
        $this->jsonResponse([
            'success' => true,
            'message' => 'Sesión cerrada correctamente'
        ]);
    }

    // Metodo para formatear la respuesta en json
    private function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
