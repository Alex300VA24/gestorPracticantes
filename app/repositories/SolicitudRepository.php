<?php
namespace App\Repositories;

use App\Config\Database;
use App\Models\SolicitudPracticas;
use PDO;
use PDOException;

class SolicitudRepository {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function listarNombresPracticantes() {
        $stmt = $this->conn->prepare("EXEC sp_ListarNombresPracticantes");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerDocumentosPorPracticante($id) {
        try {

            $stmt = $this->conn->prepare("EXEC sp_ObtenerDocumentosPorPracticante @PracticanteID = :PracticanteID");
            $stmt->bindParam(':PracticanteID', $id, PDO::PARAM_INT);
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) return null;

            $solicitud = new SolicitudPracticas();
            $solicitud->setSolicitudID($row['SolicitudID']);
            $solicitud->setFechaSolicitud($row['FechaSolicitud'] ?? null);
            $solicitud->setEstadoID($row['EstadoID'] ?? null);
            $solicitud->setPracticanteID($id);
            $solicitud->setAreaID($row['AreaID'] ?? null);
            $solicitud->setDocCV($row['DocCV']);
            $solicitud->setDocCartaPresentacionUniversidad($row['DocCartaPresentacionUniversidad']);
            $solicitud->setDocCarnetVacunacion($row['DocCarnetVacunacion']);
            $solicitud->setDocDNI($row['DocDNI']);

            return $solicitud;

        } catch (\Exception $e) {
            error_log("❌ Error en obtenerDocumentosPorPracticante: " . $e->getMessage());
            return null;
        }
    }


    public function subirDocumento($id, $tipo, $archivo) {
        $sql = "EXEC sp_SubirDocumento ?, ?, ?";
        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(1, (int)$id, PDO::PARAM_INT);
        $stmt->bindValue(2, $tipo, PDO::PARAM_STR);
        $stmt->bindParam(3, $archivo, PDO::PARAM_LOB, 0, PDO::SQLSRV_ENCODING_BINARY);

        $res = $stmt->execute();
        if ($res === false) {
            $err = $stmt->errorInfo();
            throw new \Exception("Ejecutar SP falló: " . json_encode($err));
        }

        return $res;
    }



}
