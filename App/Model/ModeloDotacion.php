<?php
class DotacionModelo {

    private $conexion;

    // Recibe la conexión a la base de datos
    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    // Inserta una nueva dotación en la BD
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

            // Si insertó correctamente, devolver ID
            if ($resultado) {
                return ['success' => true, 'id' => $this->conexion->lastInsertId()];
            }

            // Si falla: retornar error específico
            return ['success' => false, 'error' => ($stmt->errorInfo()[2] ?? 'Error desconocido al insertar')];

        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // Devuelve todas las dotaciones
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

    // Devuelve una dotación según su ID
    public function obtenerPorId(int $IdDotacion): ?array {
        try {
            $sql = "SELECT * FROM dotacion WHERE IdDotacion = ?";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([$IdDotacion]);

            // Retorna el registro o null si no existe
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

        } catch (PDOException $e) {
            return null;
        }
    }

    // Actualiza una dotación
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

            // rowCount indica cuántas filas fueron modificadas
            return [
                'success' => $resultado,
                'rows' => $stmt->rowCount()
            ];

        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // Elimina una dotación
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
