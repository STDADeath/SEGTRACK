<?php
class ModeloFuncionario {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

   
    public function registrarFuncionario(array $datos): array {
        try {
            if (!$this->conexion) {
                return ['success' => false, 'error' => 'Conexión a la base de datos no disponible'];
            }

            $sql = "INSERT INTO funcionario 
                    (CargoFuncionario, NombreFuncionario, IdSede, 
                 TelefonoFuncionario, DocumentoFuncionario, CorreoFuncionario)
                    VALUES (:cargo, :qr, :nombre, :sede, :telefono, :documento, :correo)";

            $stmt = $this->conexion->prepare($sql);
            $resultado = $stmt->execute([
                ':cargo' => $datos['CargoFuncionario'],
                ':nombre' => $datos['NombreFuncionario'],
                ':sede' => $datos['IdSede'],
                ':telefono' => $datos['TelefonoFuncionario'],
                ':documento' => $datos['DocumentoFuncionario'],
                ':correo' => $datos['CorreoFuncionario']
            ]);

            if ($resultado) {
                return ['success' => true, 'id' => $this->conexion->lastInsertId()];
            } else {
                $errorInfo = $stmt->errorInfo();
                return [
                    'success' => false,
                    'error' => $errorInfo[2] ?? 'Error desconocido al insertar'
                ];
            }

        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * ✅ Actualizar código QR de un funcionario
     */
    public function actualizarQR(int $idFuncionario, string $rutaQR): array {
        try {
            if (!$this->conexion) {
                return ['success' => false, 'error' => 'Conexión no disponible'];
            }

            $sql = "UPDATE funcionario 
                    SET QrCodigoFuncionario = :qr 
                    WHERE IdFuncionario = :id";

            $stmt = $this->conexion->prepare($sql);
            $resultado = $stmt->execute([       
                ':qr' => $rutaQR,
                ':id' => $idFuncionario
            ]);

            return [
                'success' => $resultado,
                'rows' => $stmt->rowCount()
            ];

        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * ✅ Obtener todos los funcionarios
     */
    public function obtenerTodos(): array {
        try {
            if (!$this->conexion) return [];

            $sql = "SELECT * FROM funcionario ORDER BY IdFuncionario DESC";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            return [];
        }
    }

  
    public function obtenerPorId(int $idFuncionario): ?array {
        try {
            if (!$this->conexion) return null;

            $sql = "SELECT * FROM funcionario WHERE IdFuncionario = :id";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':id' => $idFuncionario]);

            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

        } catch (PDOException $e) {
            return null;
        }
    }

  
    public function obtenerQR(int $idFuncionario): ?string {
        try {
            if (!$this->conexion) return null;

            $sql = "SELECT QrCodigoFuncionario 
                    FROM funcionario 
                    WHERE IdFuncionario = :id";

            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':id' => $idFuncionario]);

            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado['QrCodigoFuncionario'] ?? null;

        } catch (PDOException $e) {
            return null;
        }
    }

  
    public function actualizar(int $idFuncionario, array $datos): array {
        try {
            if (!$this->conexion) {
                return ['success' => false, 'error' => 'Conexión no disponible'];
            }

            $sql = "UPDATE funcionario SET 
                        CargoFuncionario = :cargo,
                        NombreFuncionario = :nombre,
                        IdSede = :sede,
                        TelefonoFuncionario = :telefono,
                        DocumentoFuncionario = :documento,
                        CorreoFuncionario = :correo
                    WHERE IdFuncionario = :id";

            $stmt = $this->conexion->prepare($sql);
            $resultado = $stmt->execute([
                ':cargo' => $datos['CargoFuncionario'],
                ':nombre' => $datos['NombreFuncionario'],
                ':sede' => $datos['IdSede'],
                ':telefono' => $datos['TelefonoFuncionario'],
                ':documento' => $datos['DocumentoFuncionario'],
                ':correo' => $datos['CorreoFuncionario'],
                ':id' => $idFuncionario
            ]);

            return [
                'success' => $resultado,
                'rows' => $stmt->rowCount()
            ];

        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function existe(int $idFuncionario): bool {
        try {
            if (!$this->conexion) return false;

            $sql = "SELECT 1 
                    FROM funcionario 
                    WHERE IdFuncionario = :id 
                    LIMIT 1";

            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':id' => $idFuncionario]);

            return $stmt->rowCount() > 0;

        } catch (PDOException $e) {
            return false;
        }
    }
}
?>
