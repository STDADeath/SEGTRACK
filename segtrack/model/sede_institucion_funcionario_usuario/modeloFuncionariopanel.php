<?php
require_once __DIR__ . '/../../Core/conexion.php';

class modeloFuncionariopanel {
    private $conexion;

    public function __construct() {
        $conexionObj = new Conexion();
        $this->conexion = $conexionObj->getConexion();
    }

    // Genera un código QR único
    private function generarQR() {
        return uniqid('QR_');
    }

    public function insertarFuncionario($datos) {
        try {
            $qr = $this->generarQR(); // QR automático

            $sql = "INSERT INTO funcionario 
                (NombreFuncionario, DocumentoFuncionario, TelefonoFuncionario, CorreoFuncionario, CargoFuncionario, IdSede, QrCodigoFuncionario)
                VALUES (:NombreFuncionario, :DocumentoFuncionario, :TelefonoFuncionario, :CorreoFuncionario, :CargoFuncionario, :IdSede, :QrCodigoFuncionario)";

            $stmt = $this->conexion->prepare($sql);

            $stmt->bindParam(':NombreFuncionario', $datos['NombreFuncionario']);
            $stmt->bindParam(':DocumentoFuncionario', $datos['DocumentoFuncionario']);
            $stmt->bindParam(':TelefonoFuncionario', $datos['TelefonoFuncionario']);
            $stmt->bindParam(':CorreoFuncionario', $datos['CorreoFuncionario']);
            $stmt->bindParam(':CargoFuncionario', $datos['CargoFuncionario']);
            $stmt->bindParam(':IdSede', $datos['IdSede']);
            $stmt->bindParam(':QrCodigoFuncionario', $qr);

            $stmt->execute();

            return ['error' => false, 'mensaje' => '✅ Funcionario registrado correctamente.'];
        } catch (PDOException $e) {
            return ['error' => true, 'mensaje' => '❌ Error al registrar: ' . $e->getMessage()];
        }
    }
}
?>
