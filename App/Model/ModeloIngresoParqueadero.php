<?php

class ModeloIngresoParqueadero {
    private $pdo;

    public function __construct() {
        require_once __DIR__ . '/../Core/conexion.php';
        $conexionObj = new Conexion();
        $this->pdo   = $conexionObj->getConexion();

        if (!$this->pdo) {
            die("ERROR: La conexión no se inicializó correctamente.");
        }
    }

    // Busca un vehículo usando el contenido del código QR.
    // El QR trae un formato como:
    //   VEHÍCULO
    //   Placa: ABC123
    //   Tipo: Carro
    //   Descripción: Toyota Corolla
    //   Fecha: 2024-01-15 10:30:00

    public function buscarVehiculoPorQr($qrCodigo) {

        if (preg_match('/Placa:\s*(.+)/i', $qrCodigo, $match)) {
            $placa = strtoupper(trim($match[1]));
        } else {
            return false;
        }

        $sql = "SELECT
                    v.*,
                    COALESCE(f.NombreFuncionario, vis.NombreVisitante, v.TarjetaPropiedad) AS DuenoVehiculo,
                    f.IdFuncionario AS IdFuncionarioReal,
                    p.IdParqueadero,
                    ep.NumeroEspacio
                FROM vehiculo v
                LEFT JOIN funcionario f   ON v.IdFuncionario = f.IdFuncionario
                LEFT JOIN visitante   vis ON v.IdVisitante   = vis.IdVisitante
                LEFT JOIN parqueadero p   ON p.IdSede = v.IdSede AND p.Estado = 'Activo'
                LEFT JOIN espacio_parqueadero ep
                       ON ep.IdVehiculo = v.IdVehiculo AND ep.Estado = 'Ocupado'
                WHERE v.PlacaVehiculo = ?
                  AND v.Estado = 'Activo'
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$placa]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Registra un ingreso o salida del vehículo en la base de datos
    // Retorna true si se insertó correctamente, false en caso contrario

    public function registrarIngreso($idVehiculo, $idFuncionario = null, $idSede = null, $idParqueadero = null, $tipoMovimiento = 'Entrada') {

        $sql = "INSERT INTO ingreso (TipoMovimiento, FechaIngreso, Estado, IdSede, IdVehiculo, IdParqueadero, IdFuncionario)
                VALUES (?, NOW(), 'Activo', ?, ?, ?, ?)";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            $tipoMovimiento,
            $idSede,
            $idVehiculo,
            $idParqueadero,
            $idFuncionario
        ]);
    }

    // Lista todos los movimientos de vehículos registrados.
    // Se utiliza JOIN para unir ingreso con vehiculo, funcionario/visitante y espacio.

    public function listarIngresos() {

        $sql = "SELECT
                    i.IdIngreso,
                    v.QrVehiculo,
                    v.PlacaVehiculo,
                    v.TipoVehiculo,
                    v.DescripcionVehiculo,
                    COALESCE(f.NombreFuncionario, vis.NombreVisitante, v.TarjetaPropiedad) AS DuenoVehiculo,
                    ep.NumeroEspacio,
                    i.TipoMovimiento,
                    i.FechaIngreso
                FROM ingreso i
                INNER JOIN vehiculo v   ON i.IdVehiculo    = v.IdVehiculo
                LEFT  JOIN funcionario f   ON v.IdFuncionario = f.IdFuncionario
                LEFT  JOIN visitante   vis ON v.IdVisitante   = vis.IdVisitante
                LEFT  JOIN espacio_parqueadero ep
                        ON ep.IdVehiculo = v.IdVehiculo AND ep.Estado = 'Ocupado'
                WHERE i.IdVehiculo IS NOT NULL
                ORDER BY i.IdIngreso DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>