<?php
require_once __DIR__ . "/../Controller/Conexion/conexion.php";

class Usuario {
    private $conexion;

    public function __construct() {
        $this->conexion = (new Conexion())->getConexion();
    }

    // ==================================================
    // ✅ 1. Validar login (correo y contraseña)
    // ==================================================
    public function validarLogin($correo, $contrasena) {
        try {
            $sql = "SELECT 
                        u.IdUsuario,
                        u.TipoRol, 
                        u.Contrasena, 
                        f.NombreFuncionario 
                    FROM usuario u
                    INNER JOIN funcionario f 
                        ON f.IdFuncionario = u.IdFuncionario
                    WHERE f.CorreoFuncionario = :correo";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(":correo", $correo);
            $stmt->execute();

            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verificar contraseña (si está encriptada o no)
            if ($resultado && (
                password_verify($contrasena, $resultado["Contrasena"]) ||
                $contrasena === $resultado["Contrasena"]
            )) {
                return $resultado;
            }

            return false;
        } catch (PDOException $e) {
            die("Error en login: " . $e->getMessage());
        }
    }

    // ==================================================
    // ✅ 2. Obtener todos los usuarios (listar)
    // ==================================================
    public function obtenerUsuarios($filtro = null) {
        try {
            $sql = "SELECT 
                        u.IdUsuario,
                        u.TipoRol,
                        f.NombreFuncionario,
                        f.CorreoFuncionario
                    FROM usuario u
                    INNER JOIN funcionario f ON f.IdFuncionario = u.IdFuncionario";

            if ($filtro) {
                $sql .= " WHERE f.NombreFuncionario LIKE :filtro 
                          OR f.CorreoFuncionario LIKE :filtro 
                          OR u.TipoRol LIKE :filtro";
            }

            $stmt = $this->conexion->prepare($sql);

            if ($filtro) {
                $param = "%" . $filtro . "%";
                $stmt->bindParam(":filtro", $param);
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error al obtener usuarios: " . $e->getMessage());
        }
    }

    // ==================================================
    // ✅ 3. Obtener un solo usuario por ID
    // ==================================================
    public function obtenerUsuarioPorId($idUsuario) {
        try {
            $sql = "SELECT 
                        u.IdUsuario,
                        u.TipoRol,
                        f.NombreFuncionario,
                        f.CorreoFuncionario
                    FROM usuario u
                    INNER JOIN funcionario f ON f.IdFuncionario = u.IdFuncionario
                    WHERE u.IdUsuario = :id";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(":id", $idUsuario, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error al obtener usuario: " . $e->getMessage());
        }
    }

    // ==================================================
    // ✅ 4. Actualizar usuario
    // ==================================================
    public function actualizarUsuario($idUsuario, $tipoRol, $contrasena = null) {
        try {
            if ($contrasena) {
                $hash = password_hash($contrasena, PASSWORD_DEFAULT);
                $sql = "UPDATE usuario 
                        SET TipoRol = :rol, Contrasena = :contrasena 
                        WHERE IdUsuario = :id";
                $stmt = $this->conexion->prepare($sql);
                $stmt->bindParam(":rol", $tipoRol);
                $stmt->bindParam(":contrasena", $hash);
            } else {
                $sql = "UPDATE usuario 
                        SET TipoRol = :rol 
                        WHERE IdUsuario = :id";
                $stmt = $this->conexion->prepare($sql);
                $stmt->bindParam(":rol", $tipoRol);
            }

            $stmt->bindParam(":id", $idUsuario, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            die("Error al actualizar usuario: " . $e->getMessage());
        }
    }

    // ==================================================
    // ✅ 5. Eliminar usuario
    // ==================================================
    public function eliminarUsuario($idUsuario) {
        try {
            $sql = "DELETE FROM usuario WHERE IdUsuario = :id";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(":id", $idUsuario, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            die("Error al eliminar usuario: " . $e->getMessage());
        }
    }
}
?>
