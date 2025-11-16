<?php
class DotacionModelo {

    private $conexion;

    // Constructor que recibe la conexión y la asigna a la propiedad de la clase
    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    // Método para insertar un nuevo registro en la base de datos
    public function insertar(array $datos): array {
        try {

            if (!$this->conexion) {
                return ['success' => false, 'error' => 'Conexión a la base de datos no disponible'];
            }

            // Sentencia SQL para insertar una nueva dotación
            $sql = "INSERT INTO dotacion 
                    (EstadoDotacion, TipoDotacion, NovedadDotacion, FechaDevolucion, FechaEntrega, IdFuncionario)
                    VALUES (:estado, :tipo, :novedad, :fechaDevolucion, :fechaEntrega, :funcionario)";

            // Preparar la consulta
            $stmt = $this->conexion->prepare($sql);

            // Ejecutar la consulta con los valores recibidos
            $resultado = $stmt->execute([
                ':estado'          => $datos['EstadoDotacion'],
                ':tipo'            => $datos['TipoDotacion'],
                ':novedad'         => $datos['NovedadDotacion'] ?? null,
                ':fechaDevolucion' => $datos['FechaDevolucion'] ?? null,
                ':fechaEntrega'    => $datos['FechaEntrega'] ?? null,
                ':funcionario'     => $datos['IdFuncionario']
            ]);

            // Si la ejecución fue exitosa devolver el id insertado
            if ($resultado) {
                return ['success' => true, 'id' => $this->conexion->lastInsertId()];
            } else {
                // Obtener información del error si falla la ejecución
                $errorInfo = $stmt->errorInfo();
                return ['success' => false, 'error' => $errorInfo[2] ?? 'Error desconocido al insertar'];
            }

        } catch (PDOException $e) {
            // Retornar error en caso de excepción
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // Método para obtener todos los registros de la tabla dotación
    public function obtenerTodos(): array {
        try {
            // Consulta SQL para obtener todas las dotaciones ordenadas por fecha de entrega
            $sql = "SELECT * FROM dotacion ORDER BY FechaEntrega DESC";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();

            // Retornar todos los registros como un arreglo asociativo
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // Método para obtener una dotación por su ID
    public function obtenerPorId(int $IdDotacion): ?array {
        try {
            // Consulta SQL con parámetro para buscar por ID
            $sql = "SELECT * FROM dotacion WHERE IdDotacion = ?";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([$IdDotacion]);

            // Retorna el registro encontrado o null si no existe
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

        } catch (PDOException $e) {
            // Retorna null si ocurre un error
            return null;
        }
    }

    // Método para actualizar un registro según el ID
    public function actualizar(int $IdDotacion, array $datos): array {
        try {
            // Consulta SQL para actualizar la dotación
            $sql = "UPDATE dotacion SET 
                        EstadoDotacion = ?, 
                        TipoDotacion = ?, 
                        NovedadDotacion = ?, 
                        FechaDevolucion = ?, 
                        FechaEntrega = ?, 
                        IdFuncionario = ?
                    WHERE IdDotacion = ?";

            // Preparar la consulta
            $stmt = $this->conexion->prepare($sql);

            // Ejecutar con los datos recibidos
            $resultado = $stmt->execute([
                $datos['EstadoDotacion'],
                $datos['TipoDotacion'],
                $datos['NovedadDotacion'] ?? null,
                $datos['FechaDevolucion'] ?? null,
                $datos['FechaEntrega'] ?? null,
                $datos['IdFuncionario'],
                $IdDotacion
            ]);

            // Retornar si tuvo éxito y cuántas filas fueron afectadas
            return [
                'success' => $resultado,
                'rows' => $stmt->rowCount()
            ];

        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // Método para eliminar un registro según el ID
    public function eliminar(int $IdDotacion): array {
        try {
            // Sentencia SQL para eliminar por ID
            $sql = "DELETE FROM dotacion WHERE IdDotacion = ?";
            $stmt = $this->conexion->prepare($sql);


            $resultado = $stmt->execute([$IdDotacion]);

            // Retornar si tuvo éxito y cuántas filas fueron eliminadas
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
