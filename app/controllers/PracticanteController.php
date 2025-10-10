<?php
namespace App\Controllers;

use App\Services\PracticanteService;

class PracticanteController {
    private $practicanteService;
    
    public function __construct() {
        $this->practicanteService = new PracticanteService();
        $this->checkAuth();
    }
    
    // Metodo para saber si el usuario esta autenticado y darle permiso a ver el dashboard
    private function checkAuth() {
        session_start();
        if (!isset($_SESSION['authenticated']) || !$_SESSION['authenticated']) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'No autorizado'
            ], 401);
        }
    }
    
    // Metodo listarPracticantes para llamar a services a la logica de negocio
    public function listarPracticantes() {
        try {
            $practicantes = $this->practicanteService->listarPracticantes();
            $this->jsonResponse([
                'success' => true,
                'data' => $practicantes
            ]);
        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    // Metodo para obtener Practicante por su ID 
    public function obtener($practicanteID) {
        try {
            $practicante = $this->practicanteService->obtenerPorId($practicanteID);
            $this->jsonResponse([
                'success' => true,
                'data' => $practicante
            ]);
        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }
    
    // Metodo para registrar un nuevo practicante
    public function registrarPracticante() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new \Exception("Método no permitido");
            }

            $json = file_get_contents('php://input');
            $data = json_decode($json, true);

            if (!$data) {
                throw new \Exception("Datos JSON inválidos o vacíos");
            }

            $practicanteID = $this->practicanteService->registrarPracticante($data);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Practicante registrado exitosamente',
                'data' => ['practicanteID' => $practicanteID]
            ], 201);

        } catch (\Throwable $e) {
            error_log("❌ Error al crear practicante: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }


    // PUT /api/practicantes/{id}
    public function actualizar($id) {
        $body = json_decode(file_get_contents("php://input"), true);
        $msg = $this->practicanteService->actualizar($id, $body);
        echo json_encode(['success' => true, 'message' => $msg]);
    }

    public function eliminar($id){
        header('Content-Type: application/json');

        try {
            $repository = new \App\Repositories\PracticanteRepository();
            $resultado = $repository->eliminar($id);

            if ($resultado) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Practicante eliminado correctamente'
                ]);
            } else {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'No se pudo eliminar el practicante'
                ]);
            }
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error interno del servidor: ' . $e->getMessage()
            ]);
        }
    }



    
    protected function jsonResponse($data, $status = 200) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($status);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

}
