<?php
require_once __DIR__ . "/../../Core/conexion.php";

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
                        f.IdFuncionario,
                        f.NombreFuncionario,
                        f.CorreoFuncionario,
                        u.IdUsuario,
                        u.TipoRol,
                        u.Contraseña
                    FROM funcionario f
                    INNER JOIN usuario u 
                        ON f.IdFuncionario = u.IdFuncionario
                    WHERE f.CorreoFuncionario = :correo
                    LIMIT 1";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(":correo", $correo);
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$resultado) {
                return false; // Correo no encontrado
            }

            // Verificar la contraseña (encriptada o no)
            if (password_verify($contrasena, $resultado["Contraseña"]) ||
                $contrasena === $resultado["Contraseña"]) {
                return $resultado;
            }

            return false; // Contraseña incorrecta
        } catch (PDOException $e) {
            die("Error en validarLogin: " . $e->getMessage());
        }
    }

    // ==================================================
    // ✅ 2. Obtener todos los usuarios
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
                $param = "%".$filtro."%";
                $stmt->bindParam(":filtro", $param);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error al obtener usuarios: " . $e->getMessage());
        }
    }

    // ==================================================
    // ✅ 3. Obtener usuario por ID
    // ==================================================
    public function obtenerUsuarioPorId($idUsuario) {
        try {
            $sql = "SELECT 
                        u.IdUsuario,
                        u.TipoRol,
                        f.NombreFuncionario,
                        f.CorreoFuncionario
                    FROM usuario u
                    INNER JOIN funcionario f 
                        ON f.IdFuncionario = u.IdFuncionario
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
                        SET TipoRol = :rol, Contraseña = :contrasena 
                        WHERE IdUsuario = :id";
                $stmt = $this->conexion->prepare($sql);
                $stmt->bindParam(":contrasena", $hash);
            } else {
                $sql = "UPDATE usuario 
                        SET TipoRol = :rol 
                        WHERE IdUsuario = :id";
                $stmt = $this->conexion->prepare($sql);
            }

            $stmt->bindParam(":rol", $tipoRol);
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
