<?php
namespace App\Services;

use App\Repositories\UsuarioRepository;

class UsuarioService {
    private $usuarioRepository;
    
    public function __construct() {
        $this->usuarioRepository = new UsuarioRepository();
    }
    
    public function login($nombreUsuario, $password) {
        if (empty($nombreUsuario) || empty($password)) {
            throw new \Exception("Usuario y contraseña son requeridos");
        }
        
        $usuario = $this->usuarioRepository->login($nombreUsuario, $password);
        
        if ($usuario === null) {
            throw new \Exception("Credenciales incorrectas");
        }
        
        return $usuario;
    }
    
    public function validarCUI($usuarioID, $cui) {
        if (empty($usuarioID) || empty($cui)) {
            throw new \Exception("Usuario y CUI son requeridos");
        }
        
        if (strlen($cui) !== 1) {
            throw new \Exception("El CUI debe ser de 1 dígito");
        }
        
        $usuario = $this->usuarioRepository->validarCUI($usuarioID, $cui);
        
        if ($usuario === null) {
            throw new \Exception("CUI incorrecto");
        }
        
        return $usuario;
    }

    /**
     * Listar todos los usuarios con información completa
     */
    public function listarTodos() {
        return $this->usuarioRepository->listarTodos();
    }

    /**
     * Obtener usuario por ID
     */
    public function obtenerPorID($usuarioID) {
        return $this->usuarioRepository->obtenerPorID($usuarioID);
    }

    /**
     * Crear nuevo usuario
     */
    public function crear($data) {
        
        // Obtener CargoID según el nombre del cargo
        $data['cargoID'] = $this->obtenerCargoID($data['cargo']);
        
        // Calcular CUI (último dígito del DNI)
        $data['cui'] = substr($data['dni'], -1);
        $data['dni'] = substr($data['dni'], 0, -1);
        
        return $this->usuarioRepository->crear($data);
    }

    /**
     * Actualizar usuario
     */
    public function actualizar($usuarioID, $data) {
        // Obtener CargoID según el nombre del cargo
        if (isset($data['cargo'])) {
            $data['cargoID'] = $this->obtenerCargoID($data['cargo']);
        }
        
        // Calcular CUI (último dígito del DNI)
        if (isset($data['dni'])) {
            $data['cui'] = substr($data['dni'], -1);
            $data['dni'] = substr($data['dni'], 0, -1);

        }
        
        return $this->usuarioRepository->actualizar($usuarioID, $data);
    }

    /**
     * Eliminar usuario
     */
    public function eliminar($usuarioID) {
        return $this->usuarioRepository->eliminar($usuarioID);
    }

    /**
     * Cambiar contraseña
     */
    public function cambiarPassword($usuarioID, $nuevaPassword) {
        return $this->usuarioRepository->cambiarPassword($usuarioID, $nuevaPassword);
    }

    /**
     * Filtrar usuarios
     */
    public function filtrar($filtros) {
        return $this->usuarioRepository->filtrar($filtros);
    }

    /**
     * Verificar si existe un nombre de usuario
     */
    public function existeNombreUsuario($nombreUsuario) {
        return $this->usuarioRepository->existeNombreUsuario($nombreUsuario);
    }

    /**
     * Verificar si existe un nombre de usuario excepto el actual
     */
    public function existeNombreUsuarioExcepto($nombreUsuario, $usuarioID) {
        return $this->usuarioRepository->existeNombreUsuarioExcepto($nombreUsuario, $usuarioID);
    }

    /**
     * Verificar si existe un DNI
     */
    public function existeDNI($dni) {
        return $this->usuarioRepository->existeDNI($dni);
    }

    /**
     * Obtener CargoID según el nombre
     */
    private function obtenerCargoID($cargo) {
        $cargos = [
            'gerente_rrhh' => 1,
            'gerente_area' => 2,
            'usuario_area' => 3,
            'gerente_sistemas' => 4
        ];
        
        return $cargos[$cargo] ?? null;
    }
    
}