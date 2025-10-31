<?php
namespace App\Controllers;

use App\Models\Practicante;
use App\Services\AsistenciaService;

class AsistenciaController {
    private $service;

    public function __construct() {
        $this->service = new AsistenciaService();
    }

    public function registrarEntrada() {
        try {
            $data = json_decode(file_get_contents("php://input"), true);

            $practicanteID = $data['practicanteID'] ?? $data['practicante_id'] ?? null;

            if (empty($data['practicanteID'])) {
                echo json_encode(['success' => false, 'message' => 'Datos incompletos', 'debug' => $data]);
                return;
            }

            $response = $this->service->registrarEntrada($practicanteID);
            $this->jsonResponse($response);

        } catch (\Throwable $e) {
            error_log("Error en registrarEntrada: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function registrarSalida() {
        try {
            $data = json_decode(file_get_contents("php://input"), true);

            $practicanteID = $data['practicanteID'] ?? $data['practicante_id'] ?? null;
            if (empty($practicanteID)) {
                throw new \Exception("Datos incompletos");
            }

            $response = $this->service->registrarSalida($practicanteID);
            $this->jsonResponse($response);

        } catch (\Throwable $e) {
            error_log("Error en registrarSalida: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }



    public function listarAsistencias() {
        try {
            // Leer el cuerpo JSON enviado desde JS
            $input = json_decode(file_get_contents('php://input'), true);
            $areaID = $input['areaID'] ?? null;

            // 🔹 Escribir en archivo para verificar que llega el valor
            $logPath = __DIR__ . '/debug_area_log.txt';
            $logMessage = date('Y-m-d H:i:s') . " - areaID recibido: " . var_export($areaID, true) . PHP_EOL;
            file_put_contents($logPath, $logMessage, FILE_APPEND);

            if (!$areaID) {
                throw new \Exception("El parámetro areaID es requerido.");
            }

            // Llamar al service con el área específica
            $response = $this->service->listarAsistencias($areaID);

            $this->jsonResponse([
                'success' => true,
                'data' => $response
            ]);
        } catch (\Throwable $e) {
            // Registrar también el error en el mismo archivo
            $logPath = __DIR__ . '/debug_area_log.txt';
            $errorMessage = date('Y-m-d H:i:s') . " - ERROR: " . $e->getMessage() . PHP_EOL;
            file_put_contents($logPath, $errorMessage, FILE_APPEND);

            header('Content-Type: application/json; charset=utf-8');
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }





    protected function jsonResponse($data, $status = 200) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($status);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
