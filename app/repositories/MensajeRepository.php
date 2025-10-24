<?php
namespace App\Repositories;

use App\Config\Database;
use PDO;

class MensajeRepository {
    private $conn;
    
    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }
    
    public function enviarSolicitudArea($solicitudID, $remitenteAreaID, $destinatarioAreaID, $contenido) {
        $stmt = $this->conn->prepare("EXEC sp_EnviarSolicitudArea ?, ?, ?, ?");
        $stmt->execute([$solicitudID, $remitenteAreaID, $destinatarioAreaID, $contenido]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function responderSolicitud($mensajeID, $respuesta, $contenido) {
        $stmt = $this->conn->prepare("EXEC sp_ResponderSolicitud ?, ?, ?");
        return $stmt->execute([$mensajeID, $respuesta, $contenido]);
    }
    
    public function listarMensajesPorArea($areaID) {
        $stmt = $this->conn->prepare("EXEC sp_ListarMensajesPorArea ?");
        $stmt->execute([$areaID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}