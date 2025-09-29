<?php
class BitacoraModelo {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

public function insertar(array $datos): array {
    try {
        $sql = "INSERT INTO bitacora 
                (TurnoBitacora, NovedadesBitacora, FechaBitacora, IdFuncionario, IdIngreso, IdDispositivo, IdVisitante) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([
            $datos['TurnoBitacora'],
            $datos['NovedadesBitacora'],
            $datos['FechaBitacora'],
            $datos['IdFuncionario'],
            $datos['IdIngreso'],
            $datos['IdDispositivo'],
            $datos['IdVisitante']
        ]);

        // Obtener el ID generado automáticamente
        $NumeroIdBitacora = $this->conexion->lastInsertId();

        return ['success' => true, 'id' => $NumeroIdBitacora];
    } catch (PDOException $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}


    public function obtenerTodos(): array {
        $sql = "SELECT * FROM bitacora";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId(int $IdBitacora): ?array {
        $sql = "SELECT * FROM bitacora WHERE IdBitacora = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$IdBitacora]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
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
            $stmt->execute([
                $datos['TurnoBitacora'],
                $datos['NovedadesBitacora'],
                $datos['FechaBitacora'],
                $datos['IdFuncionario'],
                $datos['IdIngreso'],    
                $datos['IdDispositivo'],
                $datos['IdVisitante'],
                $IdBitacora
            ]);
            return ['success' => true, 'rows' => $stmt->rowCount()];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
