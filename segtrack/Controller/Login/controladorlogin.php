<?php
require_once(__DIR__ . '/../../Core/conexion.php');

class Usuario {
    private $conexion;

    public function __construct() {
        $this->conexion = (new Conexion())->getConexion();
    }

    public function validarLogin($correo, $contrasena) {
        try {
            $sql = "SELECT f.NombreFuncionario, u.TipoRol, u.Contrasena
                    FROM usuario u
                    INNER JOIN funcionario f ON f.idFuncionario = u.idFuncionario
                    WHERE (f.Correo = :correo OR f.Documento = :correo)
                    AND u.Contrasena = :contrasena";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(":correo", $correo);
            $stmt->bindParam(":contrasena", $contrasena);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error en validarLogin: " . $e->getMessage();
            return false;
        }
    }
}
?>
