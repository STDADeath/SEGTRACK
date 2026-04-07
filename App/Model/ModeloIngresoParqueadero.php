<?php

class ModeloIngresoParqueadero {
    private $pdo;
    private $logPath;

    public function __construct() {
        require_once __DIR__ . '/../Core/conexion.php';
        $conexionObj = new Conexion();
        $this->pdo   = $conexionObj->getConexion();
        $this->logPath = __DIR__ . '/../Controller/Debug_Parqueadero/qr_debug.txt';

        if (!$this->pdo) {
            die(json_encode(['success' => false, 'message' => 'Conexión fallida']));
        }
    }

    private function log($msg) {
        $dir = dirname($this->logPath);
        if (!is_dir($dir)) mkdir($dir, 0777, true);
        
        file_put_contents(
            $this->logPath,
            date('Y-m-d H:i:s') . " - $msg\n",
            FILE_APPEND
        );
    }

    public function buscarVehiculoPorQr($qrCodigo) {

        $this->log("=== QR RAW HEX: " . bin2hex($qrCodigo));
        $this->log("=== QR TEXTO:   " . $qrCodigo);

        $qrNormalizado = trim(str_replace("\r", "", $qrCodigo));

        $idVehiculo = null;
        if (preg_match('/IdVehiculo:\s*(\d+)/i', $qrNormalizado, $match)) {
            $idVehiculo = intval($match[1]);
        }

        $this->log("=== ID EXTRAÍDO: " . $idVehiculo);

        if (!$idVehiculo || $idVehiculo <= 0) {
            $this->log("ERROR: ID inválido del QR");
            return ['encontrado' => false, 'inactivo' => false];
        }

        // PRIMERO: Verificar si el vehículo existe y su estado
        $stmtExiste = $this->pdo->prepare(
            "SELECT IdVehiculo, Estado, PlacaVehiculo, TipoVehiculo 
             FROM vehiculo 
             WHERE IdVehiculo = ? 
             LIMIT 1"
        );
        $stmtExiste->execute([$idVehiculo]);
        $existe = $stmtExiste->fetch(PDO::FETCH_ASSOC);

        $this->log("Resultado BD por ID: " . json_encode($existe));

        if (!$existe) {
            $this->log("Vehículo con ID $idVehiculo no existe en BD");
            return ['encontrado' => false, 'inactivo' => false];
        }

        // Verificar si está inactivo
        if ($existe['Estado'] !== 'Activo') {
            $this->log("Vehículo con ID $idVehiculo está INACTIVO");
            return ['encontrado' => true, 'inactivo' => true, 'datos' => $existe];
        }

        // Vehículo activo - obtener todos los datos
        $sql = "SELECT
                    v.IdVehiculo,
                    v.TipoVehiculo,
                    v.PlacaVehiculo,
                    v.DescripcionVehiculo,
                    v.IdSede,
                    v.Estado,
                    COALESCE(f.NombreFuncionario, vis.NombreVisitante, v.TarjetaPropiedad, 'No registrado') AS DuenoVehiculo,
                    f.IdFuncionario AS IdFuncionarioReal,
                    f.FotoFuncionario,
                    f.Estado AS EstadoFuncionario,
                    (SELECT p.IdParqueadero FROM parqueadero p
                     WHERE p.IdSede = v.IdSede AND p.Estado = 'Activo'
                     LIMIT 1) AS IdParqueadero,
                    ep.NumeroEspacio
                FROM vehiculo v
                LEFT JOIN funcionario f ON v.IdFuncionario = f.IdFuncionario
                LEFT JOIN visitante vis ON v.IdVisitante = vis.IdVisitante
                LEFT JOIN espacio_parqueadero ep
                       ON ep.IdVehiculo = v.IdVehiculo AND ep.Estado = 'Ocupado'
                WHERE v.IdVehiculo = ?
                  AND v.Estado = 'Activo'
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idVehiculo]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->log("Vehículo encontrado y activo: " . $row['PlacaVehiculo']);
            return array_merge($row, ['encontrado' => true, 'inactivo' => false]);
        }

        return ['encontrado' => false, 'inactivo' => false];
    }

    public function obtenerUltimoMovimientoVehiculo($idVehiculo) {
        $stmt = $this->pdo->prepare(
            "SELECT TipoMovimiento FROM ingreso
             WHERE IdVehiculo = ?
             ORDER BY IdIngreso DESC
             LIMIT 1"
        );
        $stmt->execute([$idVehiculo]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        return $fila ? $fila['TipoMovimiento'] : null;
    }

    public function registrarIngreso(
        $idVehiculo,
        $idFuncionario = null,
        $idSede = null,
        $idParqueadero = null,
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
                    COALESCE(f.NombreFuncionario, vis.NombreVisitante, v.TarjetaPropiedad, 'No registrado') AS DuenoVehiculo,
                    ep.NumeroEspacio,
                    i.TipoMovimiento,
                    i.FechaIngreso
                FROM ingreso i
                INNER JOIN vehiculo v ON i.IdVehiculo = v.IdVehiculo
                LEFT JOIN funcionario f ON v.IdFuncionario = f.IdFuncionario
                LEFT JOIN visitante vis ON v.IdVisitante = vis.IdVisitante
                LEFT JOIN espacio_parqueadero ep
                       ON ep.IdVehiculo = v.IdVehiculo AND ep.Estado = 'Ocupado'
                WHERE i.IdVehiculo IS NOT NULL
                ORDER BY i.IdIngreso DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>  