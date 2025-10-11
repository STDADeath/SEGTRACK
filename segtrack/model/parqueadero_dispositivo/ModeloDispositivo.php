<?php
class ModeloDispositivo {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    /**
     * ✅ Inserta un nuevo dispositivo en la base de datos
     */
    public function registrarDispositivo(string $tipo, string $marca, ?int $idFuncionario, ?int $idVisitante): array {
        try {
            if (!$this->conexion) {
                return ['success' => false, 'error' => 'Conexión a la base de datos no disponible'];
            }

            $sql = "INSERT INTO dispositivos 
                    (TipoDispositivo, MarcaDispositivo, IdFuncionario, IdVisitante)
                    VALUES (:tipo, :marca, :funcionario, :visitante)";

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
            $sql = "UPDATE dispositivos SET QrDispositivo = :qr WHERE IdDispositivo = :id";
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
     * ✅ Obtiene todos los dispositivos registrados
     */
    public function obtenerTodos(): array {
        try {
            $sql = "SELECT * FROM dispositivos ORDER BY IdDispositivo DESC";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * ✅ Obtiene un dispositivo por su ID
     */
    public function obtenerPorId(int $idDispositivo): ?array {
        try {
            $sql = "SELECT * FROM dispositivos WHERE IdDispositivo = ?";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([$idDispositivo]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * ✅ Actualiza los datos del dispositivo (sin tocar el QR)
     */
    public function actualizar(int $idDispositivo, array $datos): array {
        try {
            $sql = "UPDATE dispositivos SET 
                        TipoDispositivo = ?, 
                        MarcaDispositivo = ?, 
                        IdFuncionario = ?, 
                        IdVisitante = ?
                    WHERE IdDispositivo = ?";

            $stmt = $this->conexion->prepare($sql);
            $resultado = $stmt->execute([
                $datos['TipoDispositivo'],
                $datos['MarcaDispositivo'],
                $datos['IdFuncionario'] ?? null,
                $datos['IdVisitante'] ?? null,
                $idDispositivo
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
     * ✅ Elimina un dispositivo por ID
     */
    public function eliminar(int $idDispositivo): array {
        try {
            $sql = "DELETE FROM dispositivos WHERE IdDispositivo = :id";
            $stmt = $this->conexion->prepare($sql);
            $resultado = $stmt->execute([':id' => $idDispositivo]);

            return [
                'success' => $resultado,
                'rows' => $stmt->rowCount()
            ];

        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
?>
