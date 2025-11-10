<?php
class ModeloDispositivo {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }


    public function registrarDispositivo(string $tipo, string $marca, ?int $idFuncionario, ?int $idVisitante): array {
        try {
            if (!$this->conexion) {
                return ['success' => false, 'error' => 'Conexión a la base de datos no disponible'];
            }

            $sql = "INSERT INTO dispositivo 
                    (TipoDispositivo, MarcaDispositivo, IdFuncionario, IdVisitante, Estado)
                    VALUES (:tipo, :marca, :funcionario, :visitante, 'Activo')";

            $stmt = $this->conexion->prepare($sql);
            $resultado = $stmt->execute([
                ':tipo' => $tipo,
                ':marca' => $marca,
                ':funcionario' => $idFuncionario ?: null,
                ':visitante' => $idVisitante ?: null
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

    /**
     * ✅ Actualiza la ruta del código QR generado
     */
    public function actualizarQR(int $idDispositivo, string $rutaQR): array {
        try {
            if (!$this->conexion) {
                return ['success' => false, 'error' => 'Conexión a la base de datos no disponible'];
            }

            $sql = "UPDATE dispositivo SET QrDispositivo = :qr WHERE IdDispositivo = :id";
            $stmt = $this->conexion->prepare($sql);
            $resultado = $stmt->execute([
                ':qr' => $rutaQR,
                ':id' => $idDispositivo
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
     * ✅ Obtiene todos los dispositivos ACTIVOS
     */
    public function obtenerTodos(): array {
        try {
            if (!$this->conexion) {
                return [];
            }

            $sql = "SELECT * FROM dispositivo WHERE Estado = 'Activo' ORDER BY IdDispositivo DESC";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * ✅ Obtiene todos los dispositivos (incluye activos e inactivos)
     */
    public function obtenerTodosConEstado(): array {
        try {
            if (!$this->conexion) {
                return [];
            }

            $sql = "SELECT * FROM dispositivo ORDER BY 
                    CASE 
                        WHEN Estado = 'Activo' THEN 1 
                        WHEN Estado = 'Inactivo' THEN 2 
                        ELSE 3 
                    END, 
                    IdDispositivo DESC";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * ✅ Obtiene un dispositivo por su ID (incluye QR)
     */
    public function obtenerPorId(int $idDispositivo): ?array {
        try {
            if (!$this->conexion) {
                return null;
            }

            $sql = "SELECT * FROM dispositivo WHERE IdDispositivo = :id";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':id' => $idDispositivo]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * ✅ Obtiene solo la ruta del QR de un dispositivo
     */
    public function obtenerQR(int $idDispositivo): ?string {
        try {
            if (!$this->conexion) {
                return null;
            }

            $sql = "SELECT QrDispositivo FROM dispositivo WHERE IdDispositivo = :id";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':id' => $idDispositivo]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $resultado['QrDispositivo'] ?? null;

        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * ✅ Actualiza los datos del dispositivo (sin tocar el QR ni el Estado)
     */
    public function actualizar(int $idDispositivo, array $datos): array {
        try {
            if (!$this->conexion) {
                return ['success' => false, 'error' => 'Conexión a la base de datos no disponible'];
            }

            $sql = "UPDATE dispositivo SET 
                        TipoDispositivo = :tipo, 
                        MarcaDispositivo = :marca, 
                        IdFuncionario = :funcionario, 
                        IdVisitante = :visitante
                    WHERE IdDispositivo = :id";

            $stmt = $this->conexion->prepare($sql);
            $resultado = $stmt->execute([
                ':tipo' => $datos['TipoDispositivo'] ?? null,
                ':marca' => $datos['MarcaDispositivo'] ?? null,
                ':funcionario' => $datos['IdFuncionario'] ?? null,
                ':visitante' => $datos['IdVisitante'] ?? null,
                ':id' => $idDispositivo
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
     * 🆕 Cambia el estado del dispositivo (Activo <-> Inactivo)
     */
    public function cambiarEstado(int $idDispositivo, string $nuevoEstado): array {
        try {
            if (!$this->conexion) {
                return ['success' => false, 'error' => 'Conexión a la base de datos no disponible'];
            }

            // Validar que el estado sea válido
            if (!in_array($nuevoEstado, ['Activo', 'Inactivo'])) {
                return ['success' => false, 'error' => 'Estado no válido'];
            }

            $sql = "UPDATE dispositivo SET Estado = :estado WHERE IdDispositivo = :id";
            $stmt = $this->conexion->prepare($sql);
            $resultado = $stmt->execute([
                ':estado' => $nuevoEstado,
                ':id' => $idDispositivo
            ]);

            return [
                'success' => $resultado,
                'rows' => $stmt->rowCount(),
                'nuevoEstado' => $nuevoEstado
            ];

        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * ✅ Verifica si existe un dispositivo
     */
    public function existe(int $idDispositivo): bool {
        try {
            if (!$this->conexion) {
                return false;
            }

            $sql = "SELECT 1 FROM dispositivo WHERE IdDispositivo = :id LIMIT 1";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':id' => $idDispositivo]);
            return $stmt->rowCount() > 0;

        } catch (PDOException $e) {
            return false;
        }
    }
}
?>