<?php

class ModeloParqueadero {
    private $pdo;

    public function __construct() {
        require_once __DIR__ . '/../Core/conexion.php';
        $conexionObj = new Conexion();
        $this->pdo = $conexionObj->getConexion();
    }

    /**
     * Buscar vehículo por su QR (formato: "ID: X")
     */
    public function buscarVehiculoPorQr($qrCodigo) {

        if (preg_match('/ID:\s*(\d+)/i', $qrCodigo, $match)) {
            $id = $match[1];
        } else {
            return false;
        }

        $sql = "SELECT * FROM parqueadero WHERE IdParqueadero = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Registrar entrada o salida en la tabla ingreso
     */
    public function registrarIngreso($idParqueadero, $idSede, $tipoMovimiento = 'Entrada') {

        $sql = "INSERT INTO ingreso (TipoMovimiento, FechaIngreso, IdSede, IdParqueadero)
                VALUES (?, NOW(), ?, ?)";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$tipoMovimiento, $idSede, $idParqueadero]);
    }

    /**
     * Lista los ingresos de vehículos
     */
    public function listarIngresos() {

        $sql = "SELECT i.IdIngreso, i.TipoMovimiento, i.FechaIngreso,
                p.PlacaVehiculo, p.TipoVehiculo, p.QrVehiculo, p.DescripcionVehiculo
                FROM ingreso i
                INNER JOIN parqueadero p ON i.IdParqueadero = p.IdParqueadero
                ORDER BY i.IdIngreso DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>
