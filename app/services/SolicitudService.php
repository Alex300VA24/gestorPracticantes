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

    // Agregar a SolicitudService

    public function obtenerSolicitudPorPracticante($practicanteID) {
        return $this->repo->obtenerSolicitudPorPracticante($practicanteID);
    }

    public function crearSolicitud($practicanteID) {
        return $this->repo->crearSolicitud($practicanteID);
    }
    public function obtenerSolicitudPorID($solicitudID) {
        return $this->repo->obtenerSolicitudPorID($solicitudID);
    }


    // Agregar a SolicitudService

    public function eliminarDocumento($documentoID) {
        return $this->repo->eliminarDocumento($documentoID);
    }


    public function verificarEstado($solicitudID) {
        $solicitud = $this->repo->obtenerSolicitudPorID($solicitudID);
        $estado = $this->repo->obtenerEstado($solicitudID);

        return [
            'enviada' => $solicitud ? true : false, // ahora es booleano
            'estado' => $estado ?? [
                'abreviatura' => 'REV',
                'descripcion' => 'En Revisión'
            ],
            'aprobada' => isset($estado['abreviatura']) && strtoupper(trim($estado['abreviatura'])) === 'APR'

        ];
    }
}
