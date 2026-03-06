<?php
require_once __DIR__ . '/../Core/conexion.php';

class ModeloVehiculo {
    private $conexion;
    private $logPath;

    public function __construct() {
        $this->logPath = __DIR__ . '/../controller/vehiculo_dispositivo/debug_log.txt';

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

    // ── VALIDACIÓN: Verificar si una placa ya existe ─────────────────────────
    public function existePlaca(string $placa, ?int $excluirId = null): array {
        try {
            if (!$this->conexion || empty(trim($placa))) return ['existe' => false];

            if ($excluirId !== null) {
                $sql = "SELECT IdVehiculo, TipoVehiculo, PlacaVehiculo 
                        FROM vehiculo 
                        WHERE PlacaVehiculo = :placa 
                        AND IdVehiculo != :id 
                        AND Estado = 'Activo'
                        LIMIT 1";
                $stmt = $this->conexion->prepare($sql);
                $stmt->execute([':placa' => $placa, ':id' => $excluirId]);
            } else {
                $sql = "SELECT IdVehiculo, TipoVehiculo, PlacaVehiculo 
                        FROM vehiculo 
                        WHERE PlacaVehiculo = :placa 
                        AND Estado = 'Activo'
                        LIMIT 1";
                $stmt = $this->conexion->prepare($sql);
                $stmt->execute([':placa' => $placa]);
            }

            $vehiculo = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($vehiculo) {
                file_put_contents($this->logPath, "⚠️ Placa duplicada encontrada: $placa\n", FILE_APPEND);
                return ['existe' => true, 'vehiculo' => $vehiculo];
            }
            return ['existe' => false];

        } catch (PDOException $e) {
            file_put_contents($this->logPath, "❌ Error en existePlaca: " . $e->getMessage() . "\n", FILE_APPEND);
            return ['existe' => false, 'error' => $e->getMessage()];
        }
    }

    // ── VALIDACIÓN: Verificar si una tarjeta de propiedad ya existe ──────────
    public function existeTarjetaPropiedad(string $tarjeta, ?int $excluirId = null): array {
        try {
            if (!$this->conexion || empty(trim($tarjeta))) return ['existe' => false];

            if ($excluirId !== null) {
                $sql = "SELECT IdVehiculo, TipoVehiculo, PlacaVehiculo, TarjetaPropiedad 
                        FROM vehiculo 
                        WHERE TarjetaPropiedad = :tarjeta 
                        AND IdVehiculo != :id 
                        AND Estado = 'Activo'
                        LIMIT 1";
                $stmt = $this->conexion->prepare($sql);
                $stmt->execute([':tarjeta' => $tarjeta, ':id' => $excluirId]);
            } else {
                $sql = "SELECT IdVehiculo, TipoVehiculo, PlacaVehiculo, TarjetaPropiedad 
                        FROM vehiculo 
                        WHERE TarjetaPropiedad = :tarjeta 
                        AND Estado = 'Activo'
                        LIMIT 1";
                $stmt = $this->conexion->prepare($sql);
                $stmt->execute([':tarjeta' => $tarjeta]);
            }

            $vehiculo = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($vehiculo) {
                file_put_contents($this->logPath, "⚠️ Tarjeta de propiedad duplicada: $tarjeta\n", FILE_APPEND);
                return ['existe' => true, 'vehiculo' => $vehiculo];
            }
            return ['existe' => false];

        } catch (PDOException $e) {
            file_put_contents($this->logPath, "❌ Error en existeTarjetaPropiedad: " . $e->getMessage() . "\n", FILE_APPEND);
            return ['existe' => false, 'error' => $e->getMessage()];
        }
    }

    // ── Registrar vehículo ───────────────────────────────────────────────────
    public function registrarVehiculo(
        $TipoVehiculo,
        $PlacaVehiculo,
        $DescripcionVehiculo,
        $TarjetaPropiedad,
        $FechaDeVehiculo,
        $IdSede,
        $IdFuncionario,
        $IdVisitante
    ): array {
        try {
            file_put_contents($this->logPath, "Punto 1: Preparando SQL INSERT\n", FILE_APPEND);

            $sql = "INSERT INTO vehiculo 
                    (TipoVehiculo, PlacaVehiculo, DescripcionVehiculo, TarjetaPropiedad,
                     FechaDeVehiculo, IdSede, Estado, QrVehiculo, IdFuncionario, IdVisitante)
                    VALUES 
                    (:TipoVehiculo, :PlacaVehiculo, :DescripcionVehiculo, :TarjetaPropiedad,
                     :FechaDeVehiculo, :IdSede, 'Activo', '', :IdFuncionario, :IdVisitante)";

            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([
                ':TipoVehiculo'        => $TipoVehiculo,
                ':PlacaVehiculo'       => $PlacaVehiculo,
                ':DescripcionVehiculo' => $DescripcionVehiculo,
                ':TarjetaPropiedad'    => $TarjetaPropiedad,
                ':FechaDeVehiculo'     => $FechaDeVehiculo,
                ':IdSede'              => $IdSede,
                ':IdFuncionario'       => $IdFuncionario ?: null,
                ':IdVisitante'         => $IdVisitante   ?: null,
            ]);

            $id = $this->conexion->lastInsertId();
            file_put_contents($this->logPath, "✅ Vehículo insertado ID: $id\n", FILE_APPEND);
            return ['success' => true, 'id' => $id, 'message' => 'Vehículo registrado correctamente'];

        } catch (PDOException $e) {
            $msg = "❌ Error en registrarVehiculo: " . $e->getMessage();
            file_put_contents($this->logPath, "$msg\n", FILE_APPEND);
            return ['success' => false, 'error' => $msg];
        }
    }

    // ── Actualizar QR ────────────────────────────────────────────────────────
    public function actualizarQR(int $idVehiculo, string $rutaQR): array {
        try {
            if (!$this->conexion) return ['success' => false, 'error' => 'Conexión no disponible'];
            $sql  = "UPDATE vehiculo SET QrVehiculo = :qr WHERE IdVehiculo = :id";
            $stmt = $this->conexion->prepare($sql);
            $resultado = $stmt->execute([':qr' => $rutaQR, ':id' => $idVehiculo]);
            file_put_contents($this->logPath, "✅ QR actualizado para vehículo ID: $idVehiculo\n", FILE_APPEND);
            return ['success' => $resultado, 'rows' => $stmt->rowCount()];
        } catch (PDOException $e) {
            $msg = "❌ Error en actualizarQR: " . $e->getMessage();
            file_put_contents($this->logPath, "$msg\n", FILE_APPEND);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ── Obtener ruta del QR ──────────────────────────────────────────────────
    public function obtenerQR(int $idVehiculo): ?string {
        try {
            if (!$this->conexion) return null;
            $sql  = "SELECT QrVehiculo FROM vehiculo WHERE IdVehiculo = :id";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':id' => $idVehiculo]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado['QrVehiculo'] ?? null;
        } catch (PDOException $e) {
            file_put_contents($this->logPath, "❌ Error en obtenerQR: " . $e->getMessage() . "\n", FILE_APPEND);
            return null;
        }
    }

    // ── Actualizar vehículo ──────────────────────────────────────────────────
    public function actualizarVehiculo($id, $tipo, $descripcion, $idsede): array {
        try {
            $sql = "UPDATE vehiculo 
                    SET TipoVehiculo = :tipo, DescripcionVehiculo = :descripcion, IdSede = :idsede
                    WHERE IdVehiculo = :id";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':tipo' => $tipo, ':descripcion' => $descripcion, ':idsede' => $idsede, ':id' => $id]);
            file_put_contents($this->logPath, "✅ Vehículo actualizado ID: $id\n", FILE_APPEND);
            return ['success' => true, 'message' => 'Vehículo actualizado correctamente'];
        } catch (PDOException $e) {
            $msg = "❌ Error al actualizar: " . $e->getMessage();
            file_put_contents($this->logPath, "$msg\n", FILE_APPEND);
            return ['success' => false, 'error' => $msg];
        }
    }

    // ── Cambiar estado ───────────────────────────────────────────────────────
    public function cambiarEstado(int $idVehiculo, string $nuevoEstado): array {
        try {
            if (!in_array($nuevoEstado, ['Activo', 'Inactivo']))
                return ['success' => false, 'error' => 'Estado no válido'];
            $sql  = "UPDATE vehiculo SET Estado = :estado WHERE IdVehiculo = :id";
            $stmt = $this->conexion->prepare($sql);
            $resultado = $stmt->execute([':estado' => $nuevoEstado, ':id' => $idVehiculo]);
            file_put_contents($this->logPath, "✅ Estado '$nuevoEstado' para vehículo ID: $idVehiculo\n", FILE_APPEND);
            return ['success' => $resultado, 'rows' => $stmt->rowCount(), 'nuevoEstado' => $nuevoEstado];
        } catch (PDOException $e) {
            $msg = "❌ Error al cambiar estado: " . $e->getMessage();
            file_put_contents($this->logPath, "$msg\n", FILE_APPEND);
            return ['success' => false, 'error' => $msg];
        }
    }

    // ── DEPRECADO ────────────────────────────────────────────────────────────
    public function eliminarVehiculo($id): array {
        file_put_contents($this->logPath, "⚠️ eliminarVehiculo() deprecado. Use cambiarEstado().\n", FILE_APPEND);
        return $this->cambiarEstado((int)$id, 'Inactivo');
    }

    // ── Obtener todos ACTIVOS ────────────────────────────────────────────────
    public function obtenerTodos(): array {
        try {
            $sql  = "SELECT * FROM vehiculo WHERE Estado = 'Activo' ORDER BY IdVehiculo DESC";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            file_put_contents($this->logPath, "❌ Error en obtenerTodos: " . $e->getMessage() . "\n", FILE_APPEND);
            return [];
        }
    }

    // ── Obtener todos con estado ─────────────────────────────────────────────
    public function obtenerTodosConEstado(): array {
        try {
            $sql = "SELECT * FROM vehiculo ORDER BY 
                    CASE WHEN Estado = 'Activo' THEN 1 WHEN Estado = 'Inactivo' THEN 2 ELSE 3 END,
                    IdVehiculo DESC";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            file_put_contents($this->logPath, "❌ Error en obtenerTodosConEstado: " . $e->getMessage() . "\n", FILE_APPEND);
            return [];
        }
    }

    // ── Obtener por ID ───────────────────────────────────────────────────────
    public function obtenerPorId(int $idVehiculo): ?array {
        try {
            $sql  = "SELECT * FROM vehiculo WHERE IdVehiculo = :id";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':id' => $idVehiculo]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            file_put_contents($this->logPath, "❌ Error en obtenerPorId: " . $e->getMessage() . "\n", FILE_APPEND);
            return null;
        }
    }

    // ── Verificar existencia ─────────────────────────────────────────────────
    public function existe(int $idVehiculo): bool {
        try {
            $sql  = "SELECT 1 FROM vehiculo WHERE IdVehiculo = :id LIMIT 1";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':id' => $idVehiculo]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }

    // ── Obtener funcionarios activos ─────────────────────────────────────────
    public function obtenerFuncionarios(): array {
        try {
            $sql  = "SELECT IdFuncionario, NombreFuncionario FROM funcionario WHERE Estado = 'Activo' ORDER BY NombreFuncionario ASC";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            file_put_contents($this->logPath, "❌ Error en obtenerFuncionarios: " . $e->getMessage() . "\n", FILE_APPEND);
            return [];
        }
    }

    // ── Obtener visitantes activos ───────────────────────────────────────────
    public function obtenerVisitantes(): array {
        try {
            $sql  = "SELECT IdVisitante, NombreVisitante FROM visitante WHERE Estado = 'Activo' ORDER BY NombreVisitante ASC";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            file_put_contents($this->logPath, "❌ Error en obtenerVisitantes: " . $e->getMessage() . "\n", FILE_APPEND);
            return [];
        }
    }

    // ── 🆕 Obtener correo del funcionario ligado a un vehículo ───────────────
    // Retorna el CorreoFuncionario si el vehículo tiene IdFuncionario, o null si no.
    public function obtenerCorreoFuncionarioPorVehiculo(int $idVehiculo): ?string {
        try {
            $sql = "SELECT f.CorreoFuncionario
                    FROM vehiculo v
                    INNER JOIN funcionario f ON v.IdFuncionario = f.IdFuncionario
                    WHERE v.IdVehiculo = :id
                    LIMIT 1";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':id' => $idVehiculo]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['CorreoFuncionario'] ?? null;
        } catch (PDOException $e) {
            file_put_contents($this->logPath, "❌ Error en obtenerCorreoFuncionarioPorVehiculo: " . $e->getMessage() . "\n", FILE_APPEND);
            return null;
        }
    }
}
?>