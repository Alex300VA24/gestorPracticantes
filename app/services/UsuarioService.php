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
    
}