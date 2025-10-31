<?php
namespace App\Services;

use App\Repositories\DashboardRepository;

class DashboardService {
    private $repo;

    public function __construct() {
        $this->repo = new DashboardRepository();
    }

    public function obtenerDatosInicio($areaID = null) {
    return [
        'totalPracticantes' => $this->repo->obtenerTotalPracticantes($areaID),
        'pendientesAprobacion' => $this->repo->obtenerPendientesAprobacion($areaID),
        'practicantesActivos' => $this->repo->obtenerPracticantesActivos($areaID),
        'asistenciaHoy' => $this->repo->obtenerAsistenciasHoy($areaID)
    ];
}

}
