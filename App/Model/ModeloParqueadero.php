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

    // ══════════════════════════════════════════════════════════════════════════
    // MÉTODOS ADMIN — Gestión de parqueaderos
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Verifica si ya existe parqueadero para esa sede
     */
    public function existeParqueaderoPorSede(int $idSede, ?int $excluirId = null): bool {
        try {
            if (!$this->conexion) return false;

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
            file_put_contents($this->debugPath, "ERROR en existeParqueaderoPorSede: " . $e->getMessage() . "\n", FILE_APPEND);
            return false;
        }
    }

    /**
     * Crea un parqueadero y genera sus espacios individuales
     */
    public function crearParqueadero(int $idSede, int $total, int $carros, int $motos, int $bicis): array {
        try {
            file_put_contents($this->debugPath,
                "=== MODELO: crearParqueadero ===\nSede: $idSede, Total: $total, Carros: $carros, Motos: $motos, Bicis: $bicis\n",
                FILE_APPEND);

            if (!$this->conexion) {
                return ['success' => false, 'error' => 'Conexión a la base de datos no disponible'];
            }

            $this->conexion->beginTransaction();

            $sql  = "INSERT INTO parqueadero
                        (CantidadParqueadero, CantidadCarros, CantidadMotos, CantidadBicicletas, Estado, IdSede)
                     VALUES (:total, :carros, :motos, :bicis, 'Activo', :sede)";
            $stmt = $this->conexion->prepare($sql);
            $resultado = $stmt->execute([
                ':total'  => $total,
                ':carros' => $carros,
                ':motos'  => $motos,
                ':bicis'  => $bicis,
                ':sede'   => $idSede,
            ]);

            if (!$resultado) {
                $errorInfo = $stmt->errorInfo();
                file_put_contents($this->debugPath, "ERROR en execute INSERT: " . json_encode($errorInfo) . "\n", FILE_APPEND);
                $this->conexion->rollBack();
                return ['success' => false, 'error' => $errorInfo[2] ?? 'Error desconocido al insertar'];
            }

            $idParqueadero = (int)$this->conexion->lastInsertId();
            file_put_contents($this->debugPath, "INSERT exitoso, ID: $idParqueadero\n", FILE_APPEND);

            $this->generarEspacios($idParqueadero, $carros, $motos, $bicis);

            $this->conexion->commit();
            file_put_contents($this->debugPath, "COMMIT exitoso — Parqueadero $idParqueadero creado\n", FILE_APPEND);
            return ['success' => true, 'id' => $idParqueadero];

        } catch (PDOException $e) {
            $this->conexion->rollBack();
            file_put_contents($this->debugPath, "EXCEPCIÓN PDO crearParqueadero: " . $e->getMessage() . "\n", FILE_APPEND);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Genera espacios numerados por tipo para un parqueadero
     */
    private function generarEspacios(int $idParqueadero, int $carros, int $motos, int $bicis): void {
        file_put_contents($this->debugPath,
            "=== MODELO: generarEspacios — Parqueadero: $idParqueadero, Carros: $carros, Motos: $motos, Bicis: $bicis ===\n",
            FILE_APPEND);

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

        $total = $carros + $motos + $bicis;
        file_put_contents($this->debugPath, "$total espacios generados correctamente\n", FILE_APPEND);
    }

    /**
     * Actualiza un parqueadero y ajusta sus espacios
     */
    public function actualizarParqueadero(int $id, int $total, int $carros, int $motos, int $bicis): array {
        try {
            file_put_contents($this->debugPath,
                "=== MODELO: actualizarParqueadero ID: $id ===\nTotal: $total, Carros: $carros, Motos: $motos, Bicis: $bicis\n",
                FILE_APPEND);

            if (!$this->conexion) {
                return ['success' => false, 'error' => 'Conexión a la base de datos no disponible'];
            }

            $this->conexion->beginTransaction();

            $actual = $this->obtenerPorId($id);
            if (!$actual) {
                $this->conexion->rollBack();
                return ['success' => false, 'error' => "Parqueadero ID $id no encontrado"];
            }

            file_put_contents($this->debugPath,
                "Valores actuales — Carros: {$actual['CantidadCarros']}, Motos: {$actual['CantidadMotos']}, Bicis: {$actual['CantidadBicicletas']}\n",
                FILE_APPEND);

            $sql  = "UPDATE parqueadero
                     SET CantidadParqueadero = :total,
                         CantidadCarros      = :carros,
                         CantidadMotos       = :motos,
                         CantidadBicicletas  = :bicis
                     WHERE IdParqueadero = :id";
            $stmt = $this->conexion->prepare($sql);
            $resultado = $stmt->execute([
                ':total'  => $total,
                ':carros' => $carros,
                ':motos'  => $motos,
                ':bicis'  => $bicis,
                ':id'     => $id,
            ]);

            if (!$resultado) {
                $errorInfo = $stmt->errorInfo();
                file_put_contents($this->debugPath, "Error SQL UPDATE: " . json_encode($errorInfo) . "\n", FILE_APPEND);
                $this->conexion->rollBack();
                return ['success' => false, 'error' => $errorInfo[2] ?? 'Error desconocido'];
            }

            $this->ajustarEspacios($id, 'Carro',     (int)$actual['CantidadCarros'],     $carros);
            $this->ajustarEspacios($id, 'Moto',      (int)$actual['CantidadMotos'],      $motos);
            $this->ajustarEspacios($id, 'Bicicleta', (int)$actual['CantidadBicicletas'], $bicis);
            $this->renumerarEspacios($id);

            $this->conexion->commit();
            file_put_contents($this->debugPath, "COMMIT exitoso — Parqueadero $id actualizado\n", FILE_APPEND);
            return ['success' => true];

        } catch (Exception $e) {
            $this->conexion->rollBack();
            file_put_contents($this->debugPath, "EXCEPCIÓN actualizarParqueadero: " . $e->getMessage() . "\n", FILE_APPEND);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Ajusta espacios de un tipo (agrega o elimina solo los LIBRES)
     */
    private function ajustarEspacios(int $idParqueadero, string $tipo, int $anterior, int $nuevo): void {
        $diferencia = $nuevo - $anterior;
        file_put_contents($this->debugPath,
            "ajustarEspacios tipo=$tipo anterior=$anterior nuevo=$nuevo diferencia=$diferencia\n",
            FILE_APPEND);

        if ($diferencia > 0) {
            $sql  = "INSERT INTO espacio_parqueadero (NumeroEspacio, TipoVehiculo, Estado, IdParqueadero)
                     VALUES (0, :tipo, 'Libre', :idp)";
            $stmt = $this->conexion->prepare($sql);
            for ($i = 0; $i < $diferencia; $i++) {
                $stmt->execute([':tipo' => $tipo, ':idp' => $idParqueadero]);
            }
            file_put_contents($this->debugPath, "$diferencia espacios de $tipo agregados\n", FILE_APPEND);

        } elseif ($diferencia < 0) {
            $eliminar = abs($diferencia);

            $sqlLibres = "SELECT COUNT(*) FROM espacio_parqueadero
                          WHERE IdParqueadero = :idp AND TipoVehiculo = :tipo AND Estado = 'Libre'";
            $stmtL = $this->conexion->prepare($sqlLibres);
            $stmtL->execute([':idp' => $idParqueadero, ':tipo' => $tipo]);
            $libres = (int)$stmtL->fetchColumn();

            file_put_contents($this->debugPath, "Libres disponibles de $tipo: $libres, a eliminar: $eliminar\n", FILE_APPEND);

            if ($libres < $eliminar) {
                $msg = "No se pueden eliminar $eliminar espacios de $tipo: solo hay $libres libres disponibles.";
                file_put_contents($this->debugPath, "ERROR: $msg\n", FILE_APPEND);
                throw new Exception($msg);
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
            file_put_contents($this->debugPath, "$eliminar espacios de $tipo eliminados\n", FILE_APPEND);
        }
    }

    /**
     * Renumera espacios de corrido (1, 2, 3…)
     */
    private function renumerarEspacios(int $idParqueadero): void {
        file_put_contents($this->debugPath, "=== MODELO: renumerarEspacios Parqueadero: $idParqueadero ===\n", FILE_APPEND);

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
        file_put_contents($this->debugPath, count($espacios) . " espacios renumerados\n", FILE_APPEND);
    }

    /**
     * Cambia el estado del parqueadero (Activo <-> Inactivo)
     */
    public function cambiarEstado(int $id, string $nuevoEstado): array {
        try {
            if (!$this->conexion) {
                return ['success' => false, 'error' => 'Conexión no disponible'];
            }

            if (!in_array($nuevoEstado, ['Activo', 'Inactivo'])) {
                return ['success' => false, 'error' => 'Estado no válido'];
            }

            $sql  = "UPDATE parqueadero SET Estado = :estado WHERE IdParqueadero = :id";
            $stmt = $this->conexion->prepare($sql);
            $resultado = $stmt->execute([':estado' => $nuevoEstado, ':id' => $id]);

            if (!$resultado) {
                $errorInfo = $stmt->errorInfo();
                file_put_contents($this->debugPath, "Error SQL cambiarEstado: " . json_encode($errorInfo) . "\n", FILE_APPEND);
                return ['success' => false, 'error' => $errorInfo[2] ?? 'Error desconocido'];
            }

            return ['success' => true, 'rows' => $stmt->rowCount(), 'nuevoEstado' => $nuevoEstado];

        } catch (PDOException $e) {
            file_put_contents($this->debugPath, "EXCEPCIÓN PDO cambiarEstado: " . $e->getMessage() . "\n", FILE_APPEND);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Obtiene todos los parqueaderos con conteo de espacios libres/ocupados
     */
    public function obtenerTodos(): array {
        try {
            if (!$this->conexion) return [];

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
            file_put_contents($this->debugPath, "ERROR en obtenerTodos: " . $e->getMessage() . "\n", FILE_APPEND);
            return [];
        }
    }

    /**
     * Obtiene un parqueadero por su ID
     */
    public function obtenerPorId(int $id): ?array {
        try {
            if (!$this->conexion) return null;

            $sql  = "SELECT * FROM parqueadero WHERE IdParqueadero = :id";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

        } catch (PDOException $e) {
            file_put_contents($this->debugPath, "ERROR en obtenerPorId: " . $e->getMessage() . "\n", FILE_APPEND);
            return null;
        }
    }

    /**
     * Obtiene espacios individuales de un parqueadero (admin — modal ver espacios)
     */
    public function obtenerEspacios(int $idParqueadero): array {
        try {
            if (!$this->conexion) return [];

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
            file_put_contents($this->debugPath, "ERROR en obtenerEspacios: " . $e->getMessage() . "\n", FILE_APPEND);
            return [];
        }
    }

    /**
     * Resumen de espacios por tipo (Carro, Moto, Bicicleta)
     */
    public function obtenerResumenEspacios(int $idParqueadero): array {
        try {
            if (!$this->conexion) return [];

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
            file_put_contents($this->debugPath, "ERROR en obtenerResumenEspacios: " . $e->getMessage() . "\n", FILE_APPEND);
            return [];
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    // MÉTODOS GUARDIA — Vista de espacios y gestión manual
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Obtiene las sedes activas que tienen parqueadero configurado
     */
    public function obtenerSedesConParqueadero(): array {
        try {
            if (!$this->conexion) return [];

            $sql  = "SELECT s.IdSede, s.TipoSede, s.Ciudad
                     FROM sede s
                     INNER JOIN parqueadero p ON s.IdSede = p.IdSede
                     WHERE s.Estado = 'Activo' AND p.Estado = 'Activo'
                     ORDER BY s.TipoSede ASC";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            file_put_contents($this->debugPath, "ERROR en obtenerSedesConParqueadero: " . $e->getMessage() . "\n", FILE_APPEND);
            return [];
        }
    }

    /**
     * Obtiene el parqueadero activo de una sede con datos de la sede
     */
    public function obtenerParqueaderoPorSede(int $idSede): ?array {
        try {
            if (!$this->conexion) return null;

            file_put_contents($this->debugPath, "=== MODELO: obtenerParqueaderoPorSede — Sede: $idSede ===\n", FILE_APPEND);

            $sql  = "SELECT p.*, s.TipoSede, s.Ciudad
                     FROM parqueadero p
                     INNER JOIN sede s ON p.IdSede = s.IdSede
                     WHERE p.IdSede = :sede AND p.Estado = 'Activo'
                     LIMIT 1";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':sede' => $idSede]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($resultado) {
                file_put_contents($this->debugPath, "Parqueadero encontrado ID: {$resultado['IdParqueadero']}\n", FILE_APPEND);
            } else {
                file_put_contents($this->debugPath, "No hay parqueadero activo para sede $idSede\n", FILE_APPEND);
            }
            return $resultado ?: null;

        } catch (PDOException $e) {
            file_put_contents($this->debugPath, "ERROR en obtenerParqueaderoPorSede: " . $e->getMessage() . "\n", FILE_APPEND);
            return null;
        }
    }

    /**
     * Obtiene espacios con detalle completo incluyendo propietario del vehículo
     */
    public function obtenerEspaciosDetalle(int $idParqueadero): array {
        try {
            if (!$this->conexion) return [];

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
            file_put_contents($this->debugPath, "ERROR en obtenerEspaciosDetalle: " . $e->getMessage() . "\n", FILE_APPEND);
            return [];
        }
    }

    /**
     * Obtiene un vehículo activo por su placa
     */
    public function obtenerVehiculoPorPlaca(string $placa): ?array {
        try {
            if (!$this->conexion) return null;

            file_put_contents($this->debugPath, "=== MODELO: obtenerVehiculoPorPlaca — Placa: $placa ===\n", FILE_APPEND);

            $sql  = "SELECT v.*, f.NombreFuncionario, vis.NombreVisitante
                     FROM vehiculo v
                     LEFT JOIN funcionario f   ON v.IdFuncionario = f.IdFuncionario
                     LEFT JOIN visitante   vis ON v.IdVisitante   = vis.IdVisitante
                     WHERE v.PlacaVehiculo = :placa AND v.Estado = 'Activo'
                     LIMIT 1";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':placa' => strtoupper(trim($placa))]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($resultado) {
                file_put_contents($this->debugPath, "Vehículo encontrado: ID={$resultado['IdVehiculo']} Tipo={$resultado['TipoVehiculo']}\n", FILE_APPEND);
            } else {
                file_put_contents($this->debugPath, "Vehículo con placa '$placa' NO encontrado o inactivo\n", FILE_APPEND);
            }
            return $resultado ?: null;

        } catch (PDOException $e) {
            file_put_contents($this->debugPath, "ERROR en obtenerVehiculoPorPlaca: " . $e->getMessage() . "\n", FILE_APPEND);
            return null;
        }
    }

    /**
     * Verifica si el vehículo ya tiene un espacio ocupado en el parqueadero
     */
    public function obtenerEspacioOcupadoPorVehiculo(int $idVehiculo, int $idParqueadero): ?array {
        try {
            if (!$this->conexion) return null;

            $sql  = "SELECT * FROM espacio_parqueadero
                     WHERE IdVehiculo = :idv AND IdParqueadero = :idp AND Estado = 'Ocupado'
                     LIMIT 1";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':idv' => $idVehiculo, ':idp' => $idParqueadero]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

        } catch (PDOException $e) {
            file_put_contents($this->debugPath, "ERROR en obtenerEspacioOcupadoPorVehiculo: " . $e->getMessage() . "\n", FILE_APPEND);
            return null;
        }
    }

    /**
     * Obtiene el primer espacio libre del tipo del vehículo
     */
    public function obtenerPrimerEspacioLibre(int $idParqueadero, string $tipo): ?array {
        try {
            if (!$this->conexion) return null;

            $sql  = "SELECT * FROM espacio_parqueadero
                     WHERE IdParqueadero = :idp AND TipoVehiculo = :tipo AND Estado = 'Libre'
                     ORDER BY NumeroEspacio ASC
                     LIMIT 1";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':idp' => $idParqueadero, ':tipo' => $tipo]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($resultado) {
                file_put_contents($this->debugPath, "Primer espacio libre de $tipo: #{$resultado['NumeroEspacio']}\n", FILE_APPEND);
            } else {
                file_put_contents($this->debugPath, "Sin espacios libres de tipo $tipo en parqueadero $idParqueadero\n", FILE_APPEND);
            }
            return $resultado ?: null;

        } catch (PDOException $e) {
            file_put_contents($this->debugPath, "ERROR en obtenerPrimerEspacioLibre: " . $e->getMessage() . "\n", FILE_APPEND);
            return null;
        }
    }

    /**
     * Ocupa un espacio (entrada del vehículo — manual o por escáner)
     */
    public function ocuparEspacio(int $idEspacio, int $idVehiculo): array {
        try {
            file_put_contents($this->debugPath,
                "=== MODELO: ocuparEspacio — IdEspacio: $idEspacio, IdVehiculo: $idVehiculo ===\n",
                FILE_APPEND);

            if (!$this->conexion) {
                return ['success' => false, 'error' => 'Conexión no disponible'];
            }

            $sql  = "UPDATE espacio_parqueadero
                     SET Estado = 'Ocupado', IdVehiculo = :idv
                     WHERE IdEspacio = :ide AND Estado = 'Libre'";
            $stmt = $this->conexion->prepare($sql);
            $resultado = $stmt->execute([':idv' => $idVehiculo, ':ide' => $idEspacio]);

            if (!$resultado) {
                $errorInfo = $stmt->errorInfo();
                file_put_contents($this->debugPath, "Error SQL ocuparEspacio: " . json_encode($errorInfo) . "\n", FILE_APPEND);
                return ['success' => false, 'error' => $errorInfo[2] ?? 'Error desconocido'];
            }

            if ($stmt->rowCount() === 0) {
                file_put_contents($this->debugPath, "Espacio $idEspacio ya estaba ocupado (rowCount=0)\n", FILE_APPEND);
                return ['success' => false, 'error' => 'El espacio ya fue ocupado por otro vehículo'];
            }

            file_put_contents($this->debugPath, "Espacio $idEspacio ocupado correctamente por vehículo $idVehiculo\n", FILE_APPEND);
            return ['success' => true, 'rows' => $stmt->rowCount()];

        } catch (PDOException $e) {
            file_put_contents($this->debugPath, "EXCEPCIÓN PDO ocuparEspacio: " . $e->getMessage() . "\n", FILE_APPEND);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Libera un espacio (salida del vehículo — manual o por escáner)
     */
    public function liberarEspacio(int $idEspacio): array {
        try {
            file_put_contents($this->debugPath,
                "=== MODELO: liberarEspacio — IdEspacio: $idEspacio ===\n",
                FILE_APPEND);

            if (!$this->conexion) {
                return ['success' => false, 'error' => 'Conexión no disponible'];
            }

            $sql  = "UPDATE espacio_parqueadero
                     SET Estado = 'Libre', IdVehiculo = NULL
                     WHERE IdEspacio = :ide AND Estado = 'Ocupado'";
            $stmt = $this->conexion->prepare($sql);
            $resultado = $stmt->execute([':ide' => $idEspacio]);

            if (!$resultado) {
                $errorInfo = $stmt->errorInfo();
                file_put_contents($this->debugPath, "Error SQL liberarEspacio: " . json_encode($errorInfo) . "\n", FILE_APPEND);
                return ['success' => false, 'error' => $errorInfo[2] ?? 'Error desconocido'];
            }

            if ($stmt->rowCount() === 0) {
                file_put_contents($this->debugPath, "Espacio $idEspacio ya estaba libre (rowCount=0)\n", FILE_APPEND);
                return ['success' => false, 'error' => 'El espacio ya estaba libre'];
            }

            file_put_contents($this->debugPath, "Espacio $idEspacio liberado correctamente\n", FILE_APPEND);
            return ['success' => true, 'rows' => $stmt->rowCount()];

        } catch (PDOException $e) {
            file_put_contents($this->debugPath, "EXCEPCIÓN PDO liberarEspacio: " . $e->getMessage() . "\n", FILE_APPEND);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    // MÉTODO PARA EL ESCÁNER (integración con módulo del compañero)
    // ══════════════════════════════════════════════════════════════════════════
    /**
     * CÓMO CONECTAR EL ESCÁNER:
     * El módulo escáner debe enviar POST a ControladorParqueadero.php con:
     *   accion      => 'escanear_qr'
     *   placa       => 'ABC123'       ← placa leída del QR
     *   id_sede     => 3              ← sede donde está el lector
     *   tipo_evento => 'entrada'      ← o 'salida'
     *
     * NOTA: Si el QR guarda IdVehiculo en lugar de placa, reemplazar
     *       obtenerVehiculoPorPlaca() por una consulta por IdVehiculo.
     */
    public function procesarEscaneo(string $placa, int $idSede, string $tipoEvento): array {
        try {
            file_put_contents($this->debugPath,
                "=== MODELO: procesarEscaneo — Placa: $placa, Sede: $idSede, Evento: $tipoEvento ===\n",
                FILE_APPEND);

            if (!$this->conexion) {
                return ['success' => false, 'mensaje' => 'Conexión no disponible'];
            }

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

            file_put_contents($this->debugPath,
                "Vehículo: ID=$idVehiculo Tipo=$tipoVehiculo — Parqueadero: ID=$idParqueadero\n",
                FILE_APPEND);

            if ($tipoEvento === 'entrada') {
                $espacioActual = $this->obtenerEspacioOcupadoPorVehiculo($idVehiculo, $idParqueadero);
                if ($espacioActual) {
                    return ['success' => false, 'mensaje' => "El vehículo '$placa' ya ocupa el espacio #{$espacioActual['NumeroEspacio']}"];
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
                    file_put_contents($this->debugPath, "Entrada procesada: $placa → espacio #{$espacioLibre['NumeroEspacio']}\n", FILE_APPEND);
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
                    file_put_contents($this->debugPath, "Salida procesada: $placa ← espacio #{$espacioOcupado['NumeroEspacio']} liberado\n", FILE_APPEND);
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
                file_put_contents($this->debugPath, "Tipo de evento inválido: $tipoEvento\n", FILE_APPEND);
                return ['success' => false, 'mensaje' => "Tipo de evento inválido: '$tipoEvento'"];
            }

        } catch (Exception $e) {
            file_put_contents($this->debugPath, "EXCEPCIÓN procesarEscaneo: " . $e->getMessage() . "\n", FILE_APPEND);
            return ['success' => false, 'mensaje' => 'Error interno: ' . $e->getMessage()];
        }
    }
}
?>