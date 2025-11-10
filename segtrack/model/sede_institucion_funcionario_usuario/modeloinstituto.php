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
            // Separar la parte numérica del NIT
            $partes = explode('-', $ultimo['Nit_Codigo']);
            $numero = isset($partes[1]) ? intval($partes[1]) + 1 : 1;
        } else {
            $numero = 1;
        }

        // De esta forma nos aseguramos que el NIT no se repita
        do {
            $nit = "900123456-$numero";
            $check = $this->conexion->prepare("SELECT COUNT(*) FROM institucion WHERE Nit_Codigo = :nit");
            $check->bindParam(':nit', $nit);
            $check->execute();
            $existe = $check->fetchColumn();
            $numero++;
        } while ($existe > 0);

        return $nit;

    } catch (PDOException $e) {
        return "900123456-1";
    }
}


    public function insertarInstituto($datos) {
        try {
            // Generar NIT si no viene desde el controlador
            if (!isset($datos['Nit_Codigo'])) {
                $datos['Nit_Codigo'] = $this->generarNit();
            }

            $sql = "INSERT INTO institucion 
                (NombreInstitucion, Nit_Codigo, TipoInstitucion, EstadoInstitucion)
                VALUES (:NombreInstitucion, :Nit_Codigo, :TipoInstitucion, :EstadoInstitucion)";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':NombreInstitucion', $datos['NombreInstitucion']);
            $stmt->bindParam(':Nit_Codigo', $datos['Nit_Codigo']); // ahora coincide
            $stmt->bindParam(':TipoInstitucion', $datos['TipoInstitucion']);
            $stmt->bindParam(':EstadoInstitucion', $datos['EstadoInstitucion']);
            $stmt->execute();

            return ['error' => false, 'mensaje' => "✅ Institución registrada correctamente. NIT: {$datos['Nit_Codigo']}"];
        } catch (PDOException $e) {
            return ['error' => true, 'mensaje' => '❌ Error al registrar: ' . $e->getMessage()];
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