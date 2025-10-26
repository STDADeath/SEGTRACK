<?php
require_once __DIR__ . '/../../Core/conexion.php';

class ModeloInstituto {
    private $conexion;

    public function __construct() {
        $conexionObj = new Conexion();
        $this->conexion = $conexionObj->getConexion();
    }

    public function generarNit() {
        try {
            $sql = "SELECT Nit_Codigo FROM institucion ORDER BY IdInstitucion DESC LIMIT 1";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            $ultimo = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($ultimo && !empty($ultimo['Nit_Codigo'])) {
                $partes = explode('-', $ultimo['Nit_Codigo']);
                $numero = isset($partes[1]) ? intval($partes[1]) + 1 : 1;
            } else {
                $numero = 1;
            }

            return "900123456-$numero";
        } catch (PDOException $e) {
            return "900123456-1";
        }
    }

    public function insertarInstituto($datos) {
        try {
            $sql = "INSERT INTO institucion 
                    (NombreInstitucion, Nit_Codigo, Tipolnstitucion, EstadoInstitucion)
                    VALUES (:NombreInstitucion, :Nit_Codigo, :Tipolnstitucion, :EstadoInstitucion)";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':NombreInstitucion', $datos['NombreInstitucion']);
            $stmt->bindParam(':Nit_Codigo', $datos['Nit_Codigo']); // Usar el NIT del controlador
            $stmt->bindParam(':Tipolnstitucion', $datos['TipoInstitucion']);
            $stmt->bindParam(':EstadoInstitucion', $datos['EstadoInstitucion']);
            $stmt->execute();

            return ['error' => false, 'mensaje' => "Institución registrada correctamente"];
        } catch (PDOException $e) {
            return ['error' => true, 'mensaje' => $e->getMessage()];
        }
    }

    public function listarInstitutos() {
        try {
            $sql = "SELECT * FROM institucion ORDER BY IdInstitucion DESC";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => true, 'mensaje' => $e->getMessage()];
        }
    }
}
?>