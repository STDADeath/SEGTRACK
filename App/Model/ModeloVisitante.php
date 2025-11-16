<?php
class VisitanteModelo {
    // Propiedad para almacenar la conexión a la base de datos
    private $conexion;


    public function __construct($conexion) {
        $this->conexion = $conexion;
    }


  ///Insertar un nuevo visitante en la base de datos

  ///Resultado del insert con success, id o error

    public function insertar(array $datos): array {
        try {
            // Verifica si la conexión está disponible
            if (!$this->conexion) {
                return ['success' => false, 'error' => 'Conexión no disponible'];
            }

            // Consulta SQL preparada para evitar inyecciones SQL
            $sql = "INSERT INTO visitante (IdentificacionVisitante, NombreVisitante)
                    VALUES (:identificacion, :nombre)";
            $stmt = $this->conexion->prepare($sql);

            // Ejecuta la consulta con los datos proporcionados
            $resultado = $stmt->execute([
                ':identificacion' => $datos['IdentificacionVisitante'],
                ':nombre'         => $datos['NombreVisitante']
            ]);

            // Si se insertó correctamente, retorna el ID generado
            if ($resultado) {
                return ['success' => true, 'id' => $this->conexion->lastInsertId()];
            } else {
                // Si hubo error, obtiene la información de error de PDO
                $errorInfo = $stmt->errorInfo();
                return ['success' => false, 'error' => $errorInfo[2] ?? 'Error desconocido'];
            }

        } catch (PDOException $e) {
            // Captura errores de PDO
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

     // Obtener todos los visitantes registrados

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


 ///    Obtener un visitante por su ID


    public function obtenerPorId(int $id): ?array {
        try {
            $sql = "SELECT * FROM visitante WHERE IdVisitante = ?";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            // Retorna null si hay error
            return null;
        }
    }


     ///  Actualizar los datos de un visitante 
    public function actualizar(int $id, array $datos): array {
        try {
            $sql = "UPDATE visitante 
                    SET IdentificacionVisitante = ?, NombreVisitante = ?
                    WHERE IdVisitante = ?";
            $stmt = $this->conexion->prepare($sql);

            // Ejecuta la consulta con los datos proporcionados
            $resultado = $stmt->execute([
                $datos['IdentificacionVisitante'],
                $datos['NombreVisitante'],
                $id
            ]);

            return [
                'success' => $resultado,
                'rows' => $stmt->rowCount() // Número de filas afectadas
            ];
        } catch (PDOException $e) {
            // Captura errores de PDO
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
?>
