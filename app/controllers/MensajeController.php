<?php
namespace App\Controllers;

use App\Services\MensajeService;

class MensajeController {
    private $service;
    
    public function __construct() {
        $this->service = new MensajeService();
        $this->checkAuth();
    }
    
    private function checkAuth() {
        session_start();
        if (!isset($_SESSION['authenticated']) || !$_SESSION['authenticated']) {
            $this->jsonResponse(['success' => false, 'message' => 'No autorizado'], 401);
        }
    }
    
    public function enviarSolicitud() {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            $resultado = $this->service->enviarSolicitudArea(
                $data['solicitudID'],
                $data['remitenteAreaID'],
                $data['destinatarioAreaID'],
                $data['contenido']
            );
            
            $this->jsonResponse([
                'success' => true,
                'message' => 'Solicitud enviada correctamente',
                'data' => $resultado
            ]);
        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    public function responderSolicitud() {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            $this->service->responderSolicitud(
                $data['mensajeID'],
                $data['respuesta'],
                $data['contenido']
            );
            
            $this->jsonResponse([
                'success' => true,
                'message' => 'Respuesta enviada correctamente'
            ]);
        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    public function listarMensajes($areaID) {
        try {
            $mensajes = $this->service->listarMensajesPorArea($areaID);
            $this->jsonResponse([
                'success' => true,
                'data' => $mensajes
            ]);
        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function eliminarMensaje($mensajeID) {
        try {
            $resultado = $this->service->eliminarMensaje($mensajeID);

            if ($resultado) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Mensaje eliminado correctamente'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'No se encontró el mensaje o no se pudo eliminar.'
                ]);
            }

        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al eliminar mensaje: ' . $e->getMessage()
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