<?php
class BitacoraModelo {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    public function insertar(array $datos): array {
        try {

            if (!$this->conexion) {
                return ['success' => false, 'error' => 'Conexión a la base de datos no disponible'];
            }

            $sql = "INSERT INTO bitacora 
                    (TurnoBitacora, NovedadesBitacora, FechaBitacora, IdFuncionario, IdIngreso, IdDispositivo, IdVisitante)
                    VALUES (:turno, :novedades, :fecha, :funcionario, :ingreso, :dispositivo, :visitante)";

            $stmt = $this->conexion->prepare($sql);
            
            $resultado = $stmt->execute([
                ':turno'       => $datos['TurnoBitacora'],
                ':novedades'   => $datos['NovedadesBitacora'],
                ':fecha'       => $datos['FechaBitacora'], 
                ':funcionario' => $datos['IdFuncionario'],
                ':ingreso'     => $datos['IdIngreso'],
                ':dispositivo' => $datos['IdDispositivo'] ?? null, 
                ':visitante'   => $datos['IdVisitante'] ?? null,   
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
            $sql = "SELECT * FROM bitacora ORDER BY FechaBitacora DESC";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function obtenerPorId(int $IdBitacora): ?array {
        try {
            $sql = "SELECT * FROM bitacora WHERE IdBitacora = ?";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([$IdBitacora]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

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
                'rows' => $stmt->rowCount()
            ];
            
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
?>