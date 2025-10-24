<?php
require_once(__DIR__ . "/../../Core/conexion.php");

class ModuloUsuario {
    private $pdo;

    public function __construct() {
        $this->pdo = (new Conexion())->getConexion();
    }

    // ======================================================
    // ✅ Validar login (correo o documento y contraseña)
    // ======================================================
    public function validarLogin($correo, $contrasena) {
        try {
            $sql = "SELECT 
                        u.IdUsuario,
                        u.TipoRol,
                        u.Contrasena,
                        f.NombreFuncionario,
                        f.CorreoFuncionario
                    FROM usuario u
                    INNER JOIN funcionario f ON f.IdFuncionario = u.IdFuncionario
                    WHERE f.CorreoFuncionario = :correo
                    OR f.DocumentoFuncionario = :correo
                    LIMIT 1";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(":correo", $correo);
            $stmt->execute();

            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$usuario) {
                return false;
            }

            if (password_verify($contrasena, $usuario["Contrasena"]) ||
                $contrasena === $usuario["Contrasena"]) {
                return $usuario;
            }

            return false;
        } catch (PDOException $e) {
            throw new Exception("Error en validarLogin: " . $e->getMessage());
        }
    }

    // ======================================================
    // ✅ Agregar usuario
    // ======================================================
    public function agregarUsuario($idFuncionario, $tipoRol, $contrasena) {
        try {
            $hash = password_hash($contrasena, PASSWORD_DEFAULT);
            $sql = "INSERT INTO usuario (IdFuncionario, TipoRol, Contrasena)
                    VALUES (:idFuncionario, :tipoRol, :contrasena)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(":idFuncionario", $idFuncionario);
            $stmt->bindParam(":tipoRol", $tipoRol);
            $stmt->bindParam(":contrasena", $hash);
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Error al agregar usuario: " . $e->getMessage());
        }
    }

    // ======================================================
    // ✅ Editar usuario
    // ======================================================
    public function editarUsuario($idUsuario, $tipoRol, $contrasena = null) {
        try {
            if ($contrasena) {
                $hash = password_hash($contrasena, PASSWORD_DEFAULT);
                $sql = "UPDATE usuario 
                        SET TipoRol = :tipoRol, Contrasena = :contrasena
                        WHERE IdUsuario = :idUsuario";
                $stmt = $this->pdo->prepare($sql);
                $stmt->bindParam(":contrasena", $hash);
            } else {
                $sql = "UPDATE usuario 
                        SET TipoRol = :tipoRol
                        WHERE IdUsuario = :idUsuario";
                $stmt = $this->pdo->prepare($sql);
            }

            $stmt->bindParam(":tipoRol", $tipoRol);
            $stmt->bindParam(":idUsuario", $idUsuario);
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Error al editar usuario: " . $e->getMessage());
        }
    }

    // ======================================================
    // ✅ Eliminar usuario
    // ======================================================
    public function eliminarUsuario($idUsuario) {
        try {
            $sql = "DELETE FROM usuario WHERE IdUsuario = :idUsuario";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(":idUsuario", $idUsuario);
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Error al eliminar usuario: " . $e->getMessage());
        }
    }

    // ======================================================
    // ✅ Filtrar por rol
    // ======================================================
    public function filtrarPorRol($tipoRol) {
        try {
            $sql = "SELECT 
                        u.IdUsuario,
                        u.TipoRol,
                        f.NombreFuncionario,
                        f.CorreoFuncionario
                    FROM usuario u
                    INNER JOIN funcionario f ON f.IdFuncionario = u.IdFuncionario
                    WHERE u.TipoRol = :tipoRol";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(":tipoRol", $tipoRol);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error al filtrar por rol: " . $e->getMessage());
        }
    }
}
?>
