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

    // ✅ Registrar vehículo
    public function registrarVehiculo($TipoVehiculo, $PlacaVehiculo, $DescripcionVehiculo, $TarjetaPropiedad, $FechaParqueadero, $IdSede): array {
        try {
            $sql = "INSERT INTO parqueadero 
                    (TipoVehiculo, PlacaVehiculo, DescripcionVehiculo, TarjetaPropiedad, FechaParqueadero, IdSede)
                    VALUES (:TipoVehiculo, :PlacaVehiculo, :DescripcionVehiculo, :TarjetaPropiedad, :FechaParqueadero, :IdSede)";
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
            file_put_contents(__DIR__ . '/debug_log.txt', "✅ Vehículo insertado ID: $id\n", FILE_APPEND);
            return ['success' => true, 'id' => $id];
        } catch (PDOException $e) {
            $msg = "❌ Error en registrarVehiculo: " . $e->getMessage();
            file_put_contents(__DIR__ . '/debug_log.txt', "$msg\n", FILE_APPEND);
            return ['success' => false, 'error' => $msg];
        }
    }

    // ✅ Actualizar vehículo
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

    // ✅ Eliminar vehículo
    public function eliminarVehiculo($id): array {
        try {
            $sql = "DELETE FROM parqueadero WHERE IdParqueadero = :id";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':id' => $id]);
            file_put_contents(__DIR__ . '/debug_log.txt', "✅ Vehículo eliminado ID: $id\n", FILE_APPEND);
            return ['success' => true, 'message' => 'Vehículo eliminado correctamente'];
        } catch (PDOException $e) {
            $msg = "❌ Error al eliminar: " . $e->getMessage();
            file_put_contents(__DIR__ . '/debug_log.txt', "$msg\n", FILE_APPEND);
            return ['success' => false, 'error' => $msg];
        }
    }
}
?>
