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

    public function buscarVehiculoPorQr($qrCodigo) {

        if (preg_match('/Placa:\s*(.+)/i', $qrCodigo, $match)) {
            $placa = strtoupper(trim($match[1]));
        } else {
            return false;
        }

        $sql = "SELECT
                    v.IdVehiculo,
                    v.TipoVehiculo,
                    v.PlacaVehiculo,
                    v.DescripcionVehiculo,
                    v.IdSede,
                    COALESCE(f.NombreFuncionario, vis.NombreVisitante, v.TarjetaPropiedad) AS DuenoVehiculo,
                    f.IdFuncionario  AS IdFuncionarioReal,
                    f.FotoFuncionario,
                    (SELECT p.IdParqueadero FROM parqueadero p
                     WHERE p.IdSede = v.IdSede AND p.Estado = 'Activo'
                     LIMIT 1) AS IdParqueadero,
                    ep.NumeroEspacio
                FROM vehiculo v
                LEFT JOIN funcionario f   ON v.IdFuncionario = f.IdFuncionario
                LEFT JOIN visitante   vis ON v.IdVisitante   = vis.IdVisitante
                LEFT JOIN espacio_parqueadero ep
                       ON ep.IdVehiculo = v.IdVehiculo AND ep.Estado = 'Ocupado'
                WHERE v.PlacaVehiculo = ?
                  AND v.Estado = 'Activo'
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$placa]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function registrarIngreso(
        $idVehiculo,
        $idFuncionario  = null,
        $idSede         = null,
        $idParqueadero  = null,
        $tipoMovimiento = 'Entrada'
    ) {
        $sql = "INSERT INTO ingreso
                    (TipoMovimiento, FechaIngreso, Estado, IdSede, IdVehiculo, IdParqueadero, IdFuncionario)
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
                INNER JOIN vehiculo      v   ON i.IdVehiculo    = v.IdVehiculo
                LEFT  JOIN funcionario   f   ON v.IdFuncionario = f.IdFuncionario
                LEFT  JOIN visitante     vis ON v.IdVisitante   = vis.IdVisitante
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