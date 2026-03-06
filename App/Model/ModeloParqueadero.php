<?php
class ModeloParqueadero {
    private $conexion;
    private $debugPath;

    public function __construct($conexion) {
        $this->conexion  = $conexion;
        $this->debugPath = __DIR__ . '/../Controller/Debug_Parqueadero/debug_log.txt';

        $carpetaDebug = dirname($this->debugPath);
        if (!file_exists($carpetaDebug)) {
            mkdir($carpetaDebug, 0777, true);
        }
    }

    private function log(string $msg): void {
        file_put_contents($this->debugPath, date('Y-m-d H:i:s') . " $msg\n", FILE_APPEND);
    }

    // ── Verificar si ya existe parqueadero para esa sede ──────────────────────
    public function existeParqueaderoPorSede(int $idSede, ?int $excluirId = null): bool {
        try {
            if ($excluirId !== null) {
                $sql  = "SELECT 1 FROM parqueadero WHERE IdSede = :sede AND IdParqueadero != :id LIMIT 1";
                $stmt = $this->conexion->prepare($sql);
                $stmt->execute([':sede' => $idSede, ':id' => $excluirId]);
            } else {
                $sql  = "SELECT 1 FROM parqueadero WHERE IdSede = :sede LIMIT 1";
                $stmt = $this->conexion->prepare($sql);
                $stmt->execute([':sede' => $idSede]);
            }
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            $this->log("❌ existeParqueaderoPorSede: " . $e->getMessage());
            return false;
        }
    }

    // ── Crear parqueadero + generar espacios individuales ─────────────────────
    public function crearParqueadero(int $idSede, int $total, int $carros, int $motos, int $bicis): array {
        try {
            $this->conexion->beginTransaction();

            $sql  = "INSERT INTO parqueadero
                        (CantidadParqueadero, CantidadCarros, CantidadMotos, CantidadBicicletas, Estado, IdSede)
                     VALUES (:total, :carros, :motos, :bicis, 'Activo', :sede)";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([
                ':total'  => $total,
                ':carros' => $carros,
                ':motos'  => $motos,
                ':bicis'  => $bicis,
                ':sede'   => $idSede,
            ]);
            $idParqueadero = (int)$this->conexion->lastInsertId();

            $this->generarEspacios($idParqueadero, $carros, $motos, $bicis);

