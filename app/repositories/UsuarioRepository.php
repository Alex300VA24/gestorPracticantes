<?php
namespace App\Repositories;

use App\Config\Database;
use App\Models\Usuario;
use App\Models\Cargo;
use App\Models\Area;
use PDO;
use PDOException;

class UsuarioRepository {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function login($nombreUsuario, $password) {
        try {
            $sql = "EXEC sp_LoginUsuario @NombreUsuario = :nombreUsuario, @Password = :password";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':nombreUsuario', $nombreUsuario, PDO::PARAM_STR);
            $stmt->bindParam(':password', $password, PDO::PARAM_STR);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                return $this->mapToUsuario($result);
            }
            
            return null;
            
        } catch (PDOException $e) {
            error_log("Error en login: " . $e->getMessage());
            throw new \Exception("Error al iniciar sesión: " . $e->getMessage());
        }
    }
    
    public function validarCUI($usuarioID, $cui) {
        try {
            $sql = "EXEC sp_ValidarCUI @UsuarioID = :usuarioID, @CUI = :cui";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':usuarioID', $usuarioID, PDO::PARAM_INT);
            $stmt->bindParam(':cui', $cui, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                return $this->mapToUsuario($result);
            }
            
            return null;
            
        } catch (PDOException $e) {
            error_log("Error en validarCUI: " . $e->getMessage());
            throw new \Exception("Error al validar CUI: " . $e->getMessage());
        }
    }
    
    public function findById($usuarioID) {
        try {
            $sql = "SELECT * FROM Usuario WHERE UsuarioID = :usuarioID";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':usuarioID', $usuarioID, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                return $this->mapToUsuario($result);
            }
            
            return null;
            
        } catch (PDOException $e) {
            error_log("Error en findById: " . $e->getMessage());
            throw new \Exception("Error al buscar usuario: " . $e->getMessage());
        }
    }
    
    public function findAll() {
        try {
            $sql = "SELECT * FROM Usuario WHERE Estado = 'Activo' ORDER BY Nombres";
            $stmt = $this->db->query($sql);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $usuarios = [];
            foreach ($results as $row) {
                $usuarios[] = $this->mapToUsuario($row);
            }
            
            return $usuarios;
            
        } catch (PDOException $e) {
            error_log("Error en findAll: " . $e->getMessage());
            throw new \Exception("Error al obtener usuarios: " . $e->getMessage());
        }
    }
    
    private function mapToUsuario($data) {
        $usuario = new Usuario();
        $usuario->setUsuarioID($data['UsuarioID']);
        $usuario->setNombreUsuario($data['NombreUsuario']);
        $usuario->setNombres($data['Nombres']);
        $usuario->setApellidoPaterno($data['ApellidoPaterno']);
        $usuario->setApellidoMaterno($data['ApellidoMaterno']);
        $usuario->setEstadoID($data['EstadoID']);

        // 🔸 Crear objetos relacionados
        $cargo = new Cargo();
        $cargo->setCargoID($data['CargoID']);
        $cargo->setNombreCargo($data['NombreCargo']);
        $usuario->setCargo($cargo);

        $area = new Area();
        $area->setAreaID($data['AreaID']);
        $area->setNombreArea($data['NombreArea']);
        $usuario->setArea($area);

        return $usuario;
    }

}