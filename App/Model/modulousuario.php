<?php
require_once __DIR__ . '../../Core/conexion.php';

class ModuloUsuario {
    private $conexion;

    public function __construct() {
        $this->conexion = (new Conexion())->getConexion();
    }

    // ======================================================
    // 🔐 VALIDAR LOGIN
    // ======================================================
    public function validarLogin($correo, $contrasena) {
        try {
            $sql = "SELECT 
                        u.IdUsuario,
                        u.TipoRol,
                        u.Contrasena AS Contrasena,
                        u.Estado AS EstadoUsuario,
                        f.IdFuncionario,
                        f.NombreFuncionario,
                        f.CorreoFuncionario,
                        f.DocumentoFuncionario,
                        f.IdSede,
                        f.QRFuncionario,
                        f.EstadoFuncionario
                    FROM usuario u
                    INNER JOIN funcionario f 
                        ON u.IdFuncionario = f.IdFuncionario
                    WHERE f.CorreoFuncionario = :correo 
                       OR f.DocumentoFuncionario = :correo
                    LIMIT 1";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':correo', $correo, PDO::PARAM_STR);
            $stmt->execute();

            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$usuario) {
                return ['ok'=>false, 'message'=>'❌ No existe usuario con ese correo o documento.'];
            }

            // ❌ Bloquear login si está inactivo
            if ($usuario['EstadoUsuario'] === "Inactivo") {
                return ['ok'=>false, 'message'=>'⛔ Usuario inactivo. No puede iniciar sesión.'];
            }

            $hashBD = trim($usuario['Contrasena']);
            $loginValido = false;

            if (password_verify($contrasena, $hashBD)) {
                $loginValido = true;
            } elseif ($contrasena === $hashBD) {
                $loginValido = true;
                // Convertir contraseña vieja sin hash → hashed
                $newHash = password_hash($contrasena, PASSWORD_DEFAULT);
                $update = $this->conexion->prepare("UPDATE usuario SET Contrasena = :h WHERE IdUsuario = :id");
                $update->execute([':h'=>$newHash, ':id'=>$usuario['IdUsuario']]);
            }

            if (!$loginValido) {
                return ['ok'=>false, 'message'=>'❌ Contraseña incorrecta.'];
            }

            return [
                'ok'=>true,
                'usuario'=>[
                    'IdUsuario'=>$usuario['IdUsuario'],
                    'IdFuncionario'=>$usuario['IdFuncionario'],
                    'NombreFuncionario'=>$usuario['NombreFuncionario'],
                    'CorreoFuncionario'=>$usuario['CorreoFuncionario'],
                    'TipoRol'=>$usuario['TipoRol'],
                    'IdSede'=>$usuario['IdSede'],
                    'QRFuncionario'=>$usuario['QRFuncionario'],
                    'EstadoFuncionario'=>$usuario['EstadoFuncionario']
                ]
            ];

        } catch (PDOException $e) {
            return ['ok'=>false, 'message'=>'Error en BD: ' . $e->getMessage()];
        }
    }

    // ======================================================
    // 🔄 ACTUALIZAR ROL
    // ======================================================
    public function actualizarRol($idFuncionario, $nuevoRol) {
        try {
            $sql = "UPDATE usuario SET TipoRol = :rol WHERE IdFuncionario = :id";
            $stmt = $this->conexion->prepare($sql);
            return $stmt->execute([
                ':rol'=>$nuevoRol,
                ':id'=>$idFuncionario
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // ======================================================
    // 🔷 ACTUALIZAR QR + ESTADO (FUNCIONA PARA LOGIN)
    // ======================================================
    public function actualizarQR($idFuncionario, $nuevoQR, $nuevoEstado) {
        try {
            $this->conexion->beginTransaction();

            // 1️⃣ Actualizar funcionario
            $sql1 = "UPDATE funcionario 
                        SET QRFuncionario = :qr,
                            EstadoFuncionario = :estado
                    WHERE IdFuncionario = :id";

            $stmt1 = $this->conexion->prepare($sql1);
            $stmt1->execute([
                ':qr' => $nuevoQR,
                ':estado' => $nuevoEstado,
                ':id' => $idFuncionario
            ]);

            // 2️⃣ Actualizar estado del usuario para login
            $sql2 = "UPDATE usuario 
                        SET Estado = :estado
                    WHERE IdFuncionario = :id";

            $stmt2 = $this->conexion->prepare($sql2);
            $stmt2->execute([
                ':estado' => $nuevoEstado,
                ':id' => $idFuncionario
            ]);

            $this->conexion->commit();
            return true;

        } catch (PDOException $e) {
            $this->conexion->rollBack();
            return false;
        }
    }

    // ======================================================
    // 🔍 FILTROS
    // ======================================================
    public function filtrarFuncionariosPorCargo(string $cargo): array {
        try {
            $sql = "SELECT 
                        f.IdFuncionario,
                        f.NombreFuncionario,
                        f.CorreoFuncionario,
                        f.DocumentoFuncionario,
                        f.CargoFuncionario,
                        f.IdSede
                    FROM funcionario f
                    WHERE f.CargoFuncionario = :cargo
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
            $sql = "SELECT 
                        f.IdFuncionario,
                        f.NombreFuncionario,
                        f.CorreoFuncionario,
                        f.DocumentoFuncionario,
                        f.CargoFuncionario,
                        f.IdSede
                    FROM funcionario f
                    WHERE f.IdFuncionario = :id
                    LIMIT 1";

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
            $sql = "SELECT 
                        f.IdFuncionario,
                        f.NombreFuncionario,
                        f.CorreoFuncionario,
                        f.DocumentoFuncionario,
                        f.CargoFuncionario,
                        f.IdSede
                    FROM funcionario f
                    WHERE f.CorreoFuncionario = :correo
                    LIMIT 1";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':correo', $correoFuncionario, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            return null;
        }
    }
}
