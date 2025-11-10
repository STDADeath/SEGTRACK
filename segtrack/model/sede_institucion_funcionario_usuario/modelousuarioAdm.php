<?php
require_once __DIR__ . '/../../Core/conexion.php';

class Modelo_Usuario {
    private $conexion;

    public function __construct() {
        $db = new Conexion();
        $this->conexion = $db->getConexion();
    }

    // Método para registrar un usuario
    public function registrarUsuario($tipoRol, $contrasena, $idFuncionario) {
        try {
            $sql = "INSERT INTO usuario (TipoRol, Contrasena, IdFuncionario) 
                    VALUES (:tipoRol, :contrasena, :idFuncionario)";
            $stmt = $this->conexion->prepare($sql);
            
            // Encriptar contraseña
            $hash = password_hash($contrasena, PASSWORD_BCRYPT);

            $stmt->bindParam(':tipoRol', $tipoRol);
            $stmt->bindParam(':contrasena', $hash);
            $stmt->bindParam(':idFuncionario', $idFuncionario, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Error al registrar usuario: " . $e->getMessage());
        }
    }
}
