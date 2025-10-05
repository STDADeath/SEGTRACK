<?php
class Funcionario {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    public function insertar(array $datos): array {
        try {
            if (!$this->conexion) {
                return ['success' => false, 'error' => 'Conexión a la base de datos no disponible'];
            }

            // Generar QR único
            $qrCodigo = "QR-FUNC-" . strtoupper(substr(md5(uniqid(rand(), true)), 0, 5));

            $sql = "INSERT INTO funcionario 
                    (CargoFuncionario, QrCodigoFuncionario, NombreFuncionario, IdSede, TelefonoFuncionario, DocumentoFuncionario, CorreoFuncionario)
                    VALUES (:cargo, :qr, :nombre, :idSede, :telefono, :documento, :correo)";
            
            $stmt = $this->conexion->prepare($sql);
            
            $resultado = $stmt->execute([
                ':cargo'     => $datos['CargoFuncionario'],
                ':qr'        => $qrCodigo,
                ':nombre'    => $datos['NombreFuncionario'],
                ':idSede'    => $datos['IdSede'],
                ':telefono'  => $datos['TelefonoFuncionario'],
                ':documento' => $datos['DocumentoFuncionario'],
                ':correo'    => $datos['CorreoFuncionario']
            ]);

            if ($resultado) {
                return ['success' => true, 'id' => $this->conexion->lastInsertId()];
            } else {
                $errorInfo = $stmt->errorInfo();
                return ['success' => false, 'error' => $errorInfo[2] ?? 'Error desconocido al insertar'];
            }
            
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    public function obtenerTodos(): array {
        try {
            $sql = "SELECT f.*, s.TipoSede, s.Ciudad 
                    FROM funcionario f 
                    LEFT JOIN sede s ON f.IdSede = s.IdSede 
                    ORDER BY f.IdFuncionario DESC";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function obtenerPorId(int $IdFuncionario): ?array {
        try {
            $sql = "SELECT f.*, s.TipoSede, s.Ciudad 
                    FROM funcionario f 
                    LEFT JOIN sede s ON f.IdSede = s.IdSede 
                    WHERE f.IdFuncionario = ?";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([$IdFuncionario]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    public function actualizar(int $IdFuncionario, array $datos): array {
        try {
            $sql = "UPDATE funcionario SET 
                        CargoFuncionario = ?, 
                        NombreFuncionario = ?, 
                        IdSede = ?,
                        TelefonoFuncionario = ?, 
                        DocumentoFuncionario = ?,
                        CorreoFuncionario = ?
                    WHERE IdFuncionario = ?";
            
            $stmt = $this->conexion->prepare($sql);
            
            $resultado = $stmt->execute([
                $datos['CargoFuncionario'],
                $datos['NombreFuncionario'],
                $datos['IdSede'],
                $datos['TelefonoFuncionario'],
                $datos['DocumentoFuncionario'],
                $datos['CorreoFuncionario'],
                $IdFuncionario 
            ]);

            return [
                'success' => $resultado, 
                'rows' => $stmt->rowCount()
            ];
            
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function eliminar(int $IdFuncionario): array {
        try {
            $sql = "DELETE FROM funcionario WHERE IdFuncionario = ?";
            $stmt = $this->conexion->prepare($sql);
            $resultado = $stmt->execute([$IdFuncionario]);
            
            return [
                'success' => $resultado, 
                'rows' => $stmt->rowCount()
            ];
            
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function existeDocumento(string $documento, int $excluirId = null): bool {
        try {
            if ($excluirId) {
                $sql = "SELECT COUNT(*) FROM funcionario WHERE DocumentoFuncionario = ? AND IdFuncionario != ?";
                $stmt = $this->conexion->prepare($sql);
                $stmt->execute([$documento, $excluirId]);
            } else {
                $sql = "SELECT COUNT(*) FROM funcionario WHERE DocumentoFuncionario = ?";
                $stmt = $this->conexion->prepare($sql);
                $stmt->execute([$documento]);
            }
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function existeCorreo(string $correo, int $excluirId = null): bool {
        try {
            if ($excluirId) {
                $sql = "SELECT COUNT(*) FROM funcionario WHERE CorreoFuncionario = ? AND IdFuncionario != ?";
                $stmt = $this->conexion->prepare($sql);
                $stmt->execute([$correo, $excluirId]);
            } else {
                $sql = "SELECT COUNT(*) FROM funcionario WHERE CorreoFuncionario = ?";
                $stmt = $this->conexion->prepare($sql);
                $stmt->execute([$correo]);
            }
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function existeSede(int $idSede): bool {
        try {
            $sql = "SELECT COUNT(*) FROM sede WHERE IdSede = ?";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([$idSede]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function tieneRelaciones(int $IdFuncionario): ?string {
        try {
            $stmt = $this->conexion->prepare("SELECT COUNT(*) FROM bitacora WHERE IdFuncionario = ?");
            $stmt->execute([$IdFuncionario]);
            if ($stmt->fetchColumn() > 0) return 'bitacora';

            $stmt = $this->conexion->prepare("SELECT COUNT(*) FROM dispositivo WHERE IdFuncionario = ?");
            $stmt->execute([$IdFuncionario]);
            if ($stmt->fetchColumn() > 0) return 'dispositivo';

            $stmt = $this->conexion->prepare("SELECT COUNT(*) FROM dotacion WHERE IdFuncionario = ?");
            $stmt->execute([$IdFuncionario]);
            if ($stmt->fetchColumn() > 0) return 'dotacion';

            $stmt = $this->conexion->prepare("SELECT COUNT(*) FROM ingreso WHERE IdFuncionario = ?");
            $stmt->execute([$IdFuncionario]);
            if ($stmt->fetchColumn() > 0) return 'ingreso';

            return null;
        } catch (PDOException $e) {
            return null;
        }
    }
}
?>