            $this->conexion->commit();
            $this->log("✅ Parqueadero creado ID: $idParqueadero, Sede: $idSede");
            return ['success' => true, 'id' => $idParqueadero];

        } catch (PDOException $e) {
            $this->conexion->rollBack();
            $this->log("❌ crearParqueadero: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ── Generar espacios individuales numerados ───────────────────────────────
    private function generarEspacios(int $idParqueadero, int $carros, int $motos, int $bicis): void {
        $sql  = "INSERT INTO espacio_parqueadero (NumeroEspacio, TipoVehiculo, Estado, IdParqueadero)
                 VALUES (:numero, :tipo, 'Libre', :idp)";
        $stmt = $this->conexion->prepare($sql);

        $numero = 1;
        for ($i = 0; $i < $carros; $i++, $numero++) {
            $stmt->execute([':numero' => $numero, ':tipo' => 'Carro',     ':idp' => $idParqueadero]);
        }
        for ($i = 0; $i < $motos;  $i++, $numero++) {
            $stmt->execute([':numero' => $numero, ':tipo' => 'Moto',      ':idp' => $idParqueadero]);
        }
        for ($i = 0; $i < $bicis;  $i++, $numero++) {
            $stmt->execute([':numero' => $numero, ':tipo' => 'Bicicleta', ':idp' => $idParqueadero]);
        }
        $this->log("✅ Espacios generados: $carros carros, $motos motos, $bicis bicicletas");
    }

    // ── Actualizar parqueadero y ajustar espacios ─────────────────────────────
    public function actualizarParqueadero(int $id, int $total, int $carros, int $motos, int $bicis): array {
        try {
            $this->conexion->beginTransaction();

            $actual = $this->obtenerPorId($id);
            if (!$actual) throw new Exception('Parqueadero no encontrado');

            $sql  = "UPDATE parqueadero
                     SET CantidadParqueadero = :total,
                         CantidadCarros      = :carros,
                         CantidadMotos       = :motos,
                         CantidadBicicletas  = :bicis
                     WHERE IdParqueadero = :id";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([
                ':total'  => $total,
                ':carros' => $carros,
                ':motos'  => $motos,
                ':bicis'  => $bicis,
                ':id'     => $id,
            ]);

            $this->ajustarEspacios($id, 'Carro',     (int)$actual['CantidadCarros'],     $carros);
            $this->ajustarEspacios($id, 'Moto',      (int)$actual['CantidadMotos'],      $motos);
            $this->ajustarEspacios($id, 'Bicicleta', (int)$actual['CantidadBicicletas'], $bicis);
            $this->renumerarEspacios($id);

            $this->conexion->commit();
            $this->log("✅ Parqueadero actualizado ID: $id");
            return ['success' => true];

        } catch (Exception $e) {
            $this->conexion->rollBack();
            $this->log("❌ actualizarParqueadero: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ── Ajustar espacios de un tipo (agrega o elimina solo los LIBRES) ────────
    private function ajustarEspacios(int $idParqueadero, string $tipo, int $anterior, int $nuevo): void {
        $diferencia = $nuevo - $anterior;

        if ($diferencia > 0) {
            $sql  = "INSERT INTO espacio_parqueadero (NumeroEspacio, TipoVehiculo, Estado, IdParqueadero)
                     VALUES (0, :tipo, 'Libre', :idp)";
            $stmt = $this->conexion->prepare($sql);
            for ($i = 0; $i < $diferencia; $i++) {
                $stmt->execute([':tipo' => $tipo, ':idp' => $idParqueadero]);
            }
        } elseif ($diferencia < 0) {
            $eliminar = abs($diferencia);

            $sqlLibres = "SELECT COUNT(*) FROM espacio_parqueadero
                          WHERE IdParqueadero = :idp AND TipoVehiculo = :tipo AND Estado = 'Libre'";
            $stmtL = $this->conexion->prepare($sqlLibres);
            $stmtL->execute([':idp' => $idParqueadero, ':tipo' => $tipo]);
            $libres = (int)$stmtL->fetchColumn();

            if ($libres < $eliminar) {
                throw new Exception("No se pueden eliminar $eliminar espacios de $tipo: solo hay $libres libres disponibles.");
            }

            $sql  = "DELETE FROM espacio_parqueadero
                     WHERE IdParqueadero = :idp AND TipoVehiculo = :tipo AND Estado = 'Libre'
                     ORDER BY NumeroEspacio DESC
                     LIMIT :lim";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindValue(':idp',  $idParqueadero, PDO::PARAM_INT);
            $stmt->bindValue(':tipo', $tipo,          PDO::PARAM_STR);
            $stmt->bindValue(':lim',  $eliminar,      PDO::PARAM_INT);
            $stmt->execute();
        }
    }

    // ── Renumerar espacios de corrido (1, 2, 3…) ──────────────────────────────
    private function renumerarEspacios(int $idParqueadero): void {
        $sql  = "SELECT IdEspacio FROM espacio_parqueadero
                 WHERE IdParqueadero = :idp
                 ORDER BY TipoVehiculo, IdEspacio ASC";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([':idp' => $idParqueadero]);
        $espacios = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $sqlUp  = "UPDATE espacio_parqueadero SET NumeroEspacio = :num WHERE IdEspacio = :id";
        $stmtUp = $this->conexion->prepare($sqlUp);
        $numero = 1;
        foreach ($espacios as $idEspacio) {
            $stmtUp->execute([':num' => $numero++, ':id' => $idEspacio]);
        }
    }

    // ── Cambiar estado del parqueadero ────────────────────────────────────────
    public function cambiarEstado(int $id, string $nuevoEstado): array {
        try {
            if (!in_array($nuevoEstado, ['Activo', 'Inactivo'])) {
                return ['success' => false, 'error' => 'Estado no válido'];
            }
            $sql  = "UPDATE parqueadero SET Estado = :estado WHERE IdParqueadero = :id";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':estado' => $nuevoEstado, ':id' => $id]);
            $this->log("✅ Estado '$nuevoEstado' para parqueadero ID: $id");
            return ['success' => true, 'rows' => $stmt->rowCount(), 'nuevoEstado' => $nuevoEstado];
        } catch (PDOException $e) {
            $this->log("❌ cambiarEstado: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ── Obtener todos con conteo de libres/ocupados ───────────────────────────
    public function obtenerTodos(): array {
        try {
            $sql = "SELECT p.*,
                           s.TipoSede, s.Ciudad,
                           (SELECT COUNT(*) FROM espacio_parqueadero e
                            WHERE e.IdParqueadero = p.IdParqueadero AND e.Estado = 'Libre')   AS EspaciosLibres,
                           (SELECT COUNT(*) FROM espacio_parqueadero e
                            WHERE e.IdParqueadero = p.IdParqueadero AND e.Estado = 'Ocupado') AS EspaciosOcupados
                    FROM parqueadero p
                    LEFT JOIN sede s ON p.IdSede = s.IdSede
                    ORDER BY p.IdParqueadero DESC";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->log("❌ obtenerTodos: " . $e->getMessage());
            return [];
        }
    }

    // ── Obtener parqueadero por ID ────────────────────────────────────────────
    public function obtenerPorId(int $id): ?array {
        try {
            $sql  = "SELECT * FROM parqueadero WHERE IdParqueadero = :id";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            $this->log("❌ obtenerPorId: " . $e->getMessage());
            return null;
        }
    }

    // ── Obtener espacios individuales con placa si está ocupado ──────────────
    public function obtenerEspacios(int $idParqueadero): array {
        try {
            $sql = "SELECT e.*,
                           v.PlacaVehiculo,
                           v.TipoVehiculo  AS TipoVehiculoRegistrado,
                           v.DescripcionVehiculo
                    FROM espacio_parqueadero e
                    LEFT JOIN vehiculo v ON e.IdVehiculo = v.IdVehiculo
                    WHERE e.IdParqueadero = :idp
                    ORDER BY e.NumeroEspacio ASC";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':idp' => $idParqueadero]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->log("❌ obtenerEspacios: " . $e->getMessage());
            return [];
        }
    }

    // ── Resumen de espacios por tipo (para tarjetas) ──────────────────────────
    public function obtenerResumenEspacios(int $idParqueadero): array {
        try {
            $sql = "SELECT
                        TipoVehiculo,
                        COUNT(*) AS Total,
                        SUM(CASE WHEN Estado = 'Libre'   THEN 1 ELSE 0 END) AS Libres,
                        SUM(CASE WHEN Estado = 'Ocupado' THEN 1 ELSE 0 END) AS Ocupados
                    FROM espacio_parqueadero
                    WHERE IdParqueadero = :idp
                    GROUP BY TipoVehiculo";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':idp' => $idParqueadero]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->log("❌ obtenerResumenEspacios: " . $e->getMessage());
            return [];
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    // MÉTODOS GUARDIA — Vista de espacios y gestión manual
    // ══════════════════════════════════════════════════════════════════════════

    // ── Sedes activas que tienen parqueadero configurado ─────────────────────
    public function obtenerSedesConParqueadero(): array {
        try {
            $sql  = "SELECT s.IdSede, s.TipoSede, s.Ciudad
                     FROM sede s
                     INNER JOIN parqueadero p ON s.IdSede = p.IdSede
                     WHERE s.Estado = 'Activo' AND p.Estado = 'Activo'
                     ORDER BY s.TipoSede ASC";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->log("❌ obtenerSedesConParqueadero: " . $e->getMessage());
            return [];
        }
    }

    // ── Parqueadero activo de una sede con datos de sede ─────────────────────
    public function obtenerParqueaderoPorSede(int $idSede): ?array {
        try {
            $sql  = "SELECT p.*, s.TipoSede, s.Ciudad
                     FROM parqueadero p
                     INNER JOIN sede s ON p.IdSede = s.IdSede
                     WHERE p.IdSede = :sede AND p.Estado = 'Activo'
                     LIMIT 1";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':sede' => $idSede]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            $this->log("❌ obtenerParqueaderoPorSede: " . $e->getMessage());
            return null;
        }
    }

    // ── Espacios con detalle completo (incluye propietario del vehículo) ──────
    public function obtenerEspaciosDetalle(int $idParqueadero): array {
        try {
            $sql = "SELECT e.*,
                           v.PlacaVehiculo,
                           v.TipoVehiculo      AS TipoVehiculoRegistrado,
                           v.DescripcionVehiculo,
                           f.NombreFuncionario,
                           vis.NombreVisitante
                    FROM espacio_parqueadero e
                    LEFT JOIN vehiculo    v   ON e.IdVehiculo    = v.IdVehiculo
                    LEFT JOIN funcionario f   ON v.IdFuncionario = f.IdFuncionario
                    LEFT JOIN visitante   vis ON v.IdVisitante   = vis.IdVisitante
                    WHERE e.IdParqueadero = :idp
                    ORDER BY e.TipoVehiculo ASC, e.NumeroEspacio ASC";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':idp' => $idParqueadero]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->log("❌ obtenerEspaciosDetalle: " . $e->getMessage());
            return [];
        }
    }

    // ── Vehículo por placa ────────────────────────────────────────────────────
    public function obtenerVehiculoPorPlaca(string $placa): ?array {
        try {
            $sql  = "SELECT v.*, f.NombreFuncionario, vis.NombreVisitante
                     FROM vehiculo v
                     LEFT JOIN funcionario f   ON v.IdFuncionario = f.IdFuncionario
                     LEFT JOIN visitante   vis ON v.IdVisitante   = vis.IdVisitante
                     WHERE v.PlacaVehiculo = :placa AND v.Estado = 'Activo'
                     LIMIT 1";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':placa' => strtoupper(trim($placa))]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            $this->log("❌ obtenerVehiculoPorPlaca: " . $e->getMessage());
            return null;
        }
    }

    // ── Espacio ocupado por un vehículo en un parqueadero ────────────────────
    public function obtenerEspacioOcupadoPorVehiculo(int $idVehiculo, int $idParqueadero): ?array {
        try {
            $sql  = "SELECT * FROM espacio_parqueadero
                     WHERE IdVehiculo = :idv AND IdParqueadero = :idp AND Estado = 'Ocupado'
                     LIMIT 1";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':idv' => $idVehiculo, ':idp' => $idParqueadero]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            $this->log("❌ obtenerEspacioOcupadoPorVehiculo: " . $e->getMessage());
            return null;
        }
    }

    // ── Primer espacio libre del tipo del vehículo ────────────────────────────
    public function obtenerPrimerEspacioLibre(int $idParqueadero, string $tipo): ?array {
        try {
            $sql  = "SELECT * FROM espacio_parqueadero
                     WHERE IdParqueadero = :idp AND TipoVehiculo = :tipo AND Estado = 'Libre'
                     ORDER BY NumeroEspacio ASC
                     LIMIT 1";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':idp' => $idParqueadero, ':tipo' => $tipo]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            $this->log("❌ obtenerPrimerEspacioLibre: " . $e->getMessage());
            return null;
        }
    }

    // ── OCUPAR espacio (entrada manual o por escáner) ─────────────────────────
    public function ocuparEspacio(int $idEspacio, int $idVehiculo): array {
        try {
            $sql  = "UPDATE espacio_parqueadero
                     SET Estado = 'Ocupado', IdVehiculo = :idv
                     WHERE IdEspacio = :ide AND Estado = 'Libre'";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':idv' => $idVehiculo, ':ide' => $idEspacio]);

            if ($stmt->rowCount() === 0) {
                return ['success' => false, 'error' => 'El espacio ya fue ocupado por otro vehículo'];
            }
            $this->log("✅ Espacio $idEspacio ocupado por vehículo $idVehiculo");
            return ['success' => true];
        } catch (PDOException $e) {
            $this->log("❌ ocuparEspacio: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ── LIBERAR espacio (salida manual o por escáner) ─────────────────────────
    public function liberarEspacio(int $idEspacio): array {
        try {
            $sql  = "UPDATE espacio_parqueadero
                     SET Estado = 'Libre', IdVehiculo = NULL
                     WHERE IdEspacio = :ide AND Estado = 'Ocupado'";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':ide' => $idEspacio]);

            if ($stmt->rowCount() === 0) {
                return ['success' => false, 'error' => 'El espacio ya estaba libre'];
            }
            $this->log("✅ Espacio $idEspacio liberado");
            return ['success' => true];
        } catch (PDOException $e) {
            $this->log("❌ liberarEspacio: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    // MÉTODO PRINCIPAL PARA EL ESCÁNER (integración con módulo del compañero)
    // ══════════════════════════════════════════════════════════════════════════
    /**
     * CÓMO CONECTAR EL ESCÁNER:
     * El módulo escáner debe enviar POST a ControladorParqueadero.php con:
     *   accion      => 'escanear_qr'
     *   placa       => 'ABC123'       ← placa leída del QR
     *   id_sede     => 3              ← sede donde está el lector
     *   tipo_evento => 'entrada'      ← o 'salida'
     *
     * Respuesta JSON:
     *   { "success": true,  "espacio": 5, "mensaje": "Espacio #5 asignado" }
     *   { "success": false, "sin_espacio": true, "tipo": "Carro", "mensaje": "..." }
     *
     * NOTA: Si el QR guarda IdVehiculo en lugar de placa, reemplazar
     *       obtenerVehiculoPorPlaca() por obtenerVehiculoPorId() aquí.
     */
    public function procesarEscaneo(string $placa, int $idSede, string $tipoEvento): array {
        try {
            $vehiculo = $this->obtenerVehiculoPorPlaca($placa);
            if (!$vehiculo) {
                return ['success' => false, 'mensaje' => "Vehículo '$placa' no encontrado o inactivo"];
            }

            $parqueadero = $this->obtenerParqueaderoPorSede($idSede);
            if (!$parqueadero) {
                return ['success' => false, 'mensaje' => 'No hay parqueadero activo para esta sede'];
            }

            $idParqueadero = (int)$parqueadero['IdParqueadero'];
            $idVehiculo    = (int)$vehiculo['IdVehiculo'];
            $tipoVehiculo  = $vehiculo['TipoVehiculo'];

            if ($tipoEvento === 'entrada') {
                $espacioActual = $this->obtenerEspacioOcupadoPorVehiculo($idVehiculo, $idParqueadero);
                if ($espacioActual) {
                    return [
                        'success' => false,
                        'mensaje' => "El vehículo '$placa' ya ocupa el espacio #{$espacioActual['NumeroEspacio']}"
                    ];
                }

                $espacioLibre = $this->obtenerPrimerEspacioLibre($idParqueadero, $tipoVehiculo);
                if (!$espacioLibre) {
                    return [
                        'success'     => false,
                        'sin_espacio' => true,
                        'tipo'        => $tipoVehiculo,
                        'mensaje'     => "Sin espacios disponibles para $tipoVehiculo. El guardia debe decidir."
                    ];
                }

                $r = $this->ocuparEspacio((int)$espacioLibre['IdEspacio'], $idVehiculo);
                if ($r['success']) {
                    return [
                        'success'       => true,
                        'tipo_evento'   => 'entrada',
                        'espacio'       => (int)$espacioLibre['NumeroEspacio'],
                        'id_espacio'    => (int)$espacioLibre['IdEspacio'],
                        'placa'         => $placa,
                        'tipo_vehiculo' => $tipoVehiculo,
                        'mensaje'       => "Entrada registrada. Espacio #{$espacioLibre['NumeroEspacio']} asignado"
                    ];
                }
                return ['success' => false, 'mensaje' => $r['error'] ?? 'Error al ocupar espacio'];

            } elseif ($tipoEvento === 'salida') {
                $espacioOcupado = $this->obtenerEspacioOcupadoPorVehiculo($idVehiculo, $idParqueadero);
                if (!$espacioOcupado) {
                    return ['success' => false, 'mensaje' => "El vehículo '$placa' no tiene espacio ocupado en esta sede"];
                }

                $r = $this->liberarEspacio((int)$espacioOcupado['IdEspacio']);
                if ($r['success']) {
                    return [
                        'success'       => true,
                        'tipo_evento'   => 'salida',
                        'espacio'       => (int)$espacioOcupado['NumeroEspacio'],
                        'id_espacio'    => (int)$espacioOcupado['IdEspacio'],
                        'placa'         => $placa,
                        'tipo_vehiculo' => $tipoVehiculo,
                        'mensaje'       => "Salida registrada. Espacio #{$espacioOcupado['NumeroEspacio']} liberado"
                    ];
                }
                return ['success' => false, 'mensaje' => $r['error'] ?? 'Error al liberar espacio'];

            } else {
                return ['success' => false, 'mensaje' => "Tipo de evento inválido: '$tipoEvento'"];
            }

        } catch (Exception $e) {
            $this->log("❌ procesarEscaneo: " . $e->getMessage());
            return ['success' => false, 'mensaje' => 'Error interno: ' . $e->getMessage()];
        }
    }
}
?>