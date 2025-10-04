<?php
class DispositivoModel {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    public function insertar($codigoQR, $tipo, $marca, $idFuncionario, $idVisitante) {
        try {
            $sql = "INSERT INTO dispositivos (QrDispositivo, TipoDispositivo, MarcaDispositivo, IdFuncionario, IdVisitante)
                    VALUES (:qr, :tipo, :marca, :funcionario, :visitante)";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':qr', $codigoQR);
            $stmt->bindParam(':tipo', $tipo);
            $stmt->bindParam(':marca', $marca);
            $stmt->bindParam(':funcionario', $idFuncionario);
            $stmt->bindParam(':visitante', $idVisitante);
            return $stmt->execute();
        } catch (PDOException $e) {
            return "Error al insertar: " . $e->getMessage();
        }
    }

    public function eliminar($id) {
        try {
            $sql = "DELETE FROM dispositivos WHERE IdDispositivo = :id";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            return "Error al eliminar: " . $e->getMessage();
        }
    }

    public function editar($id, $tipo, $marca, $idFuncionario, $idVisitante) {
        try {
            $sql = "UPDATE dispositivos
                    SET TipoDispositivo = :tipo, MarcaDispositivo = :marca, 
                        IdFuncionario = :funcionario, IdVisitante = :visitante
                    WHERE IdDispositivo = :id";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':tipo', $tipo);
            $stmt->bindParam(':marca', $marca);
            $stmt->bindParam(':funcionario', $idFuncionario);
            $stmt->bindParam(':visitante', $idVisitante);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->rowCount();
        } catch (PDOException $e) {
            return "Error al editar: " . $e->getMessage();
        }
    }
}
?>
