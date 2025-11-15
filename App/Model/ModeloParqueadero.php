<?php
require_once __DIR__ . '/../../Core/conexion.php';

class ModeloParqueadero {
    private $conexion;
    private $logPath;

    public function __construct() {
        $this->logPath = __DIR__ . '/../../controller/parqueadero_dispositivo/debug_log.txt';
        
        try {
            $conexionObj = new Conexion();
            $this->conexion = $conexionObj->getConexion();
            file_put_contents($this->logPath, "✅ Conexión establecida correctamente\n", FILE_APPEND);
        } catch (PDOException $e) {
            $msg = "❌ Error de conexión: " . $e->getMessage();
            file_put_contents($this->logPath, "$msg\n", FILE_APPEND);
            throw new Exception($msg);
        }
    }

    // ✅ Registrar vehículo (con campo QrVehiculo)
    public function registrarVehiculo($TipoVehiculo, $PlacaVehiculo, $DescripcionVehiculo, $TarjetaPropiedad, $FechaParqueadero, $IdSede): array {
        try {
            file_put_contents($this->logPath, "Punto 1: Preparando SQL INSERT\n", FILE_APPEND);
            
            $sql = "INSERT INTO parqueadero 
                    (TipoVehiculo, PlacaVehiculo, DescripcionVehiculo, TarjetaPropiedad, FechaParqueadero, IdSede, Estado, QrVehiculo)
                    VALUES (:TipoVehiculo, :PlacaVehiculo, :DescripcionVehiculo, :TarjetaPropiedad, :FechaParqueadero, :IdSede, 'Activo', '')";
            
            file_put_contents($this->logPath, "Punto 2: Preparando statement\n", FILE_APPEND);
            $stmt = $this->conexion->prepare($sql);

            file_put_contents($this->logPath, "Punto 3: Ejecutando con parámetros\n", FILE_APPEND);
            $stmt->execute([
                ':TipoVehiculo' => $TipoVehiculo,
                ':PlacaVehiculo' => $PlacaVehiculo,
                ':DescripcionVehiculo' => $DescripcionVehiculo,
                ':TarjetaPropiedad' => $TarjetaPropiedad,
                ':FechaParqueadero' => $FechaParqueadero,
                ':IdSede' => $IdSede
            ]);

            $id = $this->conexion->lastInsertId();
            file_put_contents($this->logPath, "✅ Vehículo insertado ID: $id con Estado: Activo\n", FILE_APPEND);
            return ['success' => true, 'id' => $id, 'message' => 'Vehículo registrado correctamente'];
        } catch (PDOException $e) {
            $msg = "❌ Error en registrarVehiculo: " . $e->getMessage();
            file_put_contents($this->logPath, "$msg\n", FILE_APPEND);
            return ['success' => false, 'error' => $msg];
        }
    }

