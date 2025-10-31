<?php
namespace App\Controllers;

use App\Services\DashboardService;

class DashboardController {
    private $service;

    public function __construct() {
        $this->service = new DashboardService();
    }

    public function obtenerDatosInicio() {
        header('Content-Type: application/json');

        try {
            // Leer área desde query string (?areaID=3)
            $areaID = $_GET['areaID'] ?? null;

            // Crear texto de depuración
            $debugData = [
                'timestamp' => date('Y-m-d H:i:s'),
                'areaID_recibido' => $areaID,
                'query_string' => $_SERVER['QUERY_STRING'] ?? '',
                'request_uri' => $_SERVER['REQUEST_URI'] ?? ''
            ];

            // Guardar datos en debug.txt (modo append)
            $debugFile = __DIR__ . '/../../debug.txt';
            file_put_contents($debugFile, print_r($debugData, true) . "\n", FILE_APPEND);

            // Llamar al servicio
            $data = $this->service->obtenerDatosInicio($areaID);

            echo json_encode([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            // También logueamos los errores
            $errorLog = [
                'timestamp' => date('Y-m-d H:i:s'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ];
            $debugFile = __DIR__ . '/../../debug.txt';
            file_put_contents($debugFile, print_r($errorLog, true) . "\n", FILE_APPEND);

            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener los datos del inicio',
                'error' => $e->getMessage()
            ]);
        }
    }


}

