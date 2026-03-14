<?php
require_once __DIR__ . '/../Core/conexion.php';

class ModuloUsuario {

    private $conexion;

    public function __construct() {
        $this->conexion = (new Conexion())->getConexion();
    }

    public function validarLogin(string $correo, string $contrasena): array {
        try {
            $sql = "SELECT 
                        u.IdUsuario,
                        u.TipoRol,
                        u.Contrasena,
                        u.Estado,
                        f.IdFuncionario,
                        f.NombreFuncionario,
                        f.CorreoFuncionario,
                        f.DocumentoFuncionario,
                        f.IdSede,
                        f.FotoFuncionario
                    FROM usuario u
                    INNER JOIN funcionario f 
                        ON u.IdFuncionario = f.IdFuncionario
                    WHERE (f.CorreoFuncionario = :correo 
                        OR f.DocumentoFuncionario = :correo)
                    LIMIT 1";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':correo', $correo, PDO::PARAM_STR);
            $stmt->execute();
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$usuario) {
                return ['ok' => false, 'message' => 'No existe usuario con ese correo o documento.'];
            }

            $hashBD      = trim($usuario['Contrasena']);
            $loginValido = false;

            if (password_verify($contrasena, $hashBD)) {
                $loginValido = true;
            } elseif ($contrasena === $hashBD) {
                $loginValido = true;
                $nuevoHash   = password_hash($contrasena, PASSWORD_DEFAULT);
                $update = $this->conexion->prepare("UPDATE usuario SET Contrasena = :newHash WHERE IdUsuario = :id");
                $update->execute([':newHash' => $nuevoHash, ':id' => $usuario['IdUsuario']]);
            }

            if (!$loginValido) {
                return ['ok' => false, 'message' => 'Contraseña incorrecta.'];
            }

            return [
                'ok'      => true,
                'usuario' => [
                    'IdUsuario'          => $usuario['IdUsuario'],
                    'IdFuncionario'      => $usuario['IdFuncionario'],
                    'NombreFuncionario'  => $usuario['NombreFuncionario'],
                    'CorreoFuncionario'  => $usuario['CorreoFuncionario'],
                    'TipoRol'            => $usuario['TipoRol'],
                    'IdSede'             => $usuario['IdSede'],
                    'Estado'             => $usuario['Estado'],
                    'FotoFuncionario'    => $usuario['FotoFuncionario'] ?? ''  // ✅ foto
                ]
            ];

        } catch (PDOException $e) {
            return ['ok' => false, 'message' => 'Error de conexión. Intente nuevamente.'];
        }
    }

    public function actualizarRol(int $idFuncionario, string $nuevoRol): bool {
        try {
            $stmt = $this->conexion->prepare("UPDATE usuario SET TipoRol = :rol WHERE IdFuncionario = :id");
            return $stmt->execute([':rol' => $nuevoRol, ':id' => $idFuncionario]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function filtrarFuncionariosPorCargo(string $cargo): array {
        try {
            $sql = "SELECT f.IdFuncionario, f.NombreFuncionario, f.CorreoFuncionario,
                           f.DocumentoFuncionario, f.CargoFuncionario, f.IdSede
                    FROM funcionario f WHERE f.CargoFuncionario = :cargo
                    ORDER BY f.NombreFuncionario ASC";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':cargo', $cargo, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function filtrarFuncionarioPorId(int $idFuncionario): ?array {
        try {
            $sql = "SELECT f.IdFuncionario, f.NombreFuncionario, f.CorreoFuncionario,
                           f.DocumentoFuncionario, f.CargoFuncionario, f.IdSede
                    FROM funcionario f WHERE f.IdFuncionario = :id LIMIT 1";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id', $idFuncionario, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }

    public function filtrarFuncionarioPorCorreo(string $correoFuncionario): ?array {
        try {
            $sql = "SELECT f.IdFuncionario, f.NombreFuncionario, f.CorreoFuncionario,
                           f.DocumentoFuncionario, f.CargoFuncionario, f.IdSede
                    FROM funcionario f WHERE f.CorreoFuncionario = :correo LIMIT 1";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':correo', $correoFuncionario, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }
}