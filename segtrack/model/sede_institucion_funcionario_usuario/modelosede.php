<?php
require_once __DIR__ . '/../../Core/conexion.php';

class ModeloInstitucion {
    private $conexion;

    public function __construct() {
        $this->conexion = (new Conexion())->getConexion();
    }

    public function listar() {
        try {
            $sql = "SELECT IdInstitucion, NombreInstitucion FROM institucion ORDER BY NombreInstitucion ASC";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}
?>
