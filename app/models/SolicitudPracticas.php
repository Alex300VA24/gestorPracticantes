<?php
namespace App\Models;

class SolicitudPracticas {
    private $solicitudID;
    private $fechaSolicitud;
    private $estadoID;
    private $practicanteID;
    private $areaID;

    private $docCV;
    private $docCartaPresentacionUniversidad;
    private $docCarnetVacunacion;
    private $docDNI;

    // Getters
    public function getSolicitudID() { return $this->solicitudID; }
    public function getFechaSolicitud() { return $this->fechaSolicitud; }
    public function getEstadoID() { return $this->estadoID; }
    public function getPracticanteID() { return $this->practicanteID; }
    public function getAreaID() { return $this->areaID; }
    public function getDocCV() { return $this->docCV; }
    public function getDocCartaPresentacionUniversidad() { return $this->docCartaPresentacionUniversidad; }
    public function getDocCarnetVacunacion() { return $this->docCarnetVacunacion; }
    public function getDocDNI() { return $this->docDNI; }

    // Setters
    public function setSolicitudID($solicitudID) { $this->solicitudID = $solicitudID; }
    public function setFechaSolicitud($fechaSolicitud) { $this->fechaSolicitud = $fechaSolicitud; }
    public function setEstadoID($estadoID) { $this->estadoID = $estadoID; }
    public function setPracticanteID($practicanteID) { $this->practicanteID = $practicanteID; }
    public function setAreaID($areaID) { $this->areaID = $areaID; }
    public function setDocCV($docCV) { $this->docCV = $docCV; }
    public function setDocCartaPresentacionUniversidad($docCartaPresentacionUniversidad) { $this->docCartaPresentacionUniversidad = $docCartaPresentacionUniversidad; }
    public function setDocCarnetVacunacion($docCarnetVacunacion) { $this->docCarnetVacunacion = $docCarnetVacunacion; }
    public function setDocDNI($docDNI) { $this->docDNI = $docDNI; }

    // Convertir a arreglo (para JSON)
    public function toArray() {
        return [
            'solicitudID' => $this->solicitudID,
            'fechaSolicitud' => $this->fechaSolicitud,
            'estadoID' => $this->estadoID,
            'practicanteID' => $this->practicanteID,
            'areaID' => $this->areaID,
            'docCV' => $this->docCV ? base64_encode($this->docCV) : null,
            'docCartaPresentacionUniversidad' => $this->docCartaPresentacionUniversidad ? base64_encode($this->docCartaPresentacionUniversidad) : null,
            'docCarnetVacunacion' => $this->docCarnetVacunacion ? base64_encode($this->docCarnetVacunacion) : null,
            'docDNI' => $this->docDNI ? base64_encode($this->docDNI) : null
        ];
    }
}
