<?php
require_once __DIR__ . '/../../Core/conexion.php';

class ModeloParqueadero {
    private $conexion;

    public function __construct() {
        try {
            $conexionObj = new Conexion();
            $this->conexion = $conexionObj->getConexion();
            file_put_contents(__DIR__ . '/debug_log.txt', "✅ Conexión establecida correctamente\n", FILE_APPEND);
        } catch (PDOException $e) {
            $msg = "❌ Error de conexión: " . $e->getMessage();
            file_put_contents(__DIR__ . '/debug_log.txt', "$msg\n", FILE_APPEND);
            throw new Exception($msg);
        }
    }

    // ✅ Registrar vehículo (ahora con Estado = 'Activo' por defecto)
    public function registrarVehiculo($TipoVehiculo, $PlacaVehiculo, $DescripcionVehiculo, $TarjetaPropiedad, $FechaParqueadero, $IdSede): array {
        try {
            $sql = "INSERT INTO parqueadero 
                    (TipoVehiculo, PlacaVehiculo, DescripcionVehiculo, TarjetaPropiedad, FechaParqueadero, IdSede, Estado)
                    VALUES (:TipoVehiculo, :PlacaVehiculo, :DescripcionVehiculo, :TarjetaPropiedad, :FechaParqueadero, :IdSede, 'Activo')";
            $stmt = $this->conexion->prepare($sql);

            $stmt->execute([
                ':TipoVehiculo' => $TipoVehiculo,
                ':PlacaVehiculo' => $PlacaVehiculo,
                ':DescripcionVehiculo' => $DescripcionVehiculo,
                ':TarjetaPropiedad' => $TarjetaPropiedad,
                ':FechaParqueadero' => $FechaParqueadero,
                ':IdSede' => $IdSede
            ]);

            $id = $this->conexion->lastInsertId();
            file_put_contents(__DIR__ . '/debug_log.txt', "✅ Vehículo insertado ID: $id con Estado: Activo\n", FILE_APPEND);
            return ['success' => true, 'id' => $id, 'message' => 'Vehículo registrado correctamente'];
        } catch (PDOException $e) {
            $msg = "❌ Error en registrarVehiculo: " . $e->getMessage();
            file_put_contents(__DIR__ . '/debug_log.txt', "$msg\n", FILE_APPEND);
            return ['success' => false, 'error' => $msg];
        }
    }

    // ✅ Actualizar vehículo (sin tocar el Estado)
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
            file_put_contents(__DIR__ . '/debug_log.txt', "✅ Vehículo actualizado ID: $id\n", FILE_APPEND);
            return ['success' => true, 'message' => 'Vehículo actualizado correctamente'];
        } catch (PDOException $e) {
            $msg = "❌ Error al actualizar: " . $e->getMessage();
            file_put_contents(__DIR__ . '/debug_log.txt', "$msg\n", FILE_APPEND);
            return ['success' => false, 'error' => $msg];
        }
    }

    // 🆕 Cambiar estado del vehículo (Activo <-> Inactivo) - SOFT DELETE
    public function cambiarEstado(int $idParqueadero, string $nuevoEstado): array {
        try {
            // Validar que el estado sea válido
            if (!in_array($nuevoEstado, ['Activo', 'Inactivo'])) {
                return ['success' => false, 'error' => 'Estado no válido'];
            }

            $sql = "UPDATE parqueadero SET Estado = :estado WHERE IdParqueadero = :id";
            $stmt = $this->conexion->prepare($sql);
            $resultado = $stmt->execute([
                ':estado' => $nuevoEstado,
                ':id' => $idParqueadero
            ]);

            file_put_contents(__DIR__ . '/debug_log.txt', "✅ Estado cambiado a '$nuevoEstado' para vehículo ID: $idParqueadero\n", FILE_APPEND);

            return [
                'success' => $resultado,
                'rows' => $stmt->rowCount(),
                'nuevoEstado' => $nuevoEstado
            ];
        } catch (PDOException $e) {
            $msg = "❌ Error al cambiar estado: " . $e->getMessage();
            file_put_contents(__DIR__ . '/debug_log.txt', "$msg\n", FILE_APPEND);
            return ['success' => false, 'error' => $msg];
        }
    }

    // ⚠️ DEPRECADO: Mantener por compatibilidad pero registrar advertencia
    // Se recomienda usar cambiarEstado() en su lugar
    public function eliminarVehiculo($id): array {
        file_put_contents(__DIR__ . '/debug_log.txt', "⚠️ ADVERTENCIA: Se llamó a eliminarVehiculo() (método deprecado). Use cambiarEstado() en su lugar.\n", FILE_APPEND);
        
        try {
            // En lugar de eliminar, cambiar a Inactivo
            return $this->cambiarEstado((int)$id, 'Inactivo');
        } catch (Exception $e) {
            $msg = "❌ Error en eliminarVehiculo: " . $e->getMessage();
            file_put_contents(__DIR__ . '/debug_log.txt', "$msg\n", FILE_APPEND);
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
            file_put_contents(__DIR__ . '/debug_log.txt', "❌ Error en obtenerTodos: " . $e->getMessage() . "\n", FILE_APPEND);
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
            file_put_contents(__DIR__ . '/debug_log.txt', "❌ Error en obtenerTodosConEstado: " . $e->getMessage() . "\n", FILE_APPEND);
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
            file_put_contents(__DIR__ . '/debug_log.txt', "❌ Error en obtenerPorId: " . $e->getMessage() . "\n", FILE_APPEND);
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