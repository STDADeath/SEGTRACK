<?php
class BitacoraModelo {

    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    // ══════════════════════════════════════════════
    // INSERTAR
    // ══════════════════════════════════════════════
    public function insertar(array $datos): array {
        try {
            if (!$this->conexion) {
                return ['success' => false, 'error' => 'Conexión no disponible'];
            }

            $sql = "INSERT INTO bitacora
                        (TurnoBitacora, NovedadesBitacora, FechaBitacora,
                         ReporteBitacora, Estado,
                         IdFuncionario, IdIngreso, IdDispositivo, IdVisitante)
                    VALUES
                        (:turno, :novedades, :fecha,
                         :reporte, 'Activo',
                         :funcionario, :ingreso, :dispositivo, :visitante)";

            $stmt = $this->conexion->prepare($sql);

            $resultado = $stmt->execute([
                ':turno'       => $datos['TurnoBitacora'],
                ':novedades'   => $datos['NovedadesBitacora'],
                ':fecha'       => $datos['FechaBitacora'],
                ':reporte'     => $datos['ReporteBitacora'] ?? null,
                ':funcionario' => $datos['IdFuncionario'],
                ':ingreso'     => null,
                ':dispositivo' => $datos['IdDispositivo'] ?? null,
                ':visitante'   => $datos['IdVisitante']   ?? null,
            ]);

            return $resultado
                ? ['success' => true,  'id'    => $this->conexion->lastInsertId()]
                : ['success' => false, 'error' => $stmt->errorInfo()[2] ?? 'Error desconocido al insertar'];

        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ══════════════════════════════════════════════
    // OBTENER TODAS (con JOIN al funcionario)
    // ══════════════════════════════════════════════
    public function obtenerBitacoras(array $filtros = [], array $params = []): array {
        try {
            $where = count($filtros) > 0 ? "WHERE " . implode(" AND ", $filtros) : "";

            $sql = "SELECT b.*,
                           f.NombreFuncionario AS NombreCompleto
                    FROM   bitacora b
                    LEFT   JOIN funcionario f ON f.IdFuncionario = b.IdFuncionario
                    $where
                    ORDER  BY b.IdBitacora DESC";

            $stmt = $this->conexion->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            return [];
        }
    }

    // ══════════════════════════════════════════════
    // OBTENER POR ID
    // ══════════════════════════════════════════════
    public function obtenerPorId(int $id): ?array {
        try {
            $sql = "SELECT b.*,
                           f.NombreFuncionario AS NombreCompleto
                    FROM   bitacora b
                    LEFT   JOIN funcionario f ON f.IdFuncionario = b.IdFuncionario
                    WHERE  b.IdBitacora = ?";

            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

        } catch (PDOException $e) {
            return null;
        }
    }

    // ══════════════════════════════════════════════
    // ACTUALIZAR
    // ══════════════════════════════════════════════
    public function actualizar(int $id, array $datos): array {
        try {
            $setReporte = isset($datos['ReporteBitacora']) ? ", ReporteBitacora = ?" : "";

            $sql = "UPDATE bitacora SET
                        TurnoBitacora     = ?,
                        NovedadesBitacora = ?,
                        FechaBitacora     = ?,
                        IdFuncionario     = ?,
                        IdIngreso         = ?,
                        IdDispositivo     = ?,
                        IdVisitante       = ?
                        $setReporte
                    WHERE IdBitacora = ?";

            $params = [
                $datos['TurnoBitacora'],
                $datos['NovedadesBitacora'],
                $datos['FechaBitacora'],
                $datos['IdFuncionario'],
                null,
                $datos['IdDispositivo'] ?? null,
                $datos['IdVisitante']   ?? null,
            ];

            if (isset($datos['ReporteBitacora'])) {
                $params[] = $datos['ReporteBitacora'];
            }

            $params[] = $id;

            $stmt = $this->conexion->prepare($sql);
            $resultado = $stmt->execute($params);

            return ['success' => $resultado, 'rows' => $stmt->rowCount()];

        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ══════════════════════════════════════════════
    // PERSONAL DE SEGURIDAD (para el dropdown)
    // ══════════════════════════════════════════════
    public function obtenerPersonalSeguridad(): array {
        try {
            $sql = "SELECT   IdFuncionario,
                             NombreFuncionario AS NombreCompleto
                    FROM     funcionario
                    WHERE    CargoFuncionario = 'Personal Seguridad'
                      AND    Estado = 'Activo'
                    ORDER BY NombreFuncionario ASC";

            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            return [];
        }
    }

    // ══════════════════════════════════════════════
    // VISITANTES ACTIVOS (para el dropdown)
    // ══════════════════════════════════════════════
    public function obtenerVisitantes(): array {
        try {
            // ⚠️ Ajusta el nombre de columnas según tu tabla `visitante`
            $sql = "SELECT   IdVisitante,
                             NombreVisitante AS NombreCompleto
                    FROM     visitante
                    WHERE    Estado = 'Activo'
                    ORDER BY NombreVisitante ASC";

            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            return [];
        }
    }

    // ══════════════════════════════════════════════
    // DISPOSITIVOS ACTIVOS (para el dropdown)
    // Filtra por IdVisitante si se envía, para mostrar
    // solo los dispositivos del visitante seleccionado
    // ══════════════════════════════════════════════
    public function obtenerDispositivos(?int $idVisitante = null): array {
        try {
            if ($idVisitante) {
                $sql = "SELECT   IdDispositivo,
                                 CONCAT(TipoDispositivo, ' - ', MarcaDispositivo,
                                     IF(NumeroSerial IS NOT NULL, CONCAT(' (', NumeroSerial, ')'), '')
                                 ) AS NombreCompleto
                        FROM     dispositivo
                        WHERE    Estado = 'Activo'
                          AND    IdVisitante = :idVisitante
                        ORDER BY TipoDispositivo ASC";
                $stmt = $this->conexion->prepare($sql);
                $stmt->execute([':idVisitante' => $idVisitante]);
            } else {
                $sql = "SELECT   IdDispositivo,
                                 CONCAT(TipoDispositivo, ' - ', MarcaDispositivo,
                                     IF(NumeroSerial IS NOT NULL, CONCAT(' (', NumeroSerial, ')'), '')
                                 ) AS NombreCompleto
                        FROM     dispositivo
                        WHERE    Estado = 'Activo'
                        ORDER BY TipoDispositivo ASC";
                $stmt = $this->conexion->prepare($sql);
                $stmt->execute();
            }

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            return [];
        }
    }
}
?>