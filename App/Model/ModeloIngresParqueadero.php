<?php

class ModeloParqueadero {
    private $pdo;

    public function __construct() {
        require_once __DIR__ . '/../Core/conexion.php';
        $conexionObj = new Conexion();
        $this->pdo = $conexionObj->getConexion();

        if (!$this->pdo) {
            die("ERROR: La conexión no se inicializó correctamente.");
        }
    }

    // Busca un vehículo por el QR
    public function buscarVehiculoPorQr($qrCodigo) {
        $sql = "SELECT * FROM parqueadero WHERE QrVehiculo = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$qrCodigo]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Registra ingreso o salida en la tabla ingreso
    public function registrarIngreso($idParqueadero, $idSede, $tipoMovimiento = 'Entrada') {
        $sql = "INSERT INTO ingreso (TipoMovimiento, FechaIngreso, IdSede, IdParqueadero)
                VALUES (?, NOW(), ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$tipoMovimiento, $idSede, $idParqueadero]);
    }

    // Lista los ingresos de vehículos
    public function listarIngresos() {
        $sql = "SELECT i.IdIngreso, i.TipoMovimiento, i.FechaIngreso, 
                       p.DescripcionVehiculo, p.PlacaVehiculo, p.TipoVehiculo
                FROM ingreso i
                INNER JOIN parqueadero p ON i.IdParqueadero = p.IdParqueadero
                ORDER BY i.IdIngreso DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
