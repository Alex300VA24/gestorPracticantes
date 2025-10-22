<?php
namespace App\Services;

use App\Repositories\AsistenciaRepository;

class AsistenciaService {
    private $repository;

    public function __construct() {
        date_default_timezone_set('America/Lima'); // ✅ Ajustar zona horaria
        $this->repository = new AsistenciaRepository();
    }

    public function registrarEntrada($practicanteID) {
        try {
            if (empty($practicanteID)) {
                throw new \Exception("Datos incompletos en service");
            }

            $fecha = date('Y-m-d');
            $hora = date('H:i:s');

            // Verificar si ya existe asistencia hoy
            if ($this->repository->existeAsistencia($practicanteID, $fecha)) {
                throw new \Exception("Ya se registró entrada hoy");
            }

            // Registrar entrada
            $resultado = $this->repository->registrarEntrada($practicanteID, $hora);

            return [
                "success" => $resultado['success'],
                "message" => $resultado['message']
            ];

        } catch (\Throwable $e) {
            error_log("Service registrarEntrada: " . $e->getMessage());
            throw $e;
        }
    }

    public function registrarSalida($practicanteID) {
        try {
            if (empty($practicanteID)) {
                throw new \Exception("Datos incompletos salida service");
            }

            $fecha = date('Y-m-d');
            $hora = date('H:i:s');

            $resultado = $this->repository->registrarSalida($practicanteID, $hora);

            return [
                "success" => $resultado['success'],
                "message" => $resultado['message']
            ];

        } catch (\Throwable $e) {
            error_log("Service registrarSalida: " . $e->getMessage());
            throw $e;
        }
    }

    public function listarAsistencias() {
        try {
            $fecha = date('Y-m-d');
            $asistencias = $this->repository->obtenerAsistenciasPorFecha($fecha);
            return $asistencias;
        } catch (\Throwable $e) {
            error_log("Error en Service listarAsistencias: " . $e->getMessage());
            throw $e;
        }
    }
}
