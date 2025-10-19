<?php
class ModeloVehiculo {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    /**
     * ✅ Inserta un nuevo vehículo en la base de datos
     */
    public function registrarVehiculo(string $tipo, string $placa, ?string $descripcion, ?string $tarjeta, ?string $fecha, int $idSede): array {
        try {
            if (!$this->conexion) {
                return ['success' => false, 'error' => 'Conexión a la base de datos no disponible'];
            }

            $sql = "INSERT INTO Parqueadero 
                    (TipoVehiculo, PlacaVehiculo, DescripcionVehiculo, TarjetaPropiedad, FechaParqueadero, IdSede)
                    VALUES (:tipo, :placa, :descripcion, :tarjeta, :fecha, :idsede)";

            $stmt = $this->conexion->prepare($sql);
            $resultado = $stmt->execute([
                ':tipo' => $tipo,
                ':placa' => $placa,
                ':descripcion' => $descripcion ?: null,
                ':tarjeta' => $tarjeta ?: null,
                ':fecha' => $fecha ?: null,
                ':idsede' => $idSede
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
     * ✅ Obtiene todos los vehículos registrados
     */
    public function obtenerTodos(): array {
        try {
            if (!$this->conexion) {
                return [];
            }

            $sql = "SELECT * FROM Parqueadero ORDER BY IdParqueadero DESC";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * ✅ Obtiene un vehículo por su ID
     */
    public function obtenerPorId(int $idVehiculo): ?array {
        try {
            if (!$this->conexion) {
                return null;
            }

            $sql = "SELECT * FROM Parqueadero WHERE IdParqueadero = :id";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':id' => $idVehiculo]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * ✅ Actualiza los datos del vehículo
     */
    public function actualizar(int $idVehiculo, array $datos): array {
        try {
            if (!$this->conexion) {
                return ['success' => false, 'error' => 'Conexión a la base de datos no disponible'];
            }

            $sql = "UPDATE Parqueadero SET 
                        TipoVehiculo = :tipo, 
                        PlacaVehiculo = :placa, 
                        DescripcionVehiculo = :descripcion, 
                        TarjetaPropiedad = :tarjeta,
                        FechaParqueadero = :fecha,
                        IdSede = :idsede
                    WHERE IdParqueadero = :id";

            $stmt = $this->conexion->prepare($sql);
            $resultado = $stmt->execute([
                ':tipo' => $datos['TipoVehiculo'] ?? null,
                ':placa' => $datos['PlacaVehiculo'] ?? null,
                ':descripcion' => $datos['DescripcionVehiculo'] ?? null,
                ':tarjeta' => $datos['TarjetaPropiedad'] ?? null,
                ':fecha' => $datos['FechaParqueadero'] ?? null,
                ':idsede' => $datos['IdSede'] ?? null,
                ':id' => $idVehiculo
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
     * ✅ Elimina un vehículo por ID
     */
    public function eliminar(int $idVehiculo): array {
        try {
            if (!$this->conexion) {
                return ['success' => false, 'error' => 'Conexión a la base de datos no disponible'];
            }

            $sql = "DELETE FROM Parqueadero WHERE IdParqueadero = :id";
            $stmt = $this->conexion->prepare($sql);
            $resultado = $stmt->execute([':id' => $idVehiculo]);

            return [
                'success' => $resultado,
                'rows' => $stmt->rowCount()
            ];

        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * ✅ Verifica si existe un vehículo
     */
    public function existe(int $idVehiculo): bool {
        try {
            if (!$this->conexion) {
                return false;
            }

            $sql = "SELECT 1 FROM Parqueadero WHERE IdParqueadero = :id LIMIT 1";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':id' => $idVehiculo]);
            return $stmt->rowCount() > 0;

        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * ✅ Obtiene vehículos por placa (búsqueda)
     */
    public function obtenerPorPlaca(string $placa): ?array {
        try {
            if (!$this->conexion) {
                return null;
            }

            $sql = "SELECT * FROM Parqueadero WHERE PlacaVehiculo = :placa";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':placa' => $placa]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * ✅ Obtiene vehículos por tipo
     */
    public function obtenerPorTipo(string $tipo): array {
        try {
            if (!$this->conexion) {
                return [];
            }

            $sql = "SELECT * FROM Parqueadero WHERE TipoVehiculo = :tipo ORDER BY IdParqueadero DESC";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':tipo' => $tipo]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * ✅ Obtiene vehículos por sede
     */
    public function obtenerPorSede(int $idSede): array {
        try {
            if (!$this->conexion) {
                return [];
            }

            $sql = "SELECT * FROM Parqueadero WHERE IdSede = :idsede ORDER BY IdParqueadero DESC";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':idsede' => $idSede]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * ✅ Cuenta total de vehículos
     */
    public function contar(): int {
        try {
            if (!$this->conexion) {
                return 0;
            }

            $sql = "SELECT COUNT(*) as total FROM Parqueadero";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$resultado['total'];

        } catch (PDOException $e) {
            return 0;
        }
    }
}
?>