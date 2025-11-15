<?php
class DotacionModelo {
    private $conexion;

public function __construct($conexion) {
        $this->conexion = $conexion;
    }



    public function insertar(array $datos): array {
        try {
            if (!$this->conexion) {
                return ['success' => false, 'error' => 'Conexión a la base de datos no disponible'];
            }

            $sql = "INSERT INTO dotacion 
                    (EstadoDotacion, TipoDotacion, NovedadDotacion, FechaDevolucion, FechaEntrega, IdFuncionario)
                    VALUES (:estado, :tipo, :novedad, :fechaDevolucion, :fechaEntrega, :funcionario)";

            $stmt = $this->conexion->prepare($sql);

            $resultado = $stmt->execute([
                ':estado'          => $datos['EstadoDotacion'],
                ':tipo'            => $datos['TipoDotacion'],
                ':novedad'         => $datos['NovedadDotacion'] ?? null,
                ':fechaDevolucion' => $datos['FechaDevolucion'] ?? null,
                ':fechaEntrega'    => $datos['FechaEntrega'] ?? null,
                ':funcionario'     => $datos['IdFuncionario']
            ]);

            if ($resultado) {
                return ['success' => true, 'id' => $this->conexion->lastInsertId()];
            } else {
                $errorInfo = $stmt->errorInfo();
                return ['success' => false, 'error' => $errorInfo[2] ?? 'Error desconocido al insertar'];
            }

        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }


    public function obtenerTodos(): array {
        try {
            $sql = "SELECT * FROM dotacion ORDER BY FechaEntrega DESC";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }


    public function obtenerPorId(int $IdDotacion): ?array {
        try {
            $sql = "SELECT * FROM dotacion WHERE IdDotacion = ?";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([$IdDotacion]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }


    public function actualizar(int $IdDotacion, array $datos): array {
        try {
            $sql = "UPDATE dotacion SET 
                        EstadoDotacion = ?, 
                        TipoDotacion = ?, 
                        NovedadDotacion = ?, 
                        FechaDevolucion = ?, 
                        FechaEntrega = ?, 
                        IdFuncionario = ?
                    WHERE IdDotacion = ?";

            $stmt = $this->conexion->prepare($sql);

            $resultado = $stmt->execute([
                $datos['EstadoDotacion'],
                $datos['TipoDotacion'],
                $datos['NovedadDotacion'] ?? null,
                $datos['FechaDevolucion'] ?? null,
                $datos['FechaEntrega'] ?? null,
                $datos['IdFuncionario'],
                $IdDotacion
            ]);

            return [
                'success' => $resultado,
                'rows' => $stmt->rowCount()
            ];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }


    public function eliminar(int $IdDotacion): array {
        try {
            $sql = "DELETE FROM dotacion WHERE IdDotacion = ?";
            $stmt = $this->conexion->prepare($sql);
            $resultado = $stmt->execute([$IdDotacion]);
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
