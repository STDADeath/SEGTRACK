<?php
class VisitanteModelo {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    // ══════════════════════════════════════════════
    // INSERTAR
    // ══════════════════════════════════════════════
    public function insertar(array $datos): array {
        try {
            if (!$this->conexion) return ['success' => false, 'error' => 'Conexión no disponible'];

            $sql = "INSERT INTO visitante (IdentificacionVisitante, NombreVisitante, CorreoVisitante, Estado)
                    VALUES (:identificacion, :nombre, :correo, 'Activo')";
            $stmt = $this->conexion->prepare($sql);
            $resultado = $stmt->execute([
                ':identificacion' => $datos['IdentificacionVisitante'],
                ':nombre'         => $datos['NombreVisitante'],
                ':correo'         => $datos['CorreoVisitante'] ?? null,
            ]);

            return $resultado
                ? ['success' => true,  'id'    => $this->conexion->lastInsertId()]
                : ['success' => false, 'error' => $stmt->errorInfo()[2] ?? 'Error desconocido'];

        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ══════════════════════════════════════════════
    // OBTENER TODOS (con filtros)
    // ══════════════════════════════════════════════
    public function obtenerTodos(array $filtros = [], array $params = []): array {
        try {
            $where = count($filtros) > 0 ? "WHERE " . implode(" AND ", $filtros) : "";
            $sql   = "SELECT * FROM visitante $where ORDER BY IdVisitante DESC";
            $stmt  = $this->conexion->prepare($sql);
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
            $stmt = $this->conexion->prepare("SELECT * FROM visitante WHERE IdVisitante = ?");
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
            $sql = "UPDATE visitante SET
                        IdentificacionVisitante = ?,
                        NombreVisitante         = ?,
                        CorreoVisitante         = ?
                    WHERE IdVisitante = ?";
            $stmt      = $this->conexion->prepare($sql);
            $resultado = $stmt->execute([
                $datos['IdentificacionVisitante'],
                $datos['NombreVisitante'],
                $datos['CorreoVisitante'] ?? null,
                $id,
            ]);
            return ['success' => $resultado, 'rows' => $stmt->rowCount()];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
?>