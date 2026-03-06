<?php

/**
 * ========================================
 * MODELO USUARIO - SEGTRACK
 * ========================================
 */

require_once __DIR__ . '/../Core/conexion.php';

class Modelo_Usuario {

    private $conexion;

    public function __construct() {
        $this->conexion = (new Conexion())->getConexion();
    }

    // ==========================================
    // 🔐 VALIDAR LOGIN
    // ==========================================
    public function validarLogin(string $correo, string $contrasena): array {

        try {

            $sql = "SELECT 
                        u.IdUsuario,
                        u.TipoRol,
                        u.Contrasena,
                        f.IdFuncionario,
                        f.NombreFuncionario,
                        f.CorreoFuncionario,
                        f.DocumentoFuncionario,
                        f.IdSede
                    FROM usuario u
                    INNER JOIN funcionario f ON u.IdFuncionario = f.IdFuncionario
                    WHERE (f.CorreoFuncionario = :correo 
                        OR f.DocumentoFuncionario = :correo)
                    AND u.Estado = 'Activo'
                    LIMIT 1";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':correo', $correo, PDO::PARAM_STR);
            $stmt->execute();

            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$usuario) {
                return ['ok' => false, 'message' => 'No existe usuario con ese correo o documento.'];
            }

            $hashBD     = trim($usuario['Contrasena']);
            $loginValido = false;

            if (password_verify($contrasena, $hashBD)) {
                $loginValido = true;
            } elseif ($contrasena === $hashBD) {
                $loginValido = true;
                $nuevoHash   = password_hash($contrasena, PASSWORD_DEFAULT);
                $upd = $this->conexion->prepare("UPDATE usuario SET Contrasena = :h WHERE IdUsuario = :id");
                $upd->execute([':h' => $nuevoHash, ':id' => $usuario['IdUsuario']]);
            }

            if (!$loginValido) {
                return ['ok' => false, 'message' => 'Contraseña incorrecta.'];
            }

            return [
                'ok' => true,
                'usuario' => [
                    'IdUsuario'         => $usuario['IdUsuario'],
                    'IdFuncionario'     => $usuario['IdFuncionario'],
                    'NombreFuncionario' => $usuario['NombreFuncionario'],
                    'CorreoFuncionario' => $usuario['CorreoFuncionario'],
                    'TipoRol'           => $usuario['TipoRol'],
                    'IdSede'            => $usuario['IdSede']
                ]
            ];

        } catch (PDOException $e) {
            return ['ok' => false, 'message' => 'Error en BD: ' . $e->getMessage()];
        }
    }

    // ==========================================
    // ✅ VERIFICAR SI YA TIENE USUARIO
    // ==========================================
    public function usuarioExiste(int $idFuncionario): bool {

        $sql  = "SELECT COUNT(*) FROM usuario WHERE IdFuncionario = :id";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([':id' => $idFuncionario]);
        return (int)$stmt->fetchColumn() > 0;
    }

    // ==========================================
    // ✅ REGISTRAR USUARIO
    // ==========================================
    public function registrarUsuario(string $tipoRol, string $contrasena, int $idFuncionario): bool {

        try {
            $hash = password_hash($contrasena, PASSWORD_DEFAULT);

            $sql = "INSERT INTO usuario (TipoRol, Contrasena, IdFuncionario, Estado)
                    VALUES (:rol, :pass, :id, 'Activo')";

            $stmt = $this->conexion->prepare($sql);
            return $stmt->execute([
                ':rol'  => $tipoRol,
                ':pass' => $hash,
                ':id'   => $idFuncionario
            ]);

        } catch (PDOException $e) {
            return false;
        }
    }

    // ==========================================
    // ✅ OBTENER TODOS LOS USUARIOS (lista)
    // ==========================================
    public function obtenerUsuarios(): array {

        try {
            $sql = "SELECT 
                        u.IdUsuario,
                        f.NombreFuncionario,
                        f.DocumentoFuncionario,
                        u.TipoRol,
                        u.Estado
                    FROM usuario u
                    INNER JOIN funcionario f ON u.IdFuncionario = f.IdFuncionario
                    ORDER BY u.Estado DESC, u.IdUsuario DESC";

            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            return [];
        }
    }

    // ==========================================
    // ✅ ACTUALIZAR ROL
    // ==========================================
    public function actualizarRol(int $idUsuario, string $nuevoRol): bool {

        try {
            $sql  = "UPDATE usuario SET TipoRol = :rol WHERE IdUsuario = :id";
            $stmt = $this->conexion->prepare($sql);
            return $stmt->execute([':rol' => $nuevoRol, ':id' => $idUsuario]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // ==========================================
    // ✅ CAMBIAR ESTADO (Activo / Inactivo)
    // ==========================================
    public function cambiarEstado(int $idUsuario, string $estado): bool {

        try {
            $sql  = "UPDATE usuario SET Estado = :estado WHERE IdUsuario = :id";
            $stmt = $this->conexion->prepare($sql);
            return $stmt->execute([':estado' => $estado, ':id' => $idUsuario]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // ==========================================
    // 🔍 FILTRAR FUNCIONARIOS SIN USUARIO
    // ==========================================
    public function funcionariosSinUsuario(): array {

        try {
            $sql = "SELECT f.IdFuncionario, f.NombreFuncionario, f.DocumentoFuncionario
                    FROM funcionario f
                    LEFT JOIN usuario u ON f.IdFuncionario = u.IdFuncionario
                    WHERE f.Estado = 'Activo' AND u.IdUsuario IS NULL
                    ORDER BY f.NombreFuncionario";

            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            return [];
        }
    }
}