<?php
namespace App\Repositories;

use App\Config\Database;

class DashboardRepository {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // 🔹 Total de practicantes (filtrado por área si aplica)
    public function obtenerTotalPracticantes($areaID = null) {
        if ($areaID) {
            $sql = "
                SELECT COUNT(DISTINCT p.PracticanteID) AS total
                FROM Practicante p
                INNER JOIN SolicitudPracticas s ON p.PracticanteID = s.PracticanteID
                WHERE s.AreaID = :areaID
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':areaID', $areaID, \PDO::PARAM_INT);
        } else {
            $sql = "SELECT COUNT(*) AS total FROM Practicante";
            $stmt = $this->db->prepare($sql);
        }

        $stmt->execute();
        return $stmt->fetch()['total'] ?? 0;
    }

    // 🔹 Pendientes de aprobación (EstadoID = 6)
    public function obtenerPendientesAprobacion($areaID = null) {
        if ($areaID) {
            $sql = "
                SELECT COUNT(DISTINCT p.PracticanteID) AS total
                FROM Practicante p
                INNER JOIN SolicitudPracticas s ON p.PracticanteID = s.PracticanteID
                WHERE p.EstadoID = 6 AND s.AreaID = :areaID
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':areaID', $areaID, \PDO::PARAM_INT);
        } else {
            $sql = "SELECT COUNT(*) AS total FROM Practicante WHERE EstadoID = 6";
            $stmt = $this->db->prepare($sql);
        }

        $stmt->execute();
        return $stmt->fetch()['total'] ?? 0;
    }

    // 🔹 Practicantes activos (EstadoID = 7)
    public function obtenerPracticantesActivos($areaID = null) {
        if ($areaID) {
            $sql = "
                SELECT COUNT(DISTINCT p.PracticanteID) AS total
                FROM Practicante p
                INNER JOIN SolicitudPracticas s ON p.PracticanteID = s.PracticanteID
                WHERE p.EstadoID = 7 AND s.AreaID = :areaID
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':areaID', $areaID, \PDO::PARAM_INT);
        } else {
            $sql = "SELECT COUNT(*) AS total FROM Practicante WHERE EstadoID = 7";
            $stmt = $this->db->prepare($sql);
        }

        $stmt->execute();
        return $stmt->fetch()['total'] ?? 0;
    }

    // 🔹 Asistencias de hoy
    public function obtenerAsistenciasHoy($areaID = null) {
        if ($areaID) {
            $sql = "
                SELECT COUNT(DISTINCT a.AsistenciaID) AS total
                FROM Asistencia a
                INNER JOIN Practicante p ON a.PracticanteID = p.PracticanteID
                INNER JOIN SolicitudPracticas s ON p.PracticanteID = s.PracticanteID
                WHERE CAST(a.Fecha AS DATE) = CAST(GETDATE() AS DATE)
                AND s.AreaID = :areaID
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':areaID', $areaID, \PDO::PARAM_INT);
        } else {
            $sql = "
                SELECT COUNT(*) AS total
                FROM Asistencia
                WHERE CAST(Fecha AS DATE) = CAST(GETDATE() AS DATE)
            ";
            $stmt = $this->db->prepare($sql);
        }

        $stmt->execute();
        return $stmt->fetch()['total'] ?? 0;
    }
}
