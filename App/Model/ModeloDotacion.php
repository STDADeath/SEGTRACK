<?php
class DotacionModelo {

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

            $sql = "INSERT INTO dotacion
                        (EstadoDotacion, TipoDotacion, NovedadDotacion,
                         FechaDevolucion, FechaEntrega, Estado, IdFuncionario)
                    VALUES
                        (:estado, :tipo, :novedad,
                         :fechaDevolucion, :fechaEntrega, 'Activo', :funcionario)";

            $stmt      = $this->conexion->prepare($sql);
            $resultado = $stmt->execute([
                ':estado'          => $datos['EstadoDotacion'],
                ':tipo'            => $datos['TipoDotacion'],
                ':novedad'         => $datos['NovedadDotacion'] ?? null,
                ':fechaDevolucion' => $datos['FechaDevolucion'] ?? null,
                ':fechaEntrega'    => $datos['FechaEntrega'],
                ':funcionario'     => $datos['IdFuncionario'],
            ]);

            return $resultado
                ? ['success' => true,  'id'    => $this->conexion->lastInsertId()]
                : ['success' => false, 'error' => $stmt->errorInfo()[2] ?? 'Error desconocido'];

        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ══════════════════════════════════════════════
    // OBTENER TODAS (JOIN con funcionario)
    // ══════════════════════════════════════════════
    public function obtenerTodos(array $filtros = [], array $params = []): array {
        try {
            $where = count($filtros) > 0 ? "WHERE " . implode(" AND ", $filtros) : "";

            $sql = "SELECT d.*,
                           f.NombreFuncionario
                    FROM   dotacion d
                    LEFT JOIN funcionario f ON f.IdFuncionario = d.IdFuncionario
                    $where
                    ORDER  BY d.IdDotacion DESC";

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
            $sql = "SELECT d.*,
                           f.NombreFuncionario
                    FROM   dotacion d
                    LEFT JOIN funcionario f ON f.IdFuncionario = d.IdFuncionario
                    WHERE  d.IdDotacion = ?";

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
            $sql = "UPDATE dotacion SET
                        EstadoDotacion  = ?,
                        TipoDotacion    = ?,
                        NovedadDotacion = ?,
                        FechaDevolucion = ?,
                        FechaEntrega    = ?,
                        IdFuncionario   = ?
                    WHERE IdDotacion = ?";

            $stmt      = $this->conexion->prepare($sql);
            $resultado = $stmt->execute([
                $datos['EstadoDotacion'],
                $datos['TipoDotacion'],
                $datos['NovedadDotacion'] ?? null,
                $datos['FechaDevolucion'] ?? null,
                $datos['FechaEntrega'],
                $datos['IdFuncionario'],
                $id,
            ]);

            return ['success' => $resultado, 'rows' => $stmt->rowCount()];

        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ══════════════════════════════════════════════
    // ELIMINAR
    // ══════════════════════════════════════════════
    public function eliminar(int $id): array {
        try {
            $sql       = "DELETE FROM dotacion WHERE IdDotacion = ?";
            $stmt      = $this->conexion->prepare($sql);
            $resultado = $stmt->execute([$id]);
            return ['success' => $resultado, 'rows' => $stmt->rowCount()];

        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ══════════════════════════════════════════════
    // PERSONAL DE SEGURIDAD ACTIVO (dropdown)
    // ══════════════════════════════════════════════
    public function obtenerFuncionarios(): array {
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
    // CAMBIAR ESTADO
    // ══════════════════════════════════════════════
    public function cambiarEstado(int $id, string $estado): array {
        try {
            $stmt      = $this->conexion->prepare("UPDATE dotacion SET Estado = ? WHERE IdDotacion = ?");
            $resultado = $stmt->execute([$estado, $id]);
            return [
                'success' => $resultado,
                'message' => $resultado ? "Dotación $estado correctamente" : 'No se pudo cambiar el estado',
                'rows'    => $stmt->rowCount()
            ];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
?>