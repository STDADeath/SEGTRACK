<?php
require_once __DIR__ . '/../../Core/conexion.php';

class ModeloSede {
    private $conexion;

    public function __construct() {
        $this->conexion = (new Conexion())->getConexion();
    }

    public function insertar($tipoSede, $ciudad, $idInstitucion) {
        try {
            $sql = "INSERT INTO sede (TipoSede, Ciudad, IdInstitucion) VALUES (:tipoSede, :ciudad, :idInstitucion)";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([
                ':tipoSede' => $tipoSede,
                ':ciudad' => $ciudad,
                ':idInstitucion' => $idInstitucion
            ]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>
