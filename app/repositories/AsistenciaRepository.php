<?php
namespace App\Repositories;

use App\Config\Database;
use PDO;

class AsistenciaRepository {
    private $conn;

    public function __construct() {
        try {
            $this->conn = Database::getInstance()->getConnection();
        } catch (\Throwable $e) {
            error_log("Error en conexión DB: " . $e->getMessage());
            throw $e;
        }
    }

    public function existeAsistencia($practicanteID, $fecha) {
        try {
            $stmt = $this->conn->prepare("
                SELECT AsistenciaID FROM Asistencia 
                WHERE PracticanteID = ? AND Fecha = ?
            ");
            $stmt->execute([$practicanteID, $fecha]);
            return $stmt->fetch() !== false;
        } catch (\Throwable $e) {
            error_log("Error en existeAsistencia: " . $e->getMessage());
            throw $e;
        }
    }

    public function registrarEntrada($practicanteID, $horaDispositivo) {
        try {
            $fechaHoy = date('Y-m-d');

            // Verificar si ya existe un registro hoy
            $sqlCheck = "SELECT AsistenciaID FROM Asistencia WHERE PracticanteID = ? AND Fecha = ?";
            $stmt = $this->conn->prepare($sqlCheck);
            $stmt->execute([$practicanteID, $fechaHoy]);
            $registro = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($registro) {
                return ['success' => false, 'message' => 'Ya tiene asistencia registrada para hoy.'];
            }

            // Obtener turno según la hora del dispositivo
            $turnoID = $this->obtenerTurnoPorHora($horaDispositivo);

            if (!$turnoID) {
                return ['success' => false, 'message' => 'No puedes registrar asistencia fuera del horario establecido.'];
            }

            // Insertar nuevo registro
            $sqlInsert = "INSERT INTO Asistencia (PracticanteID, Fecha, HoraEntrada, TurnoID)
                        VALUES (?, ?, ?, ?)";
            $stmtInsert = $this->conn->prepare($sqlInsert);
            $stmtInsert->execute([$practicanteID, $fechaHoy, $horaDispositivo, $turnoID]);

            return ['success' => true, 'message' => 'Entrada registrada correctamente.'];

        } catch (\Throwable $e) {
            error_log("Error en registrarEntrada: " . $e->getMessage());
            throw $e;
        }
    }



    public function registrarSalida($practicanteID, $horaDispositivo) {
        try {
            $fechaHoy = date('Y-m-d');

            // Buscar asistencia de hoy
            $sql = "SELECT AsistenciaID, HoraSalida FROM Asistencia WHERE PracticanteID = ? AND Fecha = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$practicanteID, $fechaHoy]);
            $registro = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$registro) {
                return ['success' => false, 'message' => 'No existe registro de entrada para hoy.'];
            }

            if ($registro['HoraSalida']) {
                return ['success' => false, 'message' => 'Ya registraste la salida de hoy.'];
            }

            // Obtener turno según hora
            $turnoID = $this->obtenerTurnoPorHora($horaDispositivo);

            if (!$turnoID) {
                return ['success' => false, 'message' => 'No puedes registrar salida fuera del horario establecido.'];
            }

            // Actualizar salida
            $sqlUpdate = "UPDATE Asistencia SET HoraSalida = ?, TurnoID = ? WHERE AsistenciaID = ?";
            $stmtUpdate = $this->conn->prepare($sqlUpdate);
            $stmtUpdate->execute([$horaDispositivo, $turnoID, $registro['AsistenciaID']]);

            return ['success' => true, 'message' => 'Salida registrada correctamente.'];

        } catch (\Throwable $e) {
            error_log("Error en registrarSalida: " . $e->getMessage());
            throw $e;
        }
    }


    public function obtenerAsistenciasPorArea($areaID) {
        try {
            error_log("===> Entrando a Repository obtenerAsistenciasPorArea con areaID: $areaID");

            $sql = "EXEC sp_ListarAsistencias :areaID";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':areaID', $areaID, \PDO::PARAM_INT);

            $stmt->execute();

            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            error_log("===> Resultado obtenido: " . count($result));

            return [
                'success' => true,
                'data' => $result
            ];

        } catch (\Throwable $e) {
            error_log("❌ Error en Repository obtenerAsistenciasPorArea: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al obtener asistencias por área: ' . $e->getMessage()
            ];
        }
    }


    private function obtenerTurnoPorHora($hora) {
        $sql = "
            SELECT TurnoID 
            FROM Turno
            WHERE CAST(? AS TIME) BETWEEN HoraInicio AND HoraFin
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$hora]);
        $turno = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $turno ? $turno['TurnoID'] : null;
    }



}
