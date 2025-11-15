<?php
class VisitanteModelo {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    // Insertar un nuevo visitante
    public function insertar(array $datos): array {
        try {
            if (!$this->conexion) {
                return ['success' => false, 'error' => 'Conexión no disponible'];
            }

            $sql = "INSERT INTO visitante (IdentificacionVisitante, NombreVisitante)
                    VALUES (:identificacion, :nombre)";
            $stmt = $this->conexion->prepare($sql);

            $resultado = $stmt->execute([
                ':identificacion' => $datos['IdentificacionVisitante'],
                ':nombre'         => $datos['NombreVisitante']
            ]);

            if ($resultado) {
                return ['success' => true, 'id' => $this->conexion->lastInsertId()];
            } else {
                $errorInfo = $stmt->errorInfo();
                return ['success' => false, 'error' => $errorInfo[2] ?? 'Error desconocido'];
            }

        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // Obtener todos los visitantes
    public function obtenerTodos(): array {
        try {
            $sql = "SELECT * FROM visitante ORDER BY IdVisitante DESC";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // Obtener visitante por ID
    public function obtenerPorId(int $id): ?array {
        try {
            $sql = "SELECT * FROM visitante WHERE IdVisitante = ?";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    // Actualizar visitante
    public function actualizar(int $id, array $datos): array {
        try {
            $sql = "UPDATE visitante 
                    SET IdentificacionVisitante = ?, NombreVisitante = ?
                    WHERE IdVisitante = ?";
            $stmt = $this->conexion->prepare($sql);

            $resultado = $stmt->execute([
                $datos['IdentificacionVisitante'],
                $datos['NombreVisitante'],
                $id
            ]);

            return [
                'success' => $resultado,
                'rows' => $stmt->rowCount()
            ];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
?>
