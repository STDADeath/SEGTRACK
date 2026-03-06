<?php
require_once __DIR__ . '/../../Core/conexion.php'; // Asegúrate de tener la clase Conexion

class ModeloFuncionario {
    private $conexion;

    public function __construct() {
        $conexionObj = new Conexion();
        $this->conexion = $conexionObj->getConexion();
    }

    // ===============================
    // Insertar nuevo funcionario
    // ===============================
    public function insertarFuncionario(array $datos): array {
        try {
            // Validar duplicado por documento
            $consulta = $this->conexion->prepare("SELECT COUNT(*) FROM funcionario WHERE DocumentoFuncionario = ?");
            $consulta->execute([$datos['DocumentoFuncionario']]);
            if ($consulta->fetchColumn() > 0) {
                return ['success' => false, 'error' => '⚠️ El documento ya está registrado.'];
            }

            // Generar código QR (solo texto)
            $codigoQR = 'QR-FUNC-' . strtoupper(substr(md5(uniqid()), 0, 5));

            // Insertar funcionario
            $sql = "INSERT INTO funcionario 
                    (NombreFuncionario, DocumentoFuncionario, CorreoFuncionario, TelefonoFuncionario, CargoFuncionario, IdSede, QrCodigoFuncionario)
                    VALUES (:nombre, :documento, :correo, :telefono, :cargo, :idsede, :qr)";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([
                ':nombre' => $datos['NombreFuncionario'],
                ':documento' => $datos['DocumentoFuncionario'],
                ':correo' => $datos['CorreoFuncionario'],
                ':telefono' => $datos['TelefonoFuncionario'],
                ':cargo' => $datos['CargoFuncionario'],
                ':idsede' => $datos['IdSede'],
                ':qr' => $codigoQR
            ]);

            return ['success' => true, 'mensaje' => '✅ Registro exitoso. Código QR: ' . $codigoQR, 'id' => $this->conexion->lastInsertId()];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => '❌ Error al registrar: ' . $e->getMessage()];
        }
    }

    // ===============================
    // Actualizar datos de funcionario
    // ===============================
    public function actualizarFuncionario(int $id, array $datos): array {
        try {
            $sql = "UPDATE funcionario 
                    SET NombreFuncionario = :nombre,
                        DocumentoFuncionario = :documento,
                        CorreoFuncionario = :correo,
                        TelefonoFuncionario = :telefono,
                        CargoFuncionario = :cargo,
                        IdSede = :idsede
                    WHERE IdFuncionario = :id";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([
                ':nombre' => $datos['NombreFuncionario'],
                ':documento' => $datos['DocumentoFuncionario'],
                ':correo' => $datos['CorreoFuncionario'],
                ':telefono' => $datos['TelefonoFuncionario'],
                ':cargo' => $datos['CargoFuncionario'],
                ':idsede' => $datos['IdSede'],
                ':id' => $id
            ]);
            return ['success' => true, 'mensaje' => '✅ Funcionario actualizado correctamente.'];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => '❌ Error al actualizar: ' . $e->getMessage()];
        }
    }

    // ===============================
    // Eliminar funcionario
    // ===============================
    public function eliminarFuncionario(int $id): array {
        try {
            $stmt = $this->conexion->prepare("DELETE FROM funcionario WHERE IdFuncionario = ?");
            $stmt->execute([$id]);
            return ['success' => true, 'mensaje' => '🗑️ Funcionario eliminado correctamente.'];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => '❌ Error al eliminar: ' . $e->getMessage()];
        }
    }

    // ===============================
    // Actualizar QR de un funcionario
    // ===============================
    public function actualizarQR(int $idFuncionario, string $rutaQR): array {
        try {
            if (!$this->conexion) {
                return ['success' => false, 'error' => 'Conexión a la base de datos no disponible'];
            }

            $sql = "UPDATE funcionario SET QrCodigoFuncionario = :qr WHERE IdFuncionario = :id";
            $stmt = $this->conexion->prepare($sql);
            $resultado = $stmt->execute([
                ':qr' => $rutaQR,
                ':id' => $idFuncionario
            ]);

            if ($resultado) {
                return ['success' => true, 'mensaje' => 'QR actualizado correctamente'];
            } else {
                $errorInfo = $stmt->errorInfo();
                return ['success' => false, 'error' => $errorInfo[2] ?? 'Error desconocido al actualizar'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ===============================
    // Sincronizar Cargo según rol del usuario
    // ===============================
    public function actualizarCargoSegunRol(int $idFuncionario): array {
        try {
            $sql = "UPDATE funcionario f
                    JOIN usuario u ON f.IdFuncionario = u.IdFuncionario
                    SET f.CargoFuncionario = u.TipoRol
                    WHERE f.IdFuncionario = ?";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([$idFuncionario]);
            return ['success' => true, 'mensaje' => '🔄 Cargo sincronizado con el rol del usuario.'];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => '❌ Error al sincronizar cargo: ' . $e->getMessage()];
        }
    }

    // ===============================
    // Listar todos los funcionarios
    // ===============================
    public function listarTodos(): array {
        try {
            $stmt = $this->conexion->query("SELECT IdFuncionario, NombreFuncionario, DocumentoFuncionario FROM funcionario ORDER BY NombreFuncionario ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}
?>
