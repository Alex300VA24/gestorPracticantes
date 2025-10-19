<?php
namespace App\Services;

use App\Repositories\SolicitudRepository;

class SolicitudService {
    private $repo;

    public function __construct() {
        $this->repo = new SolicitudRepository();
    }

    public function listarNombresPracticantes() {
        return $this->repo->listarNombresPracticantes();
    }

    public function obtenerDocumentosPorPracticante($id) {
        return $this->repo->obtenerDocumentosPorPracticante($id);
        
    }

    public function subirDocumento($id, $tipo, $archivo, $observaciones = null) {
        return $this->repo->subirDocumento($id, $tipo, $archivo, $observaciones);
    }

    public function actualizarDocumento($id, $tipo = null, $archivo = null, $observaciones = null) {
        return $this->repo->actualizarDocumento($id, $tipo, $archivo, $observaciones);
    }

    public function obtenerDocumentoPorTipoYPracticante($practicanteID, $tipoDocumento) {
        return $this->repo->obtenerDocumentoPorTipoYPracticante($practicanteID, $tipoDocumento);
    }
}
