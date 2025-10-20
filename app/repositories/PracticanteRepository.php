<?php
namespace App\Repositories;

use App\Config\Database;
use App\Models\Practicante;
use PDO;
use PDOException;

class PracticanteRepository {
    private $db;

    public function __construct() {
        try {
            $this->db = Database::getInstance()->getConnection();
        } catch (\Throwable $e) {
            error_log("Error en conexión DB: " . $e->getMessage());
            throw $e;
        }
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
    public function obtenerPorID($practicanteID) {
        try {
            $sql = "EXEC sp_ObtenerPracticantePorID :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $practicanteID, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (PDOException $e) {
            error_log("PracticanteRepository::obtenerPorID - " . $e->getMessage());
            throw new \Exception("Error al obtener practicante: " . $e->getMessage());
        }
    }


    /**
     * Crea un practicante (usa sp_CrearPracticante). Devuelve el ID insertado.
     * Recibe un objeto Practicante y opcionalmente $areaID (int|null)
     */
    public function registrarPracticante($p, $areaID = null) {
        try {
            $stmt = $this->db->prepare("EXEC sp_RegistrarPracticante ?, ?, ?, ?, ?, ?, ?, ?, ?");

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
            ]);

            // El SP devuelve SELECT @NewID AS PracticanteID;
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $nuevoID = $row['PracticanteID'] ?? null;

            return $nuevoID;
        } catch (\PDOException $e) {
            error_log("PracticanteRepository::crear - " . $e->getMessage());
            throw new \Exception("Error al crear practicante: " . $e->getMessage());
        }
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
