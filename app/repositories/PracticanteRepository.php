<?php
namespace App\Repositories;

use App\Config\Database;
use App\Models\Practicante;
use PDO;
use PDOException;

class PracticanteRepository {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }


    // Metodo que devuelve un array de filas asociativas con los campos de sp_ListarPracticantes

    public function listarPracticantes() {
        try {
            $sql = "EXEC sp_ListarPracticantes";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $results;
        } catch (PDOException $e) {
            error_log("PracticanteRepository::listarPracticantes - " . $e->getMessage());
            throw new \Exception("Error al listar practicantes: " . $e->getMessage());
        }
    }

    /**
     * Obtiene un practicante por ID (incluye estado y área más reciente)
     */
    public function findById($practicanteID) {
        try {
            $sql = "
                SELECT 
                    p.PracticanteID,
                    p.DNI,
                    p.Nombres,
                    p.ApellidoPaterno,
                    p.ApellidoMaterno,
                    CONCAT(p.Nombres, ' ', p.ApellidoPaterno, ' ', p.ApellidoMaterno) AS NombreCompleto,
                    p.Carrera,
                    p.Universidad,
                    ISNULL(e.Descripcion, '-') AS Estado,
                    CONVERT(VARCHAR(10), p.FechaEntrada, 103) AS FechaRegistro,
                    p.Email,
                    p.Telefono,
                    p.Direccion,
                    ISNULL(a.NombreArea, '-') AS Area
                FROM Practicante p
                LEFT JOIN Estado e ON p.EstadoID = e.EstadoID
                OUTER APPLY (
                    SELECT TOP 1 ar.NombreArea
                    FROM SolicitudPracticas sp
                    JOIN Area ar ON sp.AreaID = ar.AreaID
                    WHERE sp.PracticanteID = p.PracticanteID
                    ORDER BY sp.FechaSolicitud DESC, sp.SolicitudID DESC
                ) a
                WHERE p.PracticanteID = :id
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $practicanteID, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (PDOException $e) {
            error_log("PracticanteRepository::findById - " . $e->getMessage());
            throw new \Exception("Error al buscar practicante: " . $e->getMessage());
        }
    }

    /**
     * Crea un practicante (usa sp_CrearPracticante). Devuelve el ID insertado.
     * Recibe un objeto Practicante y opcionalmente $areaID (int|null)
     */
    public function registrarPracticante($p, $areaID = null) {
        try {
            $stmt = $this->db->prepare("EXEC sp_RegistrarPracticante ?, ?, ?, ?, ?, ?, ?, ?, ?, ?");

            $stmt->execute([
                $p->getDNI(),
                $p->getNombres(),
                $p->getApellidoPaterno(),
                $p->getApellidoMaterno(),
                $p->getCarrera(),
                $p->getEmail(),
                $p->getTelefono(),
                $p->getDireccion(),
                $p->getUniversidad(),
                $p->getFechaEntrada(),
                $p->getFechaSalida() ?? null
            ]);

            // El SP devuelve SELECT @NewID AS PracticanteID;
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $nuevoID = $row['PracticanteID'] ?? null;

            // Si se especificó un área, crear también la solicitud
            if ($areaID) {
                $stmt2 = $this->db->prepare("
                    INSERT INTO SolicitudPracticas (FechaSolicitud, EstadoID, PracticanteID, AreaID)
                    VALUES (GETDATE(), 1, ?, ?)
                ");
                $stmt2->execute([$nuevoID, $areaID]);
            }

            return $nuevoID;
        } catch (\PDOException $e) {
            error_log("PracticanteRepository::crear - " . $e->getMessage());
            throw new \Exception("Error al crear practicante: " . $e->getMessage());
        }
    }

    public function obtenerPorId($id) {
        $stmt = $this->db->prepare("SELECT * FROM Practicante WHERE PracticanteID = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizar($id, $data) {
        $sql = "EXEC sp_ActualizarPracticante 
            @PracticanteID = :id,
            @DNI = :DNI,
            @Nombres = :Nombres,
            @ApellidoPaterno = :ApellidoPaterno,
            @ApellidoMaterno = :ApellidoMaterno,
            @Carrera = :Carrera,
            @Email = :Email,
            @Telefono = :Telefono,
            @Direccion = :Direccion,
            @Universidad = :Universidad,
            @FechaEntrada = :FechaEntrada,
            @FechaSalida = :FechaSalida";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id' => $id,
            ':DNI' => $data['DNI'],
            ':Nombres' => $data['Nombres'],
            ':ApellidoPaterno' => $data['ApellidoPaterno'],
            ':ApellidoMaterno' => $data['ApellidoMaterno'],
            ':Carrera' => $data['Carrera'],
            ':Email' => $data['Email'],
            ':Telefono' => $data['Telefono'],
            ':Direccion' => $data['Direccion'],
            ':Universidad' => $data['Universidad'],
            ':FechaEntrada' => $data['FechaEntrada'],
            ':FechaSalida' => $data['FechaSalida']
        ]);
        return "Practicante actualizado correctamente";
    }

    public function eliminar($id) {
        $stmt = $this->db->prepare("EXEC sp_EliminarPracticante @PracticanteID = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return "Practicante eliminado correctamente";
    }

}
