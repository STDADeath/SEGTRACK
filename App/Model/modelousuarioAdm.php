<?php
require_once __DIR__ . '/../Core/Conexion.php';

class Modelo_Usuario {

    private $conexion;

    public function __construct() {
        $db = new Conexion();
        $this->conexion = $db->getConexion();
    }

    // ============================================
    // ✔ Verificar si un funcionario ya tiene usuario
    // ============================================
    public function usuarioExiste($idFuncionario) {
        $sql = "SELECT IdUsuario FROM usuario WHERE IdFuncionario = :id LIMIT 1";

        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':id', $idFuncionario, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }

    // ============================================
    // ✔ Registrar usuario (con ENUM y contraseña hasheada)
    // ============================================
    public function registrarUsuario($tipoRol, $contrasena, $idFuncionario) {

        // Importante → aplicar hash seguro
        $passwordHash = password_hash($contrasena, PASSWORD_BCRYPT);

        $sql = "INSERT INTO usuario (TipoRol, Contrasena, IdFuncionario)
                VALUES (:rol, :pass, :func)";

        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':rol', $tipoRol);              // ENUM validado desde el formulario
        $stmt->bindParam(':pass', $passwordHash);        // Contraseña protegida
        $stmt->bindParam(':func', $idFuncionario, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // ============================================
    // ✔ Obtener todos los usuarios (por si lo necesitas)
    // ============================================
    public function listarUsuarios() {
        $sql = "SELECT u.IdUsuario, u.TipoRol, f.NombreFuncionario
                FROM usuario u
                INNER JOIN funcionario f ON f.IdFuncionario = u.IdFuncionario
                ORDER BY u.IdUsuario DESC";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