    // ✅ Actualizar QR del vehículo
    public function actualizarQR(int $idVehiculo, string $rutaQR): array {
        try {
            if (!$this->conexion) {
                return ['success' => false, 'error' => 'Conexión a la base de datos no disponible'];
            }

            $sql = "UPDATE parqueadero SET QrVehiculo = :qr WHERE IdParqueadero = :id";
            $stmt = $this->conexion->prepare($sql);
            $resultado = $stmt->execute([
                ':qr' => $rutaQR,
                ':id' => $idVehiculo
            ]);

            file_put_contents($this->logPath, "✅ QR actualizado para vehículo ID: $idVehiculo\n", FILE_APPEND);

            return [
                'success' => $resultado,
                'rows' => $stmt->rowCount()
            ];

        } catch (PDOException $e) {
            $msg = "❌ Error en actualizarQR: " . $e->getMessage();
            file_put_contents($this->logPath, "$msg\n", FILE_APPEND);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ✅ Obtener la ruta del QR de un vehículo
    public function obtenerQR(int $idVehiculo): ?string {
        try {
            if (!$this->conexion) {
                return null;
            }

            $sql = "SELECT QrVehiculo FROM parqueadero WHERE IdParqueadero = :id";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':id' => $idVehiculo]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $resultado['QrVehiculo'] ?? null;

        } catch (PDOException $e) {
            file_put_contents($this->logPath, "❌ Error en obtenerQR: " . $e->getMessage() . "\n", FILE_APPEND);
            return null;
        }
    }

    // ✅ Actualizar vehículo (sin tocar el Estado ni QR)
    public function actualizarVehiculo($id, $tipo, $descripcion, $idsede): array {
        try {
            $sql = "UPDATE parqueadero 
                    SET TipoVehiculo = :tipo, DescripcionVehiculo = :descripcion, IdSede = :idsede
                    WHERE IdParqueadero = :id";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([
                ':tipo' => $tipo,
                ':descripcion' => $descripcion,
                ':idsede' => $idsede,
                ':id' => $id
            ]);
            file_put_contents($this->logPath, "✅ Vehículo actualizado ID: $id\n", FILE_APPEND);
            return ['success' => true, 'message' => 'Vehículo actualizado correctamente'];
        } catch (PDOException $e) {
            $msg = "❌ Error al actualizar: " . $e->getMessage();
            file_put_contents($this->logPath, "$msg\n", FILE_APPEND);
            return ['success' => false, 'error' => $msg];
        }
    }

    // 🆕 Cambiar estado del vehículo (Activo <-> Inactivo) - SOFT DELETE
    public function cambiarEstado(int $idParqueadero, string $nuevoEstado): array {
        try {
            if (!in_array($nuevoEstado, ['Activo', 'Inactivo'])) {
                return ['success' => false, 'error' => 'Estado no válido'];
            }

            $sql = "UPDATE parqueadero SET Estado = :estado WHERE IdParqueadero = :id";
            $stmt = $this->conexion->prepare($sql);
            $resultado = $stmt->execute([
                ':estado' => $nuevoEstado,
                ':id' => $idParqueadero
            ]);

            file_put_contents($this->logPath, "✅ Estado cambiado a '$nuevoEstado' para vehículo ID: $idParqueadero\n", FILE_APPEND);

            return [
                'success' => $resultado,
                'rows' => $stmt->rowCount(),
                'nuevoEstado' => $nuevoEstado
            ];
        } catch (PDOException $e) {
            $msg = "❌ Error al cambiar estado: " . $e->getMessage();
            file_put_contents($this->logPath, "$msg\n", FILE_APPEND);
            return ['success' => false, 'error' => $msg];
        }
    }

    // ⚠️ DEPRECADO: Mantener por compatibilidad
    public function eliminarVehiculo($id): array {
        file_put_contents($this->logPath, "⚠️ ADVERTENCIA: Se llamó a eliminarVehiculo() (método deprecado). Use cambiarEstado() en su lugar.\n", FILE_APPEND);
        
        try {
            return $this->cambiarEstado((int)$id, 'Inactivo');
        } catch (Exception $e) {
            $msg = "❌ Error en eliminarVehiculo: " . $e->getMessage();
            file_put_contents($this->logPath, "$msg\n", FILE_APPEND);
            return ['success' => false, 'error' => $msg];
        }
    }

    // ✅ Obtener todos los vehículos ACTIVOS
    public function obtenerTodos(): array {
        try {
            $sql = "SELECT * FROM parqueadero WHERE Estado = 'Activo' ORDER BY IdParqueadero DESC";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            file_put_contents($this->logPath, "❌ Error en obtenerTodos: " . $e->getMessage() . "\n", FILE_APPEND);
            return [];
        }
    }

    // ✅ Obtener todos los vehículos (incluye activos e inactivos)
    public function obtenerTodosConEstado(): array {
        try {
            $sql = "SELECT * FROM parqueadero ORDER BY 
                    CASE 
                        WHEN Estado = 'Activo' THEN 1 
                        WHEN Estado = 'Inactivo' THEN 2 
                        ELSE 3 
                    END, 
                    IdParqueadero DESC";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            file_put_contents($this->logPath, "❌ Error en obtenerTodosConEstado: " . $e->getMessage() . "\n", FILE_APPEND);
            return [];
        }
    }

    // ✅ Obtener un vehículo por su ID
    public function obtenerPorId(int $idParqueadero): ?array {
        try {
            $sql = "SELECT * FROM parqueadero WHERE IdParqueadero = :id";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':id' => $idParqueadero]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            file_put_contents($this->logPath, "❌ Error en obtenerPorId: " . $e->getMessage() . "\n", FILE_APPEND);
            return null;
        }
    }

    // ✅ Verifica si existe un vehículo
    public function existe(int $idParqueadero): bool {
        try {
            $sql = "SELECT 1 FROM parqueadero WHERE IdParqueadero = :id LIMIT 1";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':id' => $idParqueadero]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>