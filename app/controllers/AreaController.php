<?php
namespace App\Controllers;

use App\Services\AreaService;

class AreaController {
    private $service;
    
    public function __construct() {
        $this->service = new AreaService();
        $this->checkAuth();
    }
    
    private function checkAuth() {
        session_start();
        if (!isset($_SESSION['authenticated']) || !$_SESSION['authenticated']) {
            $this->jsonResponse(['success' => false, 'message' => 'No autorizado'], 401);
        }
    }
    
    public function listar() {
        try {
            $areas = $this->service->listarAreas();
            $this->jsonResponse([
                'success' => true,
                'data' => $areas
            ]);
        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    protected function jsonResponse($data, $status = 200) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($status);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}