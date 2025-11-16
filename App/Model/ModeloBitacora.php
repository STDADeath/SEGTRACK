<?php
class BitacoraModelo {
    private $conexion;

    public function __construct($conexion) {
        // Guarda la conexión a la base de datos para usarla en todas las consultas
        $this->conexion = $conexion;
    }   

 
    // INSERTAR UNA NUEVA BITÁCORA
 
    public function insertar(array $datos): array {
        try {

            // Verifica si existe la conexión
            if (!$this->conexion) {
                return ['success' => false, 'error' => 'Conexión a la base de datos no disponible'];
            }

            // Consulta SQL parametrizada (segura contra SQL Injection)
            $sql = "INSERT INTO bitacora 
                    (TurnoBitacora, NovedadesBitacora, FechaBitacora, IdFuncionario, IdIngreso, IdDispositivo, IdVisitante)
                    VALUES (:turno, :novedades, :fecha, :funcionario, :ingreso, :dispositivo, :visitante)";

            $stmt = $this->conexion->prepare($sql);
            
            // Se envían los parámetros del registro
            $resultado = $stmt->execute([
                ':turno'       => $datos['TurnoBitacora'],
                ':novedades'   => $datos['NovedadesBitacora'],
                ':fecha'       => $datos['FechaBitacora'], 
                ':funcionario' => $datos['IdFuncionario'],
                ':ingreso'     => $datos['IdIngreso'],
                ':dispositivo' => $datos['IdDispositivo'] ?? null,  // Puede ser null
                ':visitante'   => $datos['IdVisitante'] ?? null,    // Puede ser null
            ]);

            // Si todo sale bien, devuelve el ID del registro insertado
            if ($resultado) {
                return ['success' => true, 'id' => $this->conexion->lastInsertId()];
            } else {
                // Si falla, obtiene detalles del error de PDO
                $errorInfo = $stmt->errorInfo();
                return ['success' => false, 'error' => $errorInfo[2] ?? 'Error desconocido al insertar'];
            }
            
        } catch (PDOException $e) {
            // Manejo de errores por excepción
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    

    //  OBTENER TODAS LAS BITÁCORAS
 

    public function obtenerTodos(): array {
        try {
            $sql = "SELECT * FROM bitacora ORDER BY FechaBitacora DESC";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();

            // Retorna todas las filas como arreglo asociativo
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }


    // OBTENER UNA BITÁCORA POR ID
    //     Devuelve una sola fila o null si no existe

    public function obtenerPorId(int $IdBitacora): ?array {
        try {
            $sql = "SELECT * FROM bitacora WHERE IdBitacora = ?";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([$IdBitacora]);

            // Si no existe, retorna null
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

        } catch (PDOException $e) {
            return null;
        }
    }


    //  ACTUALIZAR UNA BITÁCORA EXISTENTE
    //    -Modifica los campos enviados al método

    public function actualizar(int $IdBitacora, array $datos): array {
        try {
            $sql = "UPDATE bitacora SET 
                        TurnoBitacora = ?, 
                        NovedadesBitacora = ?, 
                        FechaBitacora = ?,
                        IdFuncionario = ?, 
                        IdIngreso = ?,
                        IdDispositivo = ?,
                        IdVisitante = ?
                    WHERE IdBitacora = ?";

            $stmt = $this->conexion->prepare($sql);

            // Ejecuta la actualización enviando parámetros
            $resultado = $stmt->execute([
                $datos['TurnoBitacora'],
                $datos['NovedadesBitacora'],
                $datos['FechaBitacora'],
                $datos['IdFuncionario'],
                $datos['IdIngreso'],
                $datos['IdDispositivo'] ?? null,
                $datos['IdVisitante'] ?? null,
                $IdBitacora 
            ]);

            return [
                'success' => $resultado, 
                'rows' => $stmt->rowCount() // Cantidad de filas modificadas
            ];
            
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
?>
