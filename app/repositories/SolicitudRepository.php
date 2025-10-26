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

    public function obtenerDocumentosPorPracticante($practicanteID) {
        $stmt = $this->conn->prepare("EXEC sp_ObtenerDocumentosPorPracticante :id");
        $stmt->bindValue(':id', $practicanteID, PDO::PARAM_INT);
        $stmt->execute();

        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // ✅ Convertir binario a Base64
            if (isset($row['Archivo'])) {
                $row['Archivo'] = base64_encode($row['Archivo']);
            }
            $result[] = $row;
        }

        return $result;
    }

    public function obtenerDocumentoPorTipoYPracticante($practicanteID, $tipoDocumento)
    {
        $sql = "EXEC sp_ObtenerDocumentoPorTipoYPracticante ?, ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$practicanteID, $tipoDocumento]);

        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($fila && isset($fila['Archivo'])) {
            // Convertir binario a base64
            $fila['Archivo'] = base64_encode($fila['Archivo']);
        }

        return $fila;
    }

    public function crearSolicitud($practicanteID) {
        $sql = "EXEC sp_CrearSolicitud ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, (int)$practicanteID, PDO::PARAM_INT);

        if ($stmt->execute()) {
            // Obtener el ID recién creado (asumiendo que el SP hace un SELECT SCOPE_IDENTITY())
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $row ? $row['SolicitudID'] : null;
        }

        return null;
    }


    public function subirDocumento($id, $tipo, $archivo, $observaciones = null) {
        $sql = "EXEC sp_SubirDocumento ?, ?, ?, ?";
        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(1, (int)$id, PDO::PARAM_INT);                     // @SolicitudID
        $stmt->bindValue(2, $tipo, PDO::PARAM_STR);                        // @TipoDocumento
        $stmt->bindParam(3, $archivo, PDO::PARAM_LOB, 0, PDO::SQLSRV_ENCODING_BINARY); // @Archivo
        $stmt->bindValue(4, $observaciones, PDO::PARAM_STR);               // @Observaciones (puede ser NULL)

        $res = $stmt->execute();
        if ($res === false) {
            $err = $stmt->errorInfo();
            throw new \Exception("Ejecutar SP falló: " . json_encode($err));
        }

        return $res;
    }

    public function actualizarDocumento($solicitudID, $tipoDocumento, $archivo = null, $observaciones = null)
    {
        // Si hay archivo, incluimos el parámetro @Archivo
        if ($archivo !== null) {
            $sql = "EXEC sp_ActualizarDocumento @SolicitudID = ?, @TipoDocumento = ?, @Archivo = ?, @Observaciones = ?";
        } else {
            // Sin archivo, lo excluimos completamente
            $sql = "EXEC sp_ActualizarDocumento @SolicitudID = ?, @TipoDocumento = ?, @Observaciones = ?";
        }

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(1, (int)$solicitudID, PDO::PARAM_INT);
        $stmt->bindValue(2, $tipoDocumento, PDO::PARAM_STR);

        if ($archivo !== null) {
            $stmt->bindParam(3, $archivo, PDO::PARAM_LOB, 0, PDO::SQLSRV_ENCODING_BINARY);
            $stmt->bindValue(4, $observaciones, PDO::PARAM_STR);
        } else {
            $stmt->bindValue(3, $observaciones, PDO::PARAM_STR);
        }

        if (!$stmt->execute()) {
            $err = $stmt->errorInfo();
            throw new \Exception("Error al ejecutar spActualizarDocumento: " . json_encode($err));
        }

        return $stmt->rowCount() > 0;
    }


    // Agregar a SolicitudRepository

    public function obtenerSolicitudPorPracticante($practicanteID) {
        $stmt = $this->conn->prepare("
            SELECT TOP 1 * 
            FROM SolicitudPracticas 
            WHERE PracticanteID = ? 
            ORDER BY FechaSolicitud DESC
        ");
        $stmt->execute([$practicanteID]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }








}
