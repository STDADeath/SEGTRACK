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
     * Retorna también el espacio asignado al vehículo
     */
    public function buscarVehiculoPorQr($qrCodigo) {

        if (preg_match('/ID:\s*(\d+)/i', $qrCodigo, $match)) {
            $id = $match[1];
        } else {
            return false;
        }

        // JOIN con espacio para obtener el número de espacio asignado al vehículo
        $sql = "SELECT 
                    v.IdVehiculo,
                    v.TipoVehiculo,
                    v.PlacaVehiculo,
                    v.DescripcionVehiculo,
                    v.TarjetaPropiedad AS DuenoVehiculo,
                    v.QrVehiculo,
                    v.IdSede,
                    e.IdEspacio,
                    e.NumeroEspacio,
                    e.Estado AS EstadoEspacio
                FROM vehiculo v
                LEFT JOIN espacio e ON e.IdVehiculo = v.IdVehiculo
                WHERE v.IdVehiculo = ?
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Registrar entrada o salida en la tabla ingreso
     */
    public function registrarIngreso($idVehiculo, $idSede, $tipoMovimiento = 'Entrada') {

        $sql = "INSERT INTO ingreso (TipoMovimiento, FechaIngreso, IdSede, IdVehiculo)
                VALUES (?, NOW(), ?, ?)";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$tipoMovimiento, $idSede, $idVehiculo]);
    }

    /**
     * Lista los ingresos de vehículos con dueño, placa, tipo y número de espacio
     */
    public function listarIngresos() {

        // AJUSTA los nombres de tabla si difieren en tu BD:
        // - Tabla de vehículos: "vehiculo"  (columnas: IdVehiculo, PlacaVehiculo, TipoVehiculo, TarjetaPropiedad, QrVehiculo, DescripcionVehiculo, IdSede)
        // - Tabla de espacios:  "espacio"   (columnas: IdEspacio, NumeroEspacio, TipoVehiculo, Estado, IdParqueadero, IdVehiculo)
        // - Tabla de ingresos:  "ingreso"   (columnas: IdIngreso, TipoMovimiento, FechaIngreso, IdSede, IdVehiculo)

        $sql = "SELECT 
                    i.IdIngreso,
                    i.TipoMovimiento,
                    i.FechaIngreso,
                    v.QrVehiculo,
                    v.PlacaVehiculo,
                    v.TipoVehiculo,
                    v.DescripcionVehiculo,
                    v.TarjetaPropiedad   AS DuenoVehiculo,
                    e.NumeroEspacio
                FROM ingreso i
                INNER JOIN vehiculo v ON i.IdVehiculo = v.IdVehiculo
                LEFT  JOIN espacio  e ON e.IdVehiculo = v.IdVehiculo
                ORDER BY i.IdIngreso DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